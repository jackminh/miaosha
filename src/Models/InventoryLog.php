<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jackminh\Miaosha\Scopes\InventoryLogScope;


class InventoryLog extends BaseModel
{

	protected $fillable = [
		"activity_id",
		"order_sn",
		"user_id",
		"change_type",
		"change_quantity",
		"before_quantity",
		"after_quantity",
		"remark"
	];
	protected $casts = [
		"activity_id" => "integer",
		"order_sn"	  => "string",
		"user_id"	  => "integer",
		"change_type" => "integer",
		"change_quantity" => "integer",
		"before_quantity" => "integer",
		"after_quantity"  => "integer"
	];

    protected static function booted():void
    {
        static::addGlobalScope(new InventoryLogScope);
    }
    
    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 关联活动
    */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(SeckillActivity::class, 'activity_id');
    }


}