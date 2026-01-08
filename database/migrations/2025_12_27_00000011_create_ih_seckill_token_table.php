<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ih_seckill_token', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id')->comment('活动ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('token', 64)->comment('令牌');
            $table->dateTime('expire_at')->comment('过期时间');
            $table->boolean('is_used')->default(false)->comment('是否使用');
            $table->dateTime('used_at')->nullable()->comment('使用时间');
            $table->timestamp('created_at')->useCurrent();

            $table->unique('token', 'uk_token');
            $table->index(['activity_id', 'user_id'], 'idx_activity_user');
            $table->index('expire_at', 'idx_expire');
            
            $table->comment('秒杀令牌表');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ih_seckill_token');
    }
};