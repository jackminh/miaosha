<?php

namespace Jackminh\Miaosha\Contracts;

interface GoodsItemRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
    
}