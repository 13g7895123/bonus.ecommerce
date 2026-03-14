<?php

namespace App\Repositories;

use App\Models\FileModel;
use App\Repositories\Interfaces\FileRepositoryInterface;

class FileRepository implements FileRepositoryInterface
{
    public function __construct(private readonly FileModel $model = new FileModel()) {}

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function findByUuid(string $uuid): ?array
    {
        return $this->model->findByUuid($uuid);
    }

    public function findByUserId(int $userId, ?string $type = null): array
    {
        return $this->model->findByUserId($userId, $type);
    }

    public function create(array $data): int
    {
        $this->model->insert($data);
        return (int) $this->model->getInsertID();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->delete($id);
    }
}
