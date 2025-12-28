<?php

namespace Jackminh\Miaosha\Traits;

trait HasStatus
{
    /**
     * 状态映射
     */
    public static function getStatuses(): array
    {
        return [
            0 => '仓库中',
            1 => '上架中',
            2 => '已下架',
        ];
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? '未知状态';
    }

    /**
     * 是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === 1;
    }
    
}