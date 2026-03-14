<?php

namespace App\Repositories;

use App\Models\UserWalletModel;
use App\Repositories\Interfaces\UserWalletRepositoryInterface;

class UserWalletRepository implements UserWalletRepositoryInterface
{
    public function __construct(private readonly UserWalletModel $model = new UserWalletModel()) {}

    public function findByUserId(int $userId): ?array
    {
        return $this->model->findByUserId($userId);
    }

    public function create(array $data): int
    {
        return (int) $this->model->insert($data, true);
    }

    public function updateByUserId(int $userId, array $data): bool
    {
        return $this->model->updateByUserId($userId, $data);
    }

    /**
     * Batch update wallets by user_id — single updateBatch call (no loop queries).
     * Each row must have a 'user_id' key.
     */
    public function updateBatchByUserIds(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }
        // updateBatch uses 'user_id' as the index column
        return (bool) $this->model->updateBatch($rows, 'user_id');
    }
}
