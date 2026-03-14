<?php

namespace App\Repositories;

use App\Models\ApiLogModel;
use App\Repositories\Interfaces\ApiLogRepositoryInterface;

class ApiLogRepository implements ApiLogRepositoryInterface
{
    public function __construct(private readonly ApiLogModel $model = new ApiLogModel()) {}

    public function create(array $data): int
    {
        return (int) $this->model->insert($data, true);
    }

    public function paginate(int $page, int $limit, array $filters = []): array
    {
        return $this->model->getPaginated($page, $limit, $filters);
    }
}
