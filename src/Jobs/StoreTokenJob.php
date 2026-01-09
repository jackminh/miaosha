<?php
namespace Jackminh\Miaosha\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use Jackminh\Miaosha\Repositories\SeckillTokenRepository;

class StoreTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 队列属性配置
    public $timeout = 60;           // 任务超时时间
    public $tries = 3;              // 重试次数
    public $backoff = [10, 30, 60]; // 重试间隔
    
    // 任务数据
    protected $token;

    /**
     * 创建队列任务
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->onQueue('seckill_tokens');  
        // 设置延迟1秒
        $this->delay(now()->addSeconds(1));
        
    }

    /**
     * 执行任务 - 将令牌保存到数据库
     */
    public function handle(SeckillTokenRepository $seckillTokenRepository)
    {
        try {
            // 1. 准备数据
            $data = $this->getData();
            $tokenData = [
                'activity_id'   => $data['activity_id'],
                'user_id'       => $data['user_id'],
                'token'         => $data['token'],
                'expire_at'     => $data['expire_at'],
                'is_used'       => $data['is_used'],
                'used_at'       => $data['used_at']
            ];
            // 2. 保存到数据库
            $seckillTokenRepository->createSeckillToken($tokenData);
            
            // 3. 可选的后续操作
            $this->afterStore($tokenData);
            
        } catch (\Exception $e) {
           // dump($e->getMessage());
            Log::error('令牌保存到数据库失败', [
                'token' => $this->token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * 保存后的后续操作
     */
    protected function afterStore($data)
    {
        // 1. 记录日志
        Log::info('令牌已异步保存到数据库', [
            'token' => $this->token,
            'time' => now()
        ]);
    }
    
    /**
     * 任务失败处理
     */
    public function failed(\Throwable $exception)
    {
        // 记录失败日志
        Log::critical('令牌保存任务失败', [
            'token'     => $this->token,
            'error'     => $exception->getMessage(),
            'attempts'  => $this->attempts(),
        ]);
    }
    
    /**
     * 获取任务数据
     */
    protected function getData()
    {
        $realData = [];
        try{
            $config = config("miaosha",[]);
            $prefix = $config['seckill']['seckill_token_prefix'];
            $key    = $prefix . $this->token;
            $data   = Redis::connection("seckill")->get($key);
            $tokenData = json_decode($data, true);
            if(json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON 解析失败: ' . json_last_error_msg());
            }
            dump($tokenData);
            $isUsed = match($tokenData['status'] ?? '') {
                'used'      => 1,
                'pending'   => 0,
                'expired'   => 2,
                 default    => 0
            };
            $realData = [
                'activity_id'   => $tokenData['activity_id'],
                'user_id'       => $tokenData['user_id'],
                'token'         => $this->token,
                'expire_at'     => $tokenData['expires_at'],
                'is_used'       => $isUsed,
                'used_at'       => $tokenData['used_at'] ?? null 
            ];
            return $realData;
        }catch(\Exception $e){
           // dump($e->getMessage());
            Log::error('队列中获取token失败', [
                'raw_data' => $data,
                'error' => $e->getMessage(),
            ]);
        }
        return $realData;
    }
}