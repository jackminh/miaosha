<?php

namespace Jackminh\Miaosha\Traits;

trait ActivityStatus
{
    /**
     * 状态映射
     */
    public static function getActivityStatuses(): array
    {
        return [
            0 => '未开始',
            1 => '进行中',
            2 => '已结束',
            3 => '已取消'
        ];
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        return self::getActivityStatuses()[$this->status] ?? '未知状态';
    }

    
}