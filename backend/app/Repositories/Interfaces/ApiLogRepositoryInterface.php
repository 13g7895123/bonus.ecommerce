<?php

namespace App\Repositories\Interfaces;

interface ApiLogRepositoryInterface
{
    public function create(array $data): int;
    public function paginate(int $page, int $limit, array $filters = []): array;
}
