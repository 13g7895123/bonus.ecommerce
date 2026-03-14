<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function find(int $id): ?array;
    public function findByEmail(string $email): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function paginate(int $page, int $limit): array;
    public function createBatch(array $rows): bool;
    public function updateBatch(array $rows, string $indexColumn = 'id'): bool;
}
