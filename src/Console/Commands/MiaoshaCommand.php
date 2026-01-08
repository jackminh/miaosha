<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\SeckillService;
use Jackminh\Miaosha\Services\SeckillTokenService;
use Jackminh\Miaosha\Services\AntifraudService;
use Jackminh\Miaosha\Services\OrderService;
use Jackminh\Miaosha\Services\SeckillOrderService;


class MiaoshaCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha:start';
    protected $description = '秒杀开始';
    protected SeckillService $seckillService;
    protected SeckillTokenService $seckillTokenService;
    protected AntifraudService $antifraudService;
    protected OrderService $orderService;
    protected SeckillOrderService $seckillOrderService;
    /**
     * 构造函数注入
     */
    public function __construct(SeckillService $seckillService, SeckillTokenService $seckillTokenService, AntifraudService $antifraudService, OrderService $orderService, SeckillOrderService $seckillOrderService)
    {
        parent::__construct();
        $this->seckillService = $seckillService;
        $this->seckillTokenService = $seckillTokenService;
        $this->antifraudService = $antifraudService;
        $this->orderService = $orderService;
        $this->seckillOrderService = $seckillOrderService;
    }

    public function handle(): int
    {
          dump($this->submit());
          return true;
      
    }

    public function getToken(){
        $result = [
           'status' => 1,
           'data'   => [],
           'message'=> ''
        ];
        $userId     = 1;
        $activityId = 1;
        $activity = $this->seckillService->checkActivity($activityId);
        if (!$activity) {
            $result = [
              'status'    => 0,
              'data'      => [],
              'message'   => "活动不存在或已结束"
            ];
            return $result;
        }
        // 1. 验证用户资格（黑名单、购买限制等）
        if (!$this->seckillService->checkUserQualification($userId, $activityId)) {
            $result = [
              'status'    => 0,
              'data'      => [],
              'message'   => "您没有参与资格"
            ];
            return $result;
        }
        // 2. 生成令牌（带过期时间）
        $token = $this->seckillTokenService->generateToken($userId, $activityId);
        $result = [
              'status'    => 1,
              'data'      => [
                     'token'       => $token,
                     'expire_in'   => 30, // 30秒过期
                     'server_time' => time(),
                     'start_time'  => strtotime($activity->start_time)
              ],
              'message'   => "获取成功"
        ];
        return $result;
    }


   /**
     * 提交秒杀请求
     */
    public function submit()
    {

        $res = $this->getToken();
        $token = "";
        if($res['status'] == 1){
            $token = $res['data']['token'];
        }
        if($token == ""){
            return false;
        }

        $userId   = 1;
        $token    = $token;
        $quantity = 1;
        $ip       = "127.0.0.1";
        $activityId = 1;
        $fingerprint = "qiaf:oioqe:89qoi:qoir";
        
        try {
            // 1. 令牌验证
            if(!$this->seckillTokenService->verifyToken($token)) {
                $result = [
                  'status'    => 0,
                  'data'      => [],
                  'message'   => "令牌无效或已过期"
                ];
                return $result;
            }

            //1秒内只能请求一次
            if(!$this->antifraudService->rateLimit($activityId,$userId)){
               $result = [
                  'status'    => 0,
                  'data'      => [],
                  'message'   => "1秒内只能请求一次"
                ];
                return $result;
            }
            //60秒内最多10次请求
            if(!$this->antifraudService->checkIpLimit($ip, $activityId)){
               $result = [
                  'status'    => 0,
                  'data'      => [],
                  'message'   => "60秒内最多10次请求"
                ];
                return $result;
            }
            //同一设备指纹10秒内不能重复参与
            if(!$this->antifraudService->checkDeviceFingerprint($fingerprint, $activityId)){
               $result = [
                  'status'    => 0,
                  'data'      => [],
                  'message'   => "同一设备指纹10秒内不能重复参与"
                ];
                return $result;
            }
            //用户行为分析
            if(!$this->antifraudService->analyzeUserBehavior($userId, $activityId)){
                $result = [
                  'status'    => 0,
                  'data'      => [],
                  'message'   => "用户行为不正常"
                ];
                return $result;
            }
            //执行秒杀（扣减库存）
            $result = $this->seckillService->executeSeckill($activityId, $userId, $quantity, $ip);

            if(!$result['success']) {
                $result = [
                     'status'    => 0,
                     'data'      => [],
                     'message'   => $result['message']
                ];
                return $result;
            }

            //生成订单号
            $orderNo = $this->seckillOrderService->generateOrderNo();
            
            //发送到消息队列异步创建订单
            $this->seckillService->pushToOrderQueue([
                'order_no'     => $orderNo,
                'activity_id'  => $activityId,
                'user_id'      => $userId,
                'quantity'     => $quantity,
                'ip'           => $ip,
                'created_at'   => time()
            ]);
            //标记令牌已使用
            $this->seckillTokenService->useToken($token);
            
            $result = [
                  'status'    => 1,
                  'data'      => [
                        'order_no' => $orderNo,
                        'status'   => 'processing'
                  ],
                  'message'   => "秒杀成功，正在生成订单..."
             ];
             return $result;
            
        } catch (\Exception $e) {
             Log::error('秒杀提交失败', [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
             ]);
             $result = [
                  'status'    => 0,
                  'data'      => [],
                  'message'   => "系统繁忙，请稍后重试..."
             ];
             return $result;
        }
    }
    




}