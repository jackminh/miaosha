<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsItem extends BaseModel
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'stock',
        'price',
        'market_price',
        'goods_id',
        'image',
        'bar_code'
    ];
    
    protected $casts = [
        'stock' => 'integer',
        'price' => 'decimal:2',
        'market_price' => 'decimal:2',
        'goods_id' => 'integer',
    ];
    
    protected $appends = [
        'discount_rate'
    ];
    
    /**
     * 关联 - 所属商品
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }
    
    /**
     * 访问器 - 折扣率
     */
    public function getDiscountRateAttribute(): float
    {
        if ($this->market_price <= 0) {
            return 0;
        }
        
        $discount = (($this->market_price - $this->price) / $this->market_price) * 100;
        return round($discount, 1);
    }
    /**
     * 检查是否有库存
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }
}