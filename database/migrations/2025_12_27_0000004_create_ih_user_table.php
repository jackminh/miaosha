<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_user', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->comment('会员码');
            $table->string('nickname')->comment('用户昵称');
            $table->string('avatar')->comment('用户头像');
            $table->string('mobile')->comment('手机号');
            $table->string('real_name')->comment('真实姓名');
            $table->integer('group_id')->comment('组id');
            $table->tinyInteger('sex')->comment('性别');
            $table->decimal('user_money')->comment('用户余额');
            $table->string('account')->comment('登录账号');
            $table->string('password')->comment('密码');
            $table->string('login_ip')->comment('登录ip');
            $table->integer('disable')->comment('是否禁用');
            $table->integer('del')->comment('是否删除');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_user');
    }
};
