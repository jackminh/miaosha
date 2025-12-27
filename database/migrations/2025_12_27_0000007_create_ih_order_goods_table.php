<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_order_goods', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation("utf8mb4_unicode_ci");
            $table->comment('订单商品表');
            $table->id();
            $table->integer('order_id')->comment('订单id');
            $table->integer('shop_id')->comment('店铺id');
            $table->integer('goods_id')->comment('商品id');
            $table->integer('item_id')->comment('规格id');
            $table->decimal('goods_price',total:8,places:2)->nullable()->default(0.00)->comment('商品价格');
            $table->decimal('total_pay_price',total:8,places:2)->nullable()->default(0.00)->comment('实际支付商品金额');
            $table->decimal('total_price',total:8,places:2)->nullable()->default(0.00)->comment('商品总价');
            $table->integer('goods_num')->default(0)->comment('商品数量');
            $table->string('goods_name',length:100)->nullable()->default('')->comment('商品名称');
            $table->tinyInteger('shipping_status')->default(0)->comment('0-未发货;1-已发货');
            $table->tinyInteger('refund_status')->default(0)->comment('退款状态：0-未退款；1-部分退款；2-全部退款');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_order_goods');
    }
};
