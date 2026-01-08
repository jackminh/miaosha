<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\SeckillOrder;
use Jackminh\Miaosha\Contracts\SeckillOrderRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class SeckillOrderRepository implements SeckillOrderRepositoryInterface
{
    /**
     * 
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = SeckillOrder::query();
        
        if (!$withScopes) {
            $query->withoutGlobalScopes();
        }
        
        return $query->get();
    }

    /**
     * @param  int    $id [description]
     * @return [type]     [description]
     */
    public function findById(int $id){
        $query = SeckillOrder::query();
        return $query->find($id); 
    }

    /**
     * 检查用户是否已购买
     */
    public function hasUserPurchased(int $userId, int $activityId,string $orderNo): bool
    {
        // 1. 检查数据库中的订单
        $existsInDB = SeckillOrder::where('user_id', $userId)
            ->where('activity_id', $activityId)
            ->where('order_sn',$orderNo)
            ->exists();
        if($existsInDB) {
            return true;
        }else{
            return false;
        }
    }
    /**
     * 创建订单
     * @param  array  $orderData [description]
     * @return [type]            [description]
     */
    public function createSeckillOrder(array $orderData): SeckillOrder
    {
        $order = SeckillOrder::create([
            'order_sn'       => $orderData['order_sn'],
            'activity_id'    => $orderData['activity_id'],
            'user_id'        => $orderData['user_id'],
            'goods_id'       => $orderData['goods_id'],
            'quantity'       => $orderData['quantity'],
            'unit_price'     => $orderData['unit_price'],
            'total_amount'   => $orderData['total_amount'],
            'status'         => $orderData['status'],  //状态：0-待支付 1-支付成功 2-已取消 3-超时关闭
            'ip_address'     => $orderData['ip_address'],
            'user_agent'     => $orderData['user_agent']
        ]);
        return $order;
    }




    
  
}