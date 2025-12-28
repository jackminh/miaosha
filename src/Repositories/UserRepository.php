<?php

namespace Jackminh\Miaosha\Repositories;

use Jackminh\Miaosha\Models\User;
use Jackminh\Miaosha\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * 获取所有用户
     * @param  bool|boolean $withScopes [description]
     * @return [type]                   [description]
     */
    public function getAll(bool $withScopes = true)
    {
        $query = User::query();
        
        if (!$withScopes) {
            $query->withoutGlobalScopes();
        }
        
        return $query->get();
    }
    /**
     * 查找指定用户
     * @param  int    $id [description]
     * @return [type]     [description]
     */
    public function findById(int $id){
        $query = User::query();
        return $query->find($id); 
    }


    
  
}