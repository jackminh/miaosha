<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_order_log', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('订单id');
            $table->integer('handle_id')->comment('操作人id');
            $table->integer('shop_id')->comment('店铺id');
            $table->unsignedSmallInteger('channel')->comment('渠道编号。变动方式。');
            $table->tinyInteger('type')->comment('操作类型;0-会员;1-门店');
            $table->text('content')->nullable()->comment('日志内容');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_order_log');
    }
};
