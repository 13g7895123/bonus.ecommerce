<?php

namespace App\Repositories;

use App\Models\MileageRewardOrderModel;

class MileageRewardOrderRepository
{
    public function __construct(private readonly MileageRewardOrderModel $model = new MileageRewardOrderModel()) {}

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function getByUserId(int $userId): array
    {
        return $this->model->getByUserId($userId);
    }

    public function getPendingByUserId(int $userId): array
    {
        return $this->model->getPendingByUserId($userId);
    }

    public function getAll(string $status = ''): array
    {
        return $this->model->getAll($status);
    }

    public function create(array $data): int
    {
        return (int) $this->model->insert($data, true);
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->model->update($id, $data);
    }
}
