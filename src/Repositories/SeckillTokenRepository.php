<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\SeckillToken;
use Jackminh\Miaosha\Contracts\SeckillTokenRepositoryInterface;

class SeckillTokenRepository implements SeckillTokenRepositoryInterface
{
    /**
     * 获取所有token
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = SeckillToken::query();
        
        if (!$withScopes) {
            $query->withoutGlobalScopes();
        }
        
        return $query->get();
    }
    /**
     * 查找指定token
     * @param  int    $id [description]
     * @return [type]     [description]
     */
    public function findById(int $id){
        $query = SeckillToken::query();
        return $query->find($id); 
    }

    /**
     * 创建token
     * @param  array  $tokenData [description]
     * @return [type]            [description]
     */
    public function createSeckillToken(array $tokenData): SeckillToken
    {

        $seckillToken = SeckillToken::create([
            'activity_id'    => $tokenData['activity_id'],
            'user_id'        => $tokenData['user_id'],
            'token'          => $tokenData['token'],
            'expire_at'      => $tokenData['expire_at'],
            'is_used'        => $tokenData['is_used'],
            'used_at'        => $tokenData['used_at']
        ]);
        return $seckillToken;
    }


    
  
}