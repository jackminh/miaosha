<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\SeckilltokenService;

class MiaoshaGenTokenCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha:token';
    protected $description = '秒杀token获取';
    protected SeckilltokenService $seckilltokenService;
    /**
     * 构造函数注入
     */
    public function __construct(SeckilltokenService $seckilltokenService)
    {
        parent::__construct();
        $this->seckilltokenService = $seckilltokenService;
    }

    public function handle(): int
    {
        $activityId = 1;
        $userId     = 1;
        $productId = 10;
        $token = $this->seckilltokenService->generateToken($userId, $activityId, $productId);
        $this->info($token);
        return self::SUCCESS;
    }




}