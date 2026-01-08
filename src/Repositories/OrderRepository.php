<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\Goods;
use Jackminh\Miaosha\Models\GoodsItem;
use Jackminh\Miaosha\Models\Order;
use Jackminh\Miaosha\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * 获取所有商品规格
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        
    }

    /**
     * 查找指定商品
     * @param  int    $id [description]
     * @return [type]     [description]
     */
    public function findById(int $id){
        $query = GoodsItem::query();
        return $query->find($id); 
    }

    
  
}