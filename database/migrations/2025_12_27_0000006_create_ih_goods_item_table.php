<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_goods_item', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation("utf8mb4_unicode_ci");
            $table->comment('商品规格表');
            $table->id();
            $table->unsignedInteger('stock')->nullable()->default(0)->comment('库存');
            $table->decimal('price',total:8,places:2)->nullable()->default(0.00)->comment('价格');
            $table->decimal('market_price',total:8,places:2)->nullable()->default(0.00)->comment('市场价');
            $table->integer('goods_id')->comment('商品id');
            $table->string('image',length:100)->nullable()->default('')->comment('商品SKU图像');
            $table->string('bar_code',length:100)->nullable()->default('')->comment('条码');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ih_goods_item');
    }
};

