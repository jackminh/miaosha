<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_order', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation("utf8mb4_unicode_ci");
            $table->comment('订单日志表');
            $table->id();
            $table->integer('trade_id')->comment('交易订单id(父订单id)');
            $table->integer('shop_id')->comment('店铺id');
            $table->string('order_sn')->comment('订单编号');
            $table->integer('user_id')->comment('用户id');
            $table->integer('goods_id')->comment('商品id');
            $table->integer('order_status')->nullable()->default(0)->comment('支付状态;0-待支付;1-已支付;2-已退款;3-拒绝退款');
            $table->integer('pay_status')->nullable()->default(0)->comment('支付状态;0-待支付;1-已支付;2-已退款;3-拒绝退款');
            $table->tinyInteger('pay_way')->default(1)->comment('1-微信支付  2-支付宝支付 3-余额支付  4-线下支付');
            $table->integer('pay_time')->comment('支付时间');
            $table->decimal('goods_price',total:8,places:2)->comment('订单商品总价');
            $table->decimal('order_amount',total:8,places:2)->comment('应付款金额');
            $table->decimal('total_amount',total:8,places:2)->comment('订单总价');
            $table->integer('total_num')->nullable()->default(0)->comment('订单商品数量');
            $table->timestamp('cancel_time', precision:0)->comment('订单取消时间');
            $table->tinyInteger('refund_status')->nullable()->default(0)->comment('退款状态：0-未退款；1-部分退款；2-全部退款');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_order');
    }
};