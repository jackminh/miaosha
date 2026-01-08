<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\SeckillMontiorService;

class SeckillMonitorCommand extends Command
{
    
    protected $signature = 'jackminh:miaosha:monitor';
    protected $description = 'redis数据监控';
    protected SeckillMontiorService $seckillMontiorService;
    /**
     * 构造函数注入
     */
    public function __construct(SeckillMontiorService $seckillMontiorService)
    {
        parent::__construct();
        $this->seckillMontiorService = $seckillMontiorService;
    }

    public function handle(): int
    {
        $activityId = 1;
        $userId = 1;
        $result = $this->seckillMontiorService->collectMetrics($activityId,$userId);
        dump($result);
        return self::SUCCESS;
    }




}