<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ih_inventory_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id')->comment('活动ID');
            $table->string('order_sn', 32)->nullable()->comment('订单号');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->tinyInteger('change_type')->comment('变更类型：1-扣减 2-回退');
            $table->integer('change_quantity')->comment('变更数量');
            $table->integer('before_quantity')->comment('变更前数量');
            $table->integer('after_quantity')->comment('变更后数量');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->timestamp('created_at')->useCurrent();

            $table->index('activity_id', 'idx_activity_id');
            $table->index('order_sn', 'idx_order_sn');
            
            $table->comment('库存流水表');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ih_inventory_log');
    }
};