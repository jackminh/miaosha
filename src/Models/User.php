<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jackminh\Miaosha\Scopes\UserScope;
use Jackminh\Miaosha\Traits\HasSex;
class User extends BaseModel 
{
    use SoftDeletes, HasSex;

    protected $fillable = [
        'sn',
        'nickname',
        'avatar',
        'mobile',
        'real_name',
        'group_id',
        'sex',
        'user_money',
        'account',
        'password',
        'login_ip'
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'sex' => 'integer',
        'group_id' => 'integer',
        'user_money' => 'decimal:2',
        'disable' => 'boolean',
        'del' => 'boolean',
        'email_verified_at' => 'datetime',
    ];
    
    protected $appends = [
        'avatar_url',
        'sex_text'
    ];
    
    protected static function booted():void
    {
        static::addGlobalScope(new UserScope);
    }

    /**
     * 访问器 - 头像完整URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return config('miaosha.default_avatar');
        }
        
        if (strpos($this->avatar, 'http') === 0) {
            return $this->avatar;
        }
        
        return asset('storage/' . $this->avatar);
    }
    
    /**
     * 检查用户是否可用
     */
    public function isActive(): bool
    {
        return $this->disable == 0 && $this->del == 0;
    }
    
    /**
     * 检查余额是否足够
     */
    public function hasEnoughMoney(float $amount): bool
    {
        return $this->user_money >= $amount;
    }
    
    /**
     * 扣款
     */
    public function deductMoney(float $amount): bool
    {
        if (!$this->hasEnoughMoney($amount)) {
            return false;
        }
        $this->user_money -= $amount;
        return $this->save();
    }
    
    /**
     * 充值
     */
    public function rechargeMoney(float $amount): bool
    {
        $this->user_money += $amount;
        return $this->save();
    }


}