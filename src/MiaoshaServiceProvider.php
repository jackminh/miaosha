<?php

namespace Jackminh\Miaosha;

use Illuminate\Support\ServiceProvider;
use Jackminh\Miaosha\Console\Commands\MiaoshaCommand;
use Jackminh\Miaosha\Console\Commands\MiaoshaHotCommand;
use Jackminh\Miaosha\Console\Commands\MiaoshaSyncInventoryToDbCommand;
use Jackminh\Miaosha\Console\Commands\MiaoshaGenTokenCommand;
use Jackminh\Miaosha\Console\Commands\SeckillMonitorCommand;
use Jackminh\Miaosha\Console\Commands\ConsumerCommand;

use Jackminh\Miaosha\Services\MiaoshaService;
use Jackminh\Miaosha\Services\AntifraudService;
use Jackminh\Miaosha\Services\SeckillService;
use Jackminh\Miaosha\Services\SeckillTokenService;
use Jackminh\Miaosha\Services\OrderService;
use Jackminh\Miaosha\Services\SeckillOrderService;
use Jackminh\Miaosha\Services\SeckillMontiorService;
use Jackminh\Miaosha\Services\ConsumerService;


use Jackminh\Miaosha\Contracts\UserRepositoryInterface;
use Jackminh\Miaosha\Contracts\GoodsRepositoryInterface;

use Jackminh\Miaosha\Repositories\UserRepository;
use Jackminh\Miaosha\Repositories\GoodsRepository;

use Jackminh\Miaosha\Contracts\GoodsItemRepositoryInterface;
use Jackminh\Miaosha\Repositories\GoodsItemRepository;


use Jackminh\Miaosha\Contracts\SeckillActivityRepositoryInterface;
use Jackminh\Miaosha\Repositories\SeckillActivityRepository;

use Jackminh\Miaosha\Repositories\SeckillOrderRepositoryInterface;
use Jackminh\Miaosha\Repositories\SeckillOrderRepository;


class MiaoshaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 加载辅助函数文件
        $this->loadHelpers();
        //发布配置
        $this->publishes([
            __DIR__ . '/../config/miaosha.php'  => config_path('miaosha.php')
        ],'miaosha-config');
        //发布迁移
        $this->publishes([
            __DIR__ . '/../database/migrations/' =>  database_path('migrations')

        ],'miaosha-migrations');
        //数据填充
        $this->publishes([
            __DIR__.'/../database/seeders/' => database_path('seeders/'),
        ], 'miaosha-seeds');
        //注册命令
        if($this->app->runningInConsole()){
            $this->commands([
                MiaoshaCommand::class,
                MiaoshaHotCommand::class,
                MiaoshaSyncInventoryToDbCommand::class,
                MiaoshaGenTokenCommand::class,
                SeckillMonitorCommand::class,
                ConsumerCommand::class
            ]);
        }


    }

    public function register()
    {
         // 注册 Repository 绑定
        $this->app->bind(GoodsRepositoryInterface::class, GoodsRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(GoodsItemRepositoryInterface::class, GoodsItemRepository::class);
        $this->app->bind(SeckillActivityRepositoryInterface::class, SeckillActivityRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);

        $this->app->bind(SeckillOrderRepositoryInterface::class, SeckillOrderRepository::class);





        $this->mergeConfigFrom(
            __DIR__.'/../config/miaosha.php','miaosha'
        );
        // 监控服务
        $this->app->singleton('antifraud', function ($app) {
            return new AntifraudService(
                config('miaosha',[])
            );
        });
        // 秒杀服务
        $this->app->singleton('seckill', function ($app) {
            return new SeckillService(
                config('miaosha',[])
            );
        });
        // 秒杀token服务
        $this->app->singleton('seckilltoken', function ($app) {
            return new SeckillTokenService(
                config('miaosha',[])
            );
        });
        //订单服务
        $this->app->singleton('order',function($app) {
            return new OrderService(
                config('miaosha',[])
            );
        });
        //秒杀订单服务
        $this->app->singleton('seckillorder',function($app) {
            return new SeckillOrderService(
                config('miaosha',[])
            );
        });
        //监控服务
        $this->app->singleton('seckillMontior',function($app) {
            return new SeckillMontiorService(
                config('miaosha',[])
            );
        });
        //监控服务
        $this->app->singleton('consumer',function($app) {
            return new ConsumerService(
                config('miaosha',[])
            );
        });

        
    }



    protected function loadHelpers(): void
    {
        foreach (glob(__DIR__ . '/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
    
    
}