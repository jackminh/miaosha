<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ih_store', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn');
            $table->integer('user_id');
            $table->integer('goods_id'),
            $table->integer('status'),
            $table->decimal('price'),
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ih_store');
    }
};