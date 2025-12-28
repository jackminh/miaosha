<?php

namespace Jackminh\Miaosha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    /**
     * 包的表前缀
     */
    protected $prefix = 'ih_';

    /**
     * 自动设置表名
     */
    public function getTable()
    {
        if (!isset($this->table)) {
            $this->setTableFromClass();
        }

        return $this->table;
    }

    /**
     * 根据类名设置表名
     */
    protected function setTableFromClass(): void
    {
        $className = class_basename($this);
        $tableName = Str::snake($className);
        $this->table = $this->prefix . $tableName;
    }

    /**
     * 统一的时间格式
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}