<?php
namespace Miaosha;
use Miaosha\Cache;
use Miaosha\Db;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 *
 * maiosha
 *
 */
class Activity {

	private $cache = null;
	private $db = null;
	private $error = '';
	private $log = null;

	public function __construct() {
		$this->cache = Cache::getInstance();
		$this->cache->connect();
		$this->db = Db::getInstance();
		$this->db->connect();
		$this->log = new Logger('miaosha');
		$this->log->pushHandler(new StreamHandler('./result.log', Logger::INFO));
	}

	/**
	 *
	 * 获取商品库存
	 */
	public function goods_store($goods_id) {
		$sql = "select goods_id,sku_id,number,freez,version from ih_store where goods_id={$goods_id};";
		$result = $this->db->select($sql);
		return $result;
	}

	/**
	 *
	 *  将商品库存放入redis
	 */
	public function init_goods_store_to_cache($goods_id) {
		$this->log->info("初始化库存数据到缓存中");
		$goods_key = $this->get_cache_for_goods_key($goods_id);
		if ($this->cache->isExists($goods_key)) {
			return true;
		}
		$goods_store = $this->goods_store($goods_id);
		if (!empty($goods_store)) {
			foreach ($goods_store as $key => $goods) {
				$good_id = $goods['goods_id'];
				$good_number = $goods['number'];
				for ($i = 1; $i <= $good_number; $i++) {
					$this->cache->lpush($goods_key, $i);
				}
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 *
	 * 处理高并发请求
	 */
	public function handle($post) {
		$message = "";
		try {
			$data['user_id'] = $post['user_id'];
			$data['goods_id'] = $post['goods_id'];

			$data['sku_id'] = 2;
			$data['price'] = 15.30;
			$result = $this->goods_store($data['goods_id']);
			$data['sku_id'] = $result[0]['sku_id'];
			$data['number'] = $result[0]['number'];
			$data['freez'] = $result[0]['freez'];
			$data['version'] = $result[0]['version'];

			//var_dump($result);exit;

			//step 1 将请求放入队列中
			$this->log->notice("将请求放入排队队列中");
			$key = $this->get_cache_for_queue_key($data['goods_id']);
			$value = $data['user_id'];
			$this->cache->lpush($key, $value); // 1003,1002,1001

			//step 2 从排队队列中取出一个
			$user_id = $this->cache->rpop($key);
			$this->log->notice("从排队队队中获取一个用户: " . $user_id);
			//step 3 检查商口库存是否存在
			$goods_key = $this->get_cache_for_goods_key($data['goods_id']);
			$goods_number = $this->cache->lpop($goods_key);
			$this->log->notice("从缓存中获取商品库存数量: " . $goods_number);
			if ($goods_number >= 1) {
				//商品库存大于或等于1时
				//step 4 判断结果队列中是否存在当前用户, 一个用户只能抢购一次
				$result_key = $this->get_cache_for_result_key($data['goods_id']);
				$isExistInCache = $this->isExistInCache($data['user_id'], $data['goods_id']);
				if ($isExistInCache) {
					$this->error = "您已经购买过此商品，每人限购一次";
					return false;
				} else {
					//写入结果队列中
					$this->cache->lpush($result_key, $user_id);
					$this->log->notice("写入结果到队列中: " . $user_id);
					//更新库存信息并且生成订单
					$sqls = $this->build_sqls($data);
					$this->db->handler_transaction($sqls, $this->log);
					return true;
				}
			} else {
				$this->error = "商品库存不够";
				return false;
			}
		} catch (\Exception $e) {
			$message = $e->getMessage();
		}
		$this->error = $message;
		return false;
	}

	/**
	 *
	 * 构建事务执行语句
	 */
	public function build_sqls($data) {
		//更新库库存
		$update_store_sql = "UPDATE `ih_store` SET `number` = (`number` - 1) WHERE `number` > 0 AND `goods_id`= {$data['goods_id']}";

		//生成订单
		$format = "INSERT INTO `ih_order`(`order_sn`,`user_id`,`status`,`goods_id`,`sku_id`,`price`,`addtime`) VALUES ('%s',%d,%d,%d,%d,%f,'%s')";
		$order_sn = $this->build_unique_order_no();
		$user_id = $data['user_id'];
		$status = 0;
		$goods_id = $data['goods_id'];
		$sku_id = $data['sku_id'];
		$price = $data['price'];
		$addtime = date('Y-m-d H:i:s', time());
		$insert_order_sql = sprintf($format, $order_sn, $user_id, $status, $goods_id, $sku_id, $price, $addtime);

		$sqls = [
			'insert_order_sql' => $insert_order_sql,
			'update_store_sql' => $update_store_sql,
		];
		return $sqls;
	}

	/**
	 *
	 * 生成订单
	 *
	 */
	public function order($data) {
		$format = "INSERT INTO `ih_order`(`order_sn`,`user_id`,`status`,`goods_id`,`sku_id`,`price`,`addtime`) VALUES ('%s',%d,%d,%d,%d,%f,'%s')";
		$order_sn = $this->build_unique_order_no();
		$user_id = $data['user_id'];
		$status = 0;
		$goods_id = $data['goods_id'];
		$sku_id = $data['sku_id'];
		$price = $data['price'];
		$addtime = date('Y-m-d H:i:s', time());
		$sql = sprintf($format, $order_sn, $user_id, $status, $goods_id, $sku_id, $price, $addtime);
		$result = $this->db->insert($sql);
		return $result;
	}

	/**
	 *
	 *  判断结果队列中是否存在
	 */
	private function isExistInCache($user_id, $goods_id) {
		$result_key = $this->get_cache_for_result_key($goods_id);
		//获取结果队列中的所有元素
		$result = $this->cache->lrange($result_key, 0, -1);
		$this->log->notice("从结果队中获取已经存在的用户");
		if (!empty($result)) {
			if (in_array($user_id, $result)) {
				$this->log->notice("从结果队中获取已经存在的用户：{$user_id}");
				return true;
			} else {
				return false;
			}
		} else {
			$this->log->notice("从结果队中获取已经存在的用户");
			return false;
		}
	}

	public function get_error() {
		return $this->error;
	}

	/**
	 *
	 * 生成订单号
	 */
	private function build_unique_order_no() {
		$order_id_main = date('YmdHis') . rand(10000000, 99999999);
		$order_id_len = strlen($order_id_main);
		$order_id_sum = 0;
		for ($i = 0; $i < $order_id_len; $i++) {
			$order_id_sum += (int) (substr($order_id_main, $i, 1));
		}
		$osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
		return $osn;
	}

	private function get_cache_for_goods_key($goods_id) {
		return "2023_03_18_goods_store_" . $goods_id;
	}

	private function get_cache_for_result_key($goods_id) {
		return "2023_03_18_goods_result_" . $goods_id;
	}

	private function get_cache_for_queue_key($goods_id) {
		return "2023_03_18_goods_queue_" . $goods_id;
	}

}