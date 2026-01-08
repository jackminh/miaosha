<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ih_seckill_activity', function (Blueprint $table) {
            $table->id()->comment('活动ID');
            $table->string('name', 100)->comment('活动名称');
            $table->integer('goods_id')->comment('商品ID');
            $table->integer('total_stock')->default(0)->comment('总库存');
            $table->integer('available_stock')->default(0)->comment('可用库存');
            $table->decimal('original_price', 8, 2)->comment('原价');
            $table->decimal('seckill_price', 8, 2)->comment('秒杀价');
            $table->dateTime('start_time')->comment('开始时间');
            $table->dateTime('end_time')->comment('结束时间');
            $table->tinyInteger('status')->default(0)->comment('状态：0-未开始 1-进行中 2-已结束 3-已取消');
            $table->integer('limit_per_user')->default(1)->comment('每人限购数量');
            $table->boolean('is_preheat')->default(false)->comment('是否预热');
            $table->integer('version')->default(0)->comment('乐观锁版本号');
            $table->timestamps();

            $table->index(['start_time', 'end_time', 'status'], 'idx_time_status');
            $table->index('goods_id', 'idx_goods_id');
            
            $table->comment('秒杀活动表');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ih_seckill_activity');
    }
};