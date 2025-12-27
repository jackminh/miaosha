<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_order_trade', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation("utf8mb4_unicode_ci");
            $table->comment('父订单表');
            $table->id();
            $table->string('t_sn',length:32)->comment('订单编号');
            $table->integer('shop_id')->comment('店铺id');
            $table->integer('user_id')->comment('用户id');
            $table->decimal('goods_price',total:8,places:2)->nullable()->default(0.00)->comment('订单商品总价');
            $table->decimal('order_amount',total:8,places:2)->nullable()->default(0.00)->comment('应付款金额');
            $table->decimal('total_amount',total:8,places:2)->nullable()->default(0.00)->comment('订单总价');
            $table->string('transaction_id',length:50)->nullable()->default('')->comment('第三方平台交易流水号');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_order_trade');
    }
};
