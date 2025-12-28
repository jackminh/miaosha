<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\Goods;
use Jackminh\Miaosha\Models\GoodsItem;
use Jackminh\Miaosha\Contracts\GoodsItemRepositoryInterface;

class GoodsItemRepository implements GoodsItemRepositoryInterface
{
    /**
     * 获取所有商品规格
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = GoodsItem::query();
        
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
        $query = GoodsItem::query();
        return $query->find($id); 
    }
    /**
     * 
     * @param  int    $goodsId [description]
     * @return [type]          [description]
     */
    public function getGoodsItem(int $goodsId){
        return GoodsItem::where("goods_id",$goodsId)->get();
    }
    
  
}