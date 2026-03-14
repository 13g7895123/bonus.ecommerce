<?php

namespace App\Repositories;

use App\Models\CustomerServiceMessageModel;
use App\Repositories\Interfaces\CustomerServiceMessageRepositoryInterface;

class CustomerServiceMessageRepository implements CustomerServiceMessageRepositoryInterface
{
    public function __construct(private readonly CustomerServiceMessageModel $model = new CustomerServiceMessageModel()) {}

    public function getByTicket(string $ticketId, int $page, int $limit): array
    {
        return $this->model->getByTicket($ticketId, $page, $limit);
    }

    public function getOrCreateTicket(int $userId): string
    {
        return $this->model->getOrCreateTicket($userId);
    }

    public function create(array $data): int
    {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        return (int) $this->model->insert($data, true);
    }

    /**
     * Batch insert messages — single INSERT (no loop queries).
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
}
