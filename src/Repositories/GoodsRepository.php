<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\Goods;
use Jackminh\Miaosha\Contracts\GoodsRepositoryInterface;

class GoodsRepository implements GoodsRepositoryInterface
{
    /**
     * 获取所有商品
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = Goods::query();
        
        if (!$withScopes) {
            $query->withoutGlobalScopes();
        }
        
        return $query->get();
    }

    /**
     * 查找指定商品
     * @param  int    $id [description]
     * @return [type]     [description]
     */
    public function findById(int $id){
        $query = Goods::query();
        return $query->find($id); 
    }
    
  
}