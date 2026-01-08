<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Jackminh\Miaosha\Traits\HasStatus;
use Jackminh\Miaosha\Scopes\GoodsScope;

class Goods extends BaseModel
{
    use SoftDeletes, HasStatus;

    /**
     * 批量赋值字段
     */
     protected $fillable = [
        'name', 'code', 'shop_id', 'type', 'status',
        'image', 'stock', 'content'
    ];

    protected $casts = [
        'type'      => 'integer',
        'status'    => 'integer',
        'stock'     => 'integer',
        'del'       => 'integer'
    ];

    protected $appends = [
        'image_url',
        'status_text'
    ];


    protected static function booted():void
    {
        static::addGlobalScope(new GoodsScope);
    }


    /**
     * 访问器 - 图片完整URL
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return config('miaosha.default.default_image');
        }
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    /**
     * 关联 - 商品规格（一对多）
     */
    public function goodsItems()
    {
        return $this->hasMany(GoodsItem::class, 'goods_id');
    }

    /**
     * 检查是否有库存
     */
    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * 减少库存
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }
        
        $this->stock -= $quantity;
        return $this->save();
    }

    /**
     * 增加库存
     */
    public function increaseStock(int $quantity): bool
    {
        $this->stock += $quantity;
        return $this->save();
    }


}