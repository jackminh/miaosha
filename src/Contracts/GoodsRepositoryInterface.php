<?php

namespace Jackminh\Miaosha\Contracts;

interface GoodsRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
}