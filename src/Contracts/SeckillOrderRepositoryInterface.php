<?php

namespace Jackminh\Miaosha\Contracts;

interface SeckillOrderRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
}