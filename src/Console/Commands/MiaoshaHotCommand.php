<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\SeckillService;

class MiaoshaHotCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha:hot';
    protected $description = '预热数据';
    protected SeckillService $seckillService;
    /**
     * 构造函数注入
     */
    public function __construct(SeckillService $seckillService)
    {
        parent::__construct();
        $this->seckillService = $seckillService;
    }

    public function handle(): int
    {
        //初始商品规格库存到redis
        $activityId = 1;
        $result = $this->seckillService->preheatInventory($activityId);
        dump($result);
        $this->info("####预热数据完成####");
        return self::SUCCESS;
    }




}