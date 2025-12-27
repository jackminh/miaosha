<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;

use Jackminh\Miaosha\Facades\Miaosha;


class MiaoshaCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha';
    protected $description = '';

    public function handle(): int
    {
        $this->info("秒杀开始...");
        return self::SUCCESS;
    }
}