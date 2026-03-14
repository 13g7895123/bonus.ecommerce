<?php

namespace App\Repositories\Interfaces;

interface TransactionRepositoryInterface
{
    public function create(array $data): int;
    public function createBatch(array $rows): bool;
    public function getByUserId(int $userId, ?string $type, int $page, int $limit): array;
}
