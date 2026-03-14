<?php

namespace App\Repositories;

use App\Models\AnnouncementModel;
use App\Repositories\Interfaces\AnnouncementRepositoryInterface;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function __construct(private readonly AnnouncementModel $model = new AnnouncementModel()) {}

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function getPublished(int $page, int $limit): array
    {
        return $this->model->getPublished($page, $limit);
    }

    public function create(array $data): int
    {
        return (int) $this->model->insert($data, true);
    }

    /**
     * Batch insert announcements — single INSERT (no loop queries).
     */
    public function createBatch(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }
        return (bool) $this->model->insertBatch($rows);
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->model->update($id, $data);
    }
}
