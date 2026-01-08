<?php

namespace Jackminh\Miaosha\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Jackminh\Miaosha\Services\SeckillOrderService;

class ConsumerService
{
    protected $seckillOrderService;

    protected array $config;

    public function __construct(array $config = []){
        $this->config = $config ?: config("miaosha",[]);
        $this->seckillOrderService = App::make('seckillorder');
    }
    
    /**
     * 从 Redis 队列消费订单
     */
    public function consumeOrder($queueName = 'seckill_orders', $timeout = 30)
    {
        $startTime = time();
        
        while ((time() - $startTime) < $timeout) {
            // 从队列右侧弹出（先进先出）
            $data = Redis::connection('seckill')->rpop($queueName);
            if (!$data) {
                // 队列为空，等待一下
                sleep(1);
                continue;
            }
            try {
                dump($data);
                $orderData = json_decode($data, true);
                if(json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('JSON 解析失败: ' . json_last_error_msg());
                }
                // 处理订单
                $this->processOrder($orderData);
                
            } catch (\Exception $e) {
                dump($e->getMessage());
                Log::error('消费队列订单失败', [
                    'raw_data' => $data,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    
    /**
     * 处理订单数据
     */
    private function processOrder(array $orderData): void
    {
        Log::info('开始处理队列订单', $orderData);
        
        try {
            // 1. 验证必要字段
            $this->validateOrderData($orderData);
            
            // 2. 创建订单
            $order = $this->seckillOrderService->createSeckillOrder($orderData);
            
            // if (!$order) {
            //     throw new \Exception('创建订单失败');
            // }
            
            // 3. 防止重复购买
            $this->markUserPurchased($orderData['activity_id'], $orderData['user_id'], $order->id);
            
            // 4. 记录成功日志
            Log::info('队列订单处理成功', [
                'order_id' => $order->id,
                'order_no' => $order->order_sn,
                'user_id' => $orderData['user_id'],
            ]);
            
        } catch (\Exception $e) {
            Log::error('处理队列订单异常', [
                'order_data' => $orderData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            dump($e->getMessage());
            // 可以重试或移动到死信队列
            $this->handleFailedOrder($orderData, $e);
        }
    }
    
    /**
     * 验证订单数据
     */
    private function validateOrderData(array $data): void
    {
        $requiredFields = ['order_no', 'activity_id', 'user_id', 'quantity'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("缺少必要字段: {$field}");
            }
        }
        
        // 验证数值类型
        if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            throw new \InvalidArgumentException("购买数量无效: {$data['quantity']}");
        }
    }
    
    /**
     * 标记用户已购买
     */
    private function markUserPurchased($activityId, $userId, $orderId): void
    {
        $key = "seckill:user_order:{$activityId}:{$userId}";
        Redis::connection('seckill')->setex($key, 86400, $orderId);
    }
    
    /**
     * 处理失败订单
     */
    private function handleFailedOrder(array $orderData, \Exception $e): void
    {
        // 移动到死信队列
        $deadLetterQueue = 'seckill_orders:dead';
        $failedData = [
            'original_data' => $orderData,
            'error' => $e->getMessage(),
            'failed_at' => now()->toDateTimeString(),
        ];
        
        Redis::connection('seckill')->lpush($deadLetterQueue, json_encode($failedData));
        
        Log::warning('订单已移动到死信队列', [
            'order_no'   => $orderData['order_no'],
            'dead_queue' => $deadLetterQueue,
        ]);
    }
    
    /**
     * 批量消费
     */
    public function batchConsume($queueName = 'seckill_orders', $limit = 100): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'success' => 0,
        ];
        
        for ($i = 0; $i < $limit; $i++) {
            $data = Redis::connection('seckill')->rpop($queueName);
            
            if (!$data) {
                break;
            }
            
            try {
                $orderData = json_decode($data, true);
                $this->processOrder($orderData);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error('批量消费失败', [
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
            }
            
            $results['processed']++;
        }
        
        return $results;
    }
    
    /**
     * 查看队列状态
     */
    public function getQueueStats($queueName = 'seckill_orders'): array
    {
        $redis = Redis::connection('seckill');
        
        return [
            'queue_name'        => $queueName,
            'queue_length'      => $redis->llen($queueName),
            'dead_queue_length' => $redis->llen($queueName . ':dead'),
            'first_item'        => $redis->lindex($queueName, 0),
            'last_item'         => $redis->lindex($queueName, -1),
        ];
    }
}