<?php

namespace App\Repositories\Interfaces;

interface FileRepositoryInterface
{
    public function find(int $id): ?array;
    public function findByUuid(string $uuid): ?array;
    public function findByUserId(int $userId, ?string $type = null): array;
    public function create(array $data): int;
    public function delete(int $id): bool;
}
