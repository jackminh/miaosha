<?php

namespace Jackminh\Miaosha\Contracts;

interface SeckillActivityRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
}