<?php
namespace Jackminh\Miaosha\Services;

use Jackminh\Miaosha\Repositories\SeckillActivityRepository;
use Jackminh\Miaosha\Repositories\SeckillOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class SeckillOrderService
{


    protected array $config;
    protected SeckillActivityRepository $seckillActivityRepository;
    protected SeckillOrderRepository $seckillOrderRepository;

    public function __construct(array $config = []){
        $this->config = $config ?: config("miaosha",[]);
        $this->seckillActivityRepository = App::make(SeckillActivityRepository::class);  
        $this->seckillOrderRepository = App::make(SeckillOrderRepository::class); 
    }


    /**
     * 生成订单号
     *
     * @return string
     */
    public function generateOrderNo(): string
    {
        // 格式: 年月日 + 6位随机数 + 2位随机数
        return date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT) . mt_rand(10, 99);
    }

  /**
     * 创建秒杀订单
     */
    public function createSeckillOrder(array $orderData)
    {
        DB::beginTransaction();
        
        try {
            $activity = $this->validateActivity($orderData['activity_id']);
            if(!$activity){
                throw new \Exception('活动已结束');
            }
            // 2. 检查用户是否已购买（防重复下单）
            if($this->seckillOrderRepository->hasUserPurchased($orderData['user_id'], $orderData['activity_id'],$orderData['order_no'])) {
                throw new \Exception('该订单已经存在,请勿重复下单');
            }
            // 3. 创建订单记录
            $seckillOrder = [
                'order_sn'       => $orderData['order_no'],
                'activity_id'    => $orderData['activity_id'],
                'user_id'        => $orderData['user_id'],
                'goods_id'       => $activity->goods_id,
                'quantity'       => $orderData['quantity'],
                'unit_price'     => $activity->seckill_price, //秒杀价
                'total_amount'   => $orderData['quantity'] * $activity->seckill_price,
                'status'         => 0,  //状态：0-待支付 1-支付成功 2-已取消 3-超时关闭
                'ip_address'     => $orderData['ip'] ?? request()->ip(),
                'user_agent'     => $orderData['user_agent'] ?? request()->userAgent(),
            ];

            $order = $this->seckillOrderRepository->createSeckillOrder($seckillOrder);
            
            DB::commit();

            Log::info('秒杀订单创建成功', [
                'order_id'    => $order->id,
                'order_sn'    => $order->order_sn,
                'user_id'     => $order->user_id,
                'activity_id' => $order->activity_id,
            ]);

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            dump($e->getMessage());
            Log::error('创建秒杀订单失败', [
                'order_data' => $orderData,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            
            return null;
        }
    }
    
    /**
     * 验证活动状态
     */
    private function validateActivity(int $activityId)
    {

        $activity = $this->seckillActivityRepository->findById($activityId);
        
        if (!$activity) {
            throw new \Exception('活动不存在');
        }
        
        // 检查活动状态
        if ($activity->status != 1) { // 假设1表示进行中
            throw new \Exception('活动未开始或已结束');
        }
        
        // 检查活动时间
        $now = Carbon::now();
        if ($now->lt($activity->start_time)) {
            throw new \Exception('活动尚未开始');
        }
        
        if ($now->gt($activity->end_time)) {
            throw new \Exception('活动已结束');
        }
        
        return $activity;
    }
    
   
   
}