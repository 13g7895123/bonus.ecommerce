<?php

namespace App\Repositories;

use App\Models\TransactionModel;
use App\Repositories\Interfaces\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private readonly TransactionModel $model = new TransactionModel()) {}

    public function create(array $data): int
    {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        return (int) $this->model->insert($data, true);
    }

    /**
     * Batch insert multiple transactions — single INSERT (no loop queries).
     */
    public function createBatch(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }
        $now = date('Y-m-d H:i:s');
        foreach ($rows as &$row) {
            $row['created_at'] ??= $now;
        }
        unset($row);
        return (bool) $this->model->insertBatch($rows);
    }

    public function getByUserId(int $userId, ?string $type, int $page, int $limit): array
    {
        return $this->model->getByUserId($userId, $type, $page, $limit);
    }
}
