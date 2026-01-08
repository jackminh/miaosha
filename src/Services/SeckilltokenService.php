<?php

namespace Jackminh\Miaosha\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class SeckillTokenService
{
    protected array $config;

    public function __construct(array $config = []){
        
        $this->config = $config ?: config("miaosha",[]);
        
    }

    /**
     * 生成秒杀令牌
     *
     * @param int $userId 用户ID
     * @param int $activityId 活动ID
     * @param int $productId 商品ID
     * @return string
     * @throws TokenException
     */
    public function generateToken($userId, $activityId, $productId = null): string
    {
        // 1. 生成唯一令牌
        $token = $this->createUniqueToken();
        // 2. 存储令牌信息到Redis
        $this->storeToken($token, $userId, $activityId, $productId);     
        return $token;
    }
    
    /**
     * 存储令牌信息
     */
    protected function storeToken(string $token, $userId, $activityId, $productId = null): void
    {
        try{
            $ttl    = $this->config['seckill']['seckill_token_ttl'];
            $prefix = $this->config['seckill']['seckill_token_prefix'];
            $key    = $prefix . $token;
            $data = [
                'user_id'     => $userId,
                'activity_id' => $activityId,
                'product_id'  => $productId,
                'created_at'  => now()->toDateTimeString(),
                'expires_at'  => now()->addSeconds($ttl)->toDateTimeString(),
                'status'      => 'pending', // pending, used, expired
            ];
            Redis::connection('seckill')->setex($key, $ttl, json_encode($data));
        }catch(\Exception $e){
            Log::error('创建token失败', [
                'data'  => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
    }
    
    /**
     * 验证令牌
     */
    public function verifyToken(string $token): bool
    {
        $prefix = $this->config['seckill']['seckill_token_prefix'];
        $key = $prefix . $token;
        $data = Redis::connection('seckill')->get($key);
        if(!$data){
            return false;
        }
        $tokenData = json_decode($data, true);
        // 检查是否已使用
        if($tokenData['status'] === 'used') {
            return false;
        }
        return true;
    }
    
    /**
     * 获取令牌信息
     */
    public function getTokenInfo(string $token): ?array
    {
        $prefix = $this->config['seckill']['seckill_token_prefix'];
        $key    = $prefix . $token;
        $data = Redis::connection('seckill')->get($key);
        return $data ? json_decode($data, true) : null;
    }
    
    /**
     * 使用令牌（标记为已使用）
     */
    public function useToken(string $token): bool
    {
        try{
            $prefix = $this->config['seckill']['seckill_token_prefix'];
            $ttl    = $this->config['seckill']['seckill_token_ttl'];
            $key    = $prefix . $token;
            $data   = Redis::connection('seckill')->get($key);
            if (!$data) {
                return false;
            }
            $tokenData = json_decode($data, true);
            $tokenData['status'] = 'used';
            $tokenData['used_at'] = now()->toDateTimeString();
            
            //更新令牌状态
            Redis::connection('seckill')->setex($key, $ttl, json_encode($tokenData));
        }catch(\Exception $e){
            Log::error('标记token已使用失败', [
                'token'  => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
        return true;
    }

    /**
     * 生成唯一令牌
     */
    protected function createUniqueToken(): string
    {
        return md5(uniqid('seckill_', true) . Str::random(32) . microtime(true));
    }
    

}