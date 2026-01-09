<?php

namespace Jackminh\Miaosha\Contracts;

interface InventoryLogRepositoryInterface
{
    public function getAll(bool $withScopes = true);
    public function findById(int $id);
}