<?php

namespace Jackminh\Miaosha\Traits;

trait OrderStatus
{

    // 订单状态常量
    const ORDER_STATUS_PENDING = 0;     // 待支付
    const ORDER_STATUS_PAID = 1;        // 已支付
    const ORDER_STATUS_SHIPPED = 2;     // 已发货
    const ORDER_STATUS_COMPLETED = 3;   // 已完成
    const ORDER_STATUS_CANCELLED = 4;   // 已取消
    
    // 支付状态常量
    const PAY_STATUS_PENDING = 0;       // 待支付
    const PAY_STATUS_PAID = 1;          // 已支付
    const PAY_STATUS_REFUNDED = 2;      // 已退款
    const PAY_STATUS_REFUND_REJECTED = 3; // 拒绝退款
    
    // 支付方式常量
    const PAY_WAY_WECHAT = 1;           // 微信支付
    const PAY_WAY_ALIPAY = 2;           // 支付宝支付
    const PAY_WAY_BALANCE = 3;          // 余额支付
    const PAY_WAY_OFFLINE = 4;          // 线下支付
    
    // 退款状态常量
    const REFUND_STATUS_NONE = 0;       // 未退款
    const REFUND_STATUS_PARTIAL = 1;    // 部分退款
    const REFUND_STATUS_FULL = 2;       // 全部退款

    /**
     * 获取订单状态文本
     */
    public function getOrderStatusTextAttribute(): string
    {
        $statusMap = [
            self::ORDER_STATUS_PENDING      => '待支付',
            self::ORDER_STATUS_PAID         => '已支付',
            self::ORDER_STATUS_SHIPPED      => '已发货',
            self::ORDER_STATUS_COMPLETED    => '已完成',
            self::ORDER_STATUS_CANCELLED    => '已取消',
        ];
        
        return $statusMap[$this->order_status] ?? '未知状态';
    }
    /**
     * 获取支付状态文本
     */
    public function getPayStatusTextAttribute(): string
    {
        $statusMap = [
            self::PAY_STATUS_PENDING         => '待支付',
            self::PAY_STATUS_PAID            => '已支付',
            self::PAY_STATUS_REFUNDED        => '已退款',
            self::PAY_STATUS_REFUND_REJECTED => '拒绝退款',
        ];
        
        return $statusMap[$this->pay_status] ?? '未知状态';
    }
    
    /**
     * 获取支付方式文本
     */
    public function getPayWayTextAttribute(): string
    {
        $wayMap = [
            self::PAY_WAY_WECHAT => '微信支付',
            self::PAY_WAY_ALIPAY => '支付宝支付',
            self::PAY_WAY_BALANCE => '余额支付',
            self::PAY_WAY_OFFLINE => '线下支付',
        ];
        
        return $wayMap[$this->pay_way] ?? '未知支付方式';
    }

    
}