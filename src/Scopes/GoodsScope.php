<?php

namespace Jackminh\Miaosha\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GoodsScope implements Scope
{
    /**
     * 应用作用域
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 默认只查询未删除的商品
        $builder->where('del', 0);
    }

    /**
     * 扩展查询构造器
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withStock', function (Builder $builder) {
            return $builder->where('stock', '>', 0);
        });
        
        $builder->macro('onSale', function (Builder $builder) {
            return $builder->where('status', 1);
        });

    }
}