<?php

namespace App\Repositories\Interfaces;

interface UserWalletRepositoryInterface
{
    public function findByUserId(int $userId): ?array;
    public function create(array $data): int;
    public function updateByUserId(int $userId, array $data): bool;
    public function updateBatchByUserIds(array $rows): bool;
}
