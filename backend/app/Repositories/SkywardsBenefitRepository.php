<?php

namespace App\Repositories;

use App\Models\SkywardsBenefitModel;

class SkywardsBenefitRepository
{
    public function __construct(private readonly SkywardsBenefitModel $model = new SkywardsBenefitModel()) {}

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
