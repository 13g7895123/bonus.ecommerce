<?php

namespace App\Repositories;

use App\Models\MileageRedemptionItemModel;

class MileageRedemptionItemRepository
{
    public function __construct(private readonly MileageRedemptionItemModel $model = new MileageRedemptionItemModel()) {}

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function getActive(): array
    {
        return $this->model->getActive();
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
