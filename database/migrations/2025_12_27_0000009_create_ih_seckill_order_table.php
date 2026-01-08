<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ih_seckill_order', function (Blueprint $table) {
            $table->id()->comment('订单ID');
            $table->string('order_sn', 32)->comment('订单编号');
            $table->unsignedBigInteger('activity_id')->comment('活动ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->integer('quantity')->default(1)->comment('购买数量');
            $table->decimal('unit_price', 10, 2)->comment('单价');
            $table->decimal('total_amount', 10, 2)->comment('总金额');
            $table->tinyInteger('status')->default(0)->comment('状态：0-待支付 1-支付成功 2-已取消 3-超时关闭');
            $table->dateTime('pay_time')->nullable()->comment('支付时间');
            $table->dateTime('cancel_time')->nullable()->comment('取消时间');
            $table->string('cancel_reason', 255)->nullable()->comment('取消原因');
            $table->string('ip_address', 45)->nullable()->comment('用户IP');
            $table->string('user_agent', 500)->nullable()->comment('用户UA');
            $table->timestamps();

            $table->unique('order_sn', 'uk_order_sn');
            $table->index('user_id', 'idx_user_id');
            $table->index('activity_id', 'idx_activity_id');
            $table->index('created_at', 'idx_created_at');
            
            $table->comment('秒杀订单表');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ih_seckill_order');
    }
};