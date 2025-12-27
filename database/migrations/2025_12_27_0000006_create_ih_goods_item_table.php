<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_goods_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('stock')->comment('库存');
            $table->decimal('price')->comment('价格');
            $table->decimal('market_price')->comment('市场价');
            $table->integer('goods_id')->comment('商品id');
            $table->string('image')->comment('商品SKU图像');
            $table->string('bar_code')->comment('条码');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ih_goods_item');
    }
};

