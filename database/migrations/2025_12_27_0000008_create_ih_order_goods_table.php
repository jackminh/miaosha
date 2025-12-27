<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ls_order_goods', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('订单id');
            $table->integer('shop_id')->comment('店铺id');
            $table->string('goods_id')->comment('商品id');
            $table->integer('item_id')->comment('规格id');
            $table->decimal('goods_price')->comment('商品价格');
            $table->decimal('total_pay_price')->comment('实际支付商品金额');
            $table->decimal('total_price')->comment('商品总价');
            $table->integer('goods_num')->comment('商品数量');
            $table->string('goods_name')->comment('商品名称');
            $table->tinyInteger('shipping_status')->comment('0-未发货;1-已发货');
            $table->tinyInteger('refund_status')->comment('退款状态：0-未退款；1-部分退款；2-全部退款');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ls_order_goods');
    }
};

CREATE TABLE ``  (

  
  `discount_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '优惠金额',
  `spec_value` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品规格值',
  `spec_value_ids` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品规格id',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品主图',
  `weight` decimal(10, 3) UNSIGNED NULL DEFAULT NULL COMMENT '重量',
  
  `delivery_id` int(11) NULL DEFAULT 0 COMMENT '发货单ID',
  `refund_status` tinyint(1) NULL DEFAULT 0 COMMENT '售后状态;0-未申请退款;1-申请退款;2-等待退款;3-退款成功;',
  `is_comment` tinyint(1) NULL DEFAULT 0 COMMENT '是否已评论；0-否；1-是',
  `commission_ratio` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '抽成比例',
  `create_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_orderid_iscomment`(`order_id`, `is_comment`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单商品表';
