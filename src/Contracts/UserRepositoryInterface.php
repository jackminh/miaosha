<?php

namespace Jackminh\Miaosha\Contracts;

interface UserRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
}