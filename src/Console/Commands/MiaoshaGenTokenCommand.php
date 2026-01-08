<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\SeckillTokenService;

class MiaoshaGenTokenCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha:token';
    protected $description = '秒杀token获取';
    protected SeckillTokenService $seckillTokenService;
    /**
     * 构造函数注入
     */
    public function __construct(SeckillTokenService $seckillTokenService)
    {
        parent::__construct();
        $this->seckillTokenService = $seckillTokenService;
    }

    public function handle(): int
    {
        $activityId = 1;
        $userId     = 1;
        $productId = 10;
        $token = $this->seckillTokenService->generateToken($userId, $activityId, $productId);
        $this->info($token);
        return self::SUCCESS;
    }




}