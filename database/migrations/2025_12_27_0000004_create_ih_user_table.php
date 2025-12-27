<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_user', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation("utf8mb4_unicode_ci");
            $table->comment('订单日志表');
            $table->id();
            $table->string('sn',length:32)->comment('会员码');
            $table->string('nickname',length:100)->nullable()->default('')->comment('用户昵称');
            $table->string('avatar',length:100)->nullable()->default('')->comment('用户头像');
            $table->string('mobile',length:25)->nullable()->default('')->comment('手机号');
            $table->string('real_name',length:100)->nullable()->default('')->comment('真实姓名');
            $table->integer('group_id')->nullable()->default(0)->comment('组id');
            $table->tinyInteger('sex')->nullable()->default(0)->comment('性别:0男，1:女');
            $table->decimal('user_money',total:8,places:2)->nullable()->default(0.00)->comment('用户余额');
            $table->string('account',length:32)->nullable()->default('')->comment('登录账号');
            $table->string('password',length:32)->nullable()->default('')->comment('密码');
            $table->string('login_ip',length:32)->nullable()->default('')->comment('登录ip');
            $table->integer('disable')->nullable()->default(0)->comment('是否禁用');
            $table->integer('del')->nullable()->default(0)->comment('是否删除');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_user');
    }
};
