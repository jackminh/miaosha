<?php

namespace Jackminh\Miaosha;

use Illuminate\Support\ServiceProvider;
use Jackminh\Miaosha\Console\Commands\MiaoshaCommand;
use Jackminh\Miaosha\Services\MiaoshaService;
use Jackminh\Miaosha\Contracts\UserRepositoryInterface;
use Jackminh\Miaosha\Contracts\GoodsRepositoryInterface;

use Jackminh\Miaosha\Repositories\UserRepository;
use Jackminh\Miaosha\Repositories\GoodsRepository;

use Jackminh\Miaosha\Contracts\GoodsItemRepositoryInterface;
use Jackminh\Miaosha\Repositories\GoodsItemRepository;

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
                MiaoshaCommand::class
            ]);
        }


    }

    public function register()
    {
         // 注册 Repository 绑定
        $this->app->bind(GoodsRepositoryInterface::class, GoodsRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(GoodsItemRepositoryInterface::class, GoodsItemRepository::class);
        

        $this->mergeConfigFrom(
            __DIR__.'/../config/miaosha.php','miaosha'
        );
        // 注册主服务
        $this->app->singleton('miaosha', function ($app) {
            return new MiaoshaService(
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