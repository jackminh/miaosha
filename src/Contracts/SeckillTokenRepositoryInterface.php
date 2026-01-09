<?php

namespace Jackminh\Miaosha\Contracts;

interface SeckillTokenRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
}