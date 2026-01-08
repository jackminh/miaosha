<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\SeckillActivity;
use Jackminh\Miaosha\Contracts\SeckillActivityRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class SeckillActivityRepository implements SeckillActivityRepositoryInterface
{
    /**
     * 获取所有秒杀活动
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = SeckillActivity::query();
        
        if (!$withScopes) {
            $query->withoutGlobalScopes();
        }
        
        return $query->get();
    }

    /**
     * 查找指定秒杀活动
     * @param  int    $id [description]
     * @return [type]     [description]
     */
    public function findById(int $id){
        $query = SeckillActivity::query();
        return $query->find($id); 
    }

    /**
     * 标记为已预热
     * @param  int    $activityId [description]
     * @return [type]             [description]
     */
    public function markAsPreheated(int $activityId)
    {
        $activity = SeckillActivity::find($activityId);
        $activity->is_preheat = 1;
        $activity->save();
    }




    
  
}