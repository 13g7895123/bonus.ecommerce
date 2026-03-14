<?php

namespace App\Repositories;

use App\Models\MileageRecordModel;
use App\Repositories\Interfaces\MileageRecordRepositoryInterface;

class MileageRecordRepository implements MileageRecordRepositoryInterface
{
    public function __construct(private readonly MileageRecordModel $model = new MileageRecordModel()) {}

    public function create(array $data): int
    {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        return (int) $this->model->insert($data, true);
    }

    /**
     * Batch insert mileage records — single INSERT (no loop queries).
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

    public function getByUserId(int $userId, int $page, int $limit): array
    {
        return $this->model->getByUserId($userId, $page, $limit);
    }
}
