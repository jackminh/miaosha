<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\InventoryLog;
use Jackminh\Miaosha\Contracts\InventoryLogRepositoryInterface;

class InventoryLogRepository implements InventoryLogRepositoryInterface
{
    /**
     * 获取所有商品
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = InventoryLog::query();
        
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
        $query = InventoryLog::query();
        return $query->find($id); 
    }

    /**
     * 创建 inventory log
     * @param  array  $inventoryLogData [description]
     * @return [type]            [description]
     */
    public function createInventoryLog(array $inventoryLogData): InventoryLog
    {

        $inventoryLog = InventoryLog::create([
            'activity_id'       => $inventoryLogData['activity_id'],
            'user_id'           => $inventoryLogData['user_id'],
            'order_sn'          => $inventoryLogData['order_sn'],
            'change_type'       => $inventoryLogData['change_type'],
            'change_quantity'   => $inventoryLogData['change_quantity'],
            'before_quantity'   => $inventoryLogData['before_quantity'],
            'after_quantity'    => $inventoryLogData['after_quantity'],
            'remark'            => $inventoryLogData['remark']
        ]); 
        return $inventoryLog;
    }
    
  
}