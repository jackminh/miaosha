<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jackminh\Miaosha\Scopes\SeckillTokenScope;


class SeckillToken extends BaseModel
{

	protected $fillable = [
		"activity_id",
		"user_id",
		"token",
		"expire_at",
		"is_used",
		"used_at"
	];
	protected $casts = [
		"activity_id" => "integer",
		"user_id"	  => "integer",
		"token"	      => "string",
		"expire_at"   => "datetime",
		"used_at"     => "datetime"
	];

	//注册全局查询领域
    protected static function booted():void
    {
        static::addGlobalScope(new SeckillTokenScope);
    } 

    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * 是否已使用
     * @return boolean [description]
     */
    public function isUsed():bool
    {
    	return $this->is_used ? true : false; 
    }

    /**
     * 关联活动
    */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(SeckillActivity::class, 'activity_id');
    }

    



}