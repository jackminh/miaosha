<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Jackminh\Miaosha\Traits\OrderStatus;
use Jackminh\Miaosha\Scopes\OrderScope;

class Order extends BaseModel
{
    use SoftDeletes, OrderStatus;
    
    protected $fillable = [
        'trade_id',
        'shop_id',
        'order_sn',
        'user_id',
        'goods_id',
        'order_status',
        'pay_status',
        'pay_way',
        'pay_time',
        'goods_price',
        'order_amount',
        'total_amount',
        'total_num',
        'cancel_time',
        'refund_status',
    ];
    
    protected $casts = [
        'goods_price' => 'decimal:2',
        'order_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'pay_time' => 'datetime',
        'cancel_time' => 'datetime',
    ];
    //注册全局查询领域
    protected static function booted():void
    {
        static::addGlobalScope(new OrderScope);
    }
    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * 关联商品
     */
    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }
    
    /**
     * 检查订单是否可以取消
     */
    public function canCancel(): bool
    {
        return $this->pay_status == self::PAY_STATUS_PENDING 
            && $this->order_status == self::ORDER_STATUS_PENDING;
    }
    
    /**
     * 检查订单是否可以退款
     */
    public function canRefund(): bool
    {
        return $this->pay_status == self::PAY_STATUS_PAID 
            && $this->refund_status == self::REFUND_STATUS_NONE;
    }
}