<?php
namespace Jackminh\Miaosha\Services;

use Jackminh\Miaosha\Repositories\SeckillActivityRepository;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class SeckillService
{
    private $activityRepository;
    private $redis;
    
    // Lua 脚本
    private $decreaseStockScript;

    protected $config;
    
    public function __construct(array $config = []) {
        $this->activityRepository = App::make(SeckillActivityRepository::class);
        $this->config = $config ?: config("miaosha",[]);
        $this->redis = Redis::connection('seckill')->client();
        // 加载 Lua 脚本
        $this->decreaseStockScript = $this->loadDecreaseStockScript();
    }
    /**
     * 检查活动
     * @param  [type] $activityId [description]
     * @return [type]             [description]
     */
    public function checkActivity($activityId)
    {
        $activity = $this->activityRepository->findById($activityId);
        if (!$activity || $activity->status != 1) { //1:活动进行中
            return false;
        }
        $now = time();

        if ($now < strtotime($activity->start_time)) {
            return false;
        }
        if ($now > strtotime($activity->end_time)) {
            return false;
        }
        
        return $activity;

    }


    /**
     * 执行秒杀（扣减库存）
     */
    public function executeSeckill($activityId, $userId, $quantity = 1, $ip = '')
    {
        try {
            // 1. 获取活动信息
            $activity = $this->activityRepository->findById($activityId);
            if (!$activity || $activity->status != 1) { //状态：0-未开始 1-进行中 2-已结束 3-已取消
                return ['success' => false, 'message' => '活动已结束'];
            }
            // 2. 使用 Lua 脚本原子性扣减库存
            $result = $this->redis->evalSha(
                $this->decreaseStockScript, // SHA1 脚本哈希
                [
                    $activityId,  // KEYS[1]
                    $userId,      // KEYS[2]
                    $quantity,           // ARGV[1]
                    $activity->limit_per_user ?? 1, // ARGV[2]
                ],
                2
            );
            if ($result[0] == 0) {
                $messageMap = [
                    'EXCEED_LIMIT'       => '超过购买限制',
                    'NO_ACTIVITY'        => '活动不存在',
                    'INSUFFICIENT_STOCK' => '库存不足'
                ];
                return [
                    'success' => false,
                    'message' => $messageMap[$result[1]] ?? '秒杀失败'
                ];
            }

            // 3. 记录秒杀成功日志
            $this->logSeckillSuccess($activityId, $userId, $quantity, $ip);
            
            return [
                'success' => true,
                'message' => '秒杀成功',
                'remaining_stock' => $result[1]
            ];
            
        } catch (\Exception $e) {
            Log::error('执行秒杀失败', [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    

    /**
     * 推送订单到消息队列
     */
    public function pushToOrderQueue($orderData)
    {
        $queueName = 'seckill_orders';
        // 使用 Redis 列表作为队列
        Redis::connection('seckill')->lpush($queueName, json_encode($orderData));
    }

    /**
     * 预热库存到 Redis
     */
    public function preheatInventory($activityId)
    {
        try {
            $activity = $this->activityRepository->findById($activityId);
            if (!$activity || $activity->available_stock <= 0) {
                throw new \Exception('活动不存在或库存不足');
            }
            
            if ($activity->is_preheat == 1) {
                throw new \Exception('已预热，无需重复预热');
            }
            
            // 获取 Redis 连接
            $redis = Redis::connection('seckill');
            
            $inventoryKey = "seckill:inventory:{$activityId}";
            $soldKey = "seckill:sold:{$activityId}";
            
            // 使用事务确保原子性
            $redis->multi();
            
            // 设置库存
            $redis->set($inventoryKey, $activity->available_stock);
            $redis->set($soldKey, 0);
            
            // 设置过期时间
            $expireSeconds = strtotime($activity->end_time) - time() + 3600;
            if ($expireSeconds > 0) {
                $redis->expire($inventoryKey, $expireSeconds);
                $redis->expire($soldKey, $expireSeconds);
            }
            
            $redis->exec();
            
            // 验证数据是否写入成功
            $actualStock = $redis->get($inventoryKey);
            if ($actualStock != $activity->available_stock) {
                throw new \Exception('库存写入Redis失败');
            }
            
            // 标记已预热
            $this->activityRepository->markAsPreheated($activityId);
            
            Log::info('库存预热成功', [
                'activity_id' => $activityId,
                'stock' => $activity->available_stock,
                'expire_seconds' => $expireSeconds,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('库存预热失败', [
                'activity_id' => $activityId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return false;
        }
    }
    
    
    /**
     * 检查用户资格
     */
    public function checkUserQualification($userId, $activityId)
    {
        // 1. 检查黑名单
        if ($this->isInBlacklist($userId)) {
            return false;
        }
        // 2. 检查活动时间
        $activity = $this->activityRepository->findById($activityId);
        $now = time();
        if ($now < strtotime($activity->start_time)) {
            return false;
        }
        if ($now > strtotime($activity->end_time)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 同步 Redis 库存到数据库
     */
    public function syncInventoryToDB($activityId)
    {
        $inventoryKey = "seckill:inventory:{$activityId}";
        $soldKey = "seckill:sold:{$activityId}";
        
        $remainingStock = Redis::connection('seckill')->get($inventoryKey) ?? 0;
        $soldCount      = Redis::connection('seckill')->get($soldKey) ?? 0;
        
        // 更新数据库
        DB::transaction(function () use ($activityId, $remainingStock, $soldCount) {
            $this->activityRepository->updateStock($activityId, $remainingStock);
        });
    }

    /**
     * 加载扣减库存 Lua 脚本
     */
    private function loadDecreaseStockScript()
    {
        $script = <<<LUA
local activity_id = KEYS[1]
local user_id = KEYS[2]
local quantity = tonumber(ARGV[1])
local limit_per_user = tonumber(ARGV[2])

-- 库存 key
local inventory_key = "seckill:inventory:" .. activity_id
local sold_key = "seckill:sold:" .. activity_id
local user_limit_key = "seckill:user_limit:" .. activity_id .. ":" .. user_id

-- 检查用户购买限制
local user_bought = redis.call('GET', user_limit_key)
if user_bought and tonumber(user_bought) + quantity > limit_per_user then
    return {0, "EXCEED_LIMIT"}
end

-- 检查库存
local current_stock = redis.call('GET', inventory_key)
if not current_stock then
    return {0, "NO_ACTIVITY"}
end

current_stock = tonumber(current_stock)
if current_stock < quantity then
    return {0, "INSUFFICIENT_STOCK"}
end

-- 扣减库存
local new_stock = current_stock - quantity
redis.call('SET', inventory_key, new_stock)

-- 更新已售数量
redis.call('INCRBY', sold_key, quantity)

-- 更新用户购买次数
if user_bought then
    redis.call('INCRBY', user_limit_key, quantity)
else
    redis.call('SETEX', user_limit_key, 86400, quantity)
end

return {1, new_stock}
LUA;
        
        return $this->redis->script('load', $script);
    }
    

    private function logSeckillSuccess($activityId, $userId, $quantity, $ip)
    {
        Log::channel('seckill')->info('秒杀成功', [
            'activity_id' => $activityId,
            'user_id' => $userId,
            'quantity' => $quantity,
            'ip' => $ip,
            'timestamp' => time()
        ]);
    }

    /**
     * 检查用户
     * @param  [type]  $userId [description]
     * @return boolean         [description]
     */
    private function isInBlacklist($userId)
    {
        $blacklistKey = "seckill:blacklist:{$userId}";
        return Redis::connection('seckill')->exists($blacklistKey);
    }





}