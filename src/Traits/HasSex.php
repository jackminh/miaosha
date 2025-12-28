<?php

namespace Jackminh\Miaosha\Traits;

trait HasSex
{
    /**
     * 性别
     */
    public static function getSex(): array
    {
        return [
            0 => '男',
            1 => '女'
        ];
    }
    /**
     * 获取性别
     */
    public function getSexTextAttribute(): string
    {
        return self::getSex()[$this->sex] ?? '未知';
    }

    
}