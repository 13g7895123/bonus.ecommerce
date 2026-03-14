<?php

namespace App\Repositories;

use App\Entities\UserEntity;
use App\Models\UserModel;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly UserModel $model = new UserModel()) {}

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->model->findByEmail($email);
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

    public function paginate(int $page, int $limit): array
    {
        $total = $this->model->countAll();
        $items = $this->model
            ->orderBy('created_at', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->findAll();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Batch insert — single INSERT with multiple rows (no loop queries).
     */
    public function createBatch(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }
        return (bool) $this->model->insertBatch($rows);
    }

    /**
     * Batch update — single UPDATE … CASE WHEN query (no loop queries).
     * Each row must contain the $indexColumn key.
     */
    public function updateBatch(array $rows, string $indexColumn = 'id'): bool
    {
        if (empty($rows)) {
            return true;
        }
        return (bool) $this->model->updateBatch($rows, $indexColumn);
    }
}
