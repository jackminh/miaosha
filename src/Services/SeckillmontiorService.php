<?php
namespace Jackminh\Miaosha\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class SeckillMontiorService
{


    protected array $config;

    public function __construct(array $config = []){
        $this->config = $config ?: config("miaosha",[]);
        
    }


    // 监控关键指标
    public function collectMetrics($activityId,$userId)
    {
        $metrics = [
             // 库存监控
            'remaining_stock'   => Redis::connection('seckill')->get("seckill:inventory:{$activityId}"),
            'sold_count'        => Redis::connection('seckill')->get("seckill:sold:{$activityId}"),
            
            // 成功率监控
            'total_requests'    => Redis::connection('seckill')->get("seckill:requests:total:{$activityId}"),
            'success_count'     => Redis::connection('seckill')->get("seckill:requests:success:{$activityId}"),
            'failure_count'     => Redis::connection('seckill')->get("seckill:requests:failure:{$activityId}"),
            
            // 队列监控
            'queue_size'        => Redis::connection('seckill')->llen("seckill_orders"),
            'processing_count'  => Redis::connection('seckill')->get("seckill:processing:{$activityId}"),
            
            // 用户行为
            'unique_users'      => Redis::connection('seckill')->scard("seckill:users:{$activityId}"),
            'duplicate_attempts'=> Redis::connection('seckill')->get("seckill:duplicate:{$activityId}")
        ];
        
        // 计算成功率
        if ($metrics['total_requests'] > 0) {
            $metrics['success_rate'] = 
                round($metrics['success_count'] / $metrics['total_requests'] * 100, 2);
        }
        
        return $metrics;
    }
   
   
}