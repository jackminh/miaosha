<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_goods', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->comment('商品类型 0-实物商品 1-虚拟商品');
            $table->string('name')->comment('商品名称');
            $table->string('code')->comment('商品编码');
            $table->integer('shop_id')->comment('商家id');
            $table->tinyInteger('status')->comment('销售状态: 0-仓库中；1-上架中');
            $table->string('image')->comment('商品主图');
            $table->unsignedInteger('stock')->comment('总库存');
            $table->tinyInteger('del')->comment('删除状态 0-正常 1-已删除 2-回收站');
            $table->text('content')->comment('商品详细描述');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_goods');
    }
};