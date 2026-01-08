<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jackminh\Miaosha\Scopes\SeckillOrderScope;



class SeckillOrder extends BaseModel
{

    protected $fillable = [
        'order_sn',
        'activity_id',
        'user_id',
        'goods_id',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'pay_time',
        'cancel_time',
        'cancel_reason',
        'ip_address',
        'user_agent',
    ];
    
    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'pay_time' => 'datetime',
        'cancel_time' => 'datetime',
        'quantity' => 'integer',
    ];


    //注册全局查询领域
    protected static function booted():void
    {
        static::addGlobalScope(new SeckillOrderScope);
    } 
    
    /**
     * 关联活动
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(SeckillActivity::class, 'activity_id');
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
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        $statusMap = [
            0 => '待支付',
            1 => '支付成功',
            2 => '已取消',
            3 => '超时关闭',
        ];
        
        return $statusMap[$this->status] ?? '未知状态';
    }
    
    /**
     * 检查订单是否可支付
     */
    public function canPay(): bool
    {
        return $this->status === 0; // 只有待支付状态可以支付
    }
    
    /**
     * 检查订单是否可取消
     */
    public function canCancel(): bool
    {
        return $this->status === 0; // 只有待支付状态可以取消
    }
    
    /**
     * 获取支付超时时间
     */
    public function getPaymentTimeoutAt(): ?Carbon
    {
        if (!$this->created_at) {
            return null;
        }
        
        $timeoutMinutes = $this->activity->pay_timeout ?? 30;
        return $this->created_at->addMinutes($timeoutMinutes);
    }
    
    /**
     * 检查是否已超时
     */
    public function isTimeout(): bool
    {
        if ($this->status !== 0) {
            return false;
        }
        
        $timeoutAt = $this->getPaymentTimeoutAt();
        return $timeoutAt && $timeoutAt->lt(Carbon::now());
    }
}