<?php

namespace App\Repositories\Interfaces;

interface MileageRecordRepositoryInterface
{
    public function create(array $data): int;
    public function createBatch(array $rows): bool;
    public function getByUserId(int $userId, int $page, int $limit): array;
}
