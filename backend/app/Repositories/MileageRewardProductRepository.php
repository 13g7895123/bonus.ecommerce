<?php

namespace App\Repositories;

use App\Models\MileageRewardProductModel;

class MileageRewardProductRepository
{
    public function __construct(private readonly MileageRewardProductModel $model = new MileageRewardProductModel()) {}

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function getActive(): array
    {
        return $this->model->getActive();
    }

    public function getActiveByItemId(int $itemId): array
    {
        return $this->model->getActiveByItemId($itemId);
    }

    public function getAllByItemId(int $itemId): array
    {
        return $this->model->getAllByItemId($itemId);
    }

    public function getAll(): array
    {
        return $this->model->getAll();
    }

    public function create(array $data): int
    {
        return (int) $this->model->insert($data, true);
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->model->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->delete($id);
    }
}
