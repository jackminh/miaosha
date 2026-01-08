<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Support\Str;
use Jackminh\Miaosha\Traits\ActivityStatus;

class SeckillActivity extends BaseModel
{
    use ActivityStatus;

    /**
     * 批量赋值字段
     */
     protected $fillable = [
        'name', 'goods_id', 'total_stock', 'available_stock', 'original_price' ,'status',
        'seckill_price', 'start_time', 'end_time','limit_per_user'
    ];

    protected $casts = [
        'goods_id'      => 'integer',
        'status'    => 'integer',
        'limit_per_user'     => 'integer',
        'version'       => 'integer'
    ];









}