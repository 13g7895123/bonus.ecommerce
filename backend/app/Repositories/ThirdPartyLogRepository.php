<?php

namespace App\Repositories;

use App\Models\ThirdPartyLogModel;

class ThirdPartyLogRepository
{
    public function __construct(private readonly ThirdPartyLogModel $model = new ThirdPartyLogModel()) {}

    public function create(array $data): int
    {
        return (int) $this->model->insert($data, true);
    }

    public function paginate(int $page, int $limit, array $filters = []): array
    {
        return $this->model->getPaginated($page, $limit, $filters);
    }
}
