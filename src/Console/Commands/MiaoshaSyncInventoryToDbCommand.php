<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\SeckillService;

class MiaoshaSyncInventoryToDbCommand extends Command
{
	
	protected $signature = 'jackminh:sync:inventory:to:db';
    protected $description = '同步redis库存数据到数据库';
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
        //同步redis库存数据到数据库
        $activityId = 1;
        $this->seckillService->syncInventoryToDB($activityId);
        $this->info("####同步redis库存数据到数据库完成####");
        return self::SUCCESS;
    }




}