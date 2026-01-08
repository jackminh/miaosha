<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Jackminh\Miaosha\Services\ConsumerService;
use Jackminh\Miaosha\Services\SeckillOrderService;

class ConsumerCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha:consumer';
    protected $description = '队列消费';
    protected ConsumerService $consumerService;
    protected SeckillOrderService $seckillorderService;
    /**
     * 构造函数注入
     */
    public function __construct(ConsumerService $consumerService, SeckillOrderService $seckillOrderService)
    {
        parent::__construct();
        $this->consumerService = $consumerService;
        $this->seckillOrderService = $seckillOrderService;
    }

    public function handle(): int
    {
        $res = $this->consumerService->consumeOrder();
        
        $this->info($res);
        return self::SUCCESS;
    }




}