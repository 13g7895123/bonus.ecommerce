<?php

namespace App\Repositories\Interfaces;

interface AnnouncementRepositoryInterface
{
    public function find(int $id): ?array;
    public function getPublished(int $page, int $limit): array;
    public function create(array $data): int;
    public function createBatch(array $rows): bool;
    public function update(int $id, array $data): bool;
}
