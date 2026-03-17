<?php

namespace App\Services;

use App\Repositories\SkywardsBenefitRepository;

class SkywardsBenefitService
{
    public function __construct(
        private readonly SkywardsBenefitRepository $repo = new SkywardsBenefitRepository(),
    ) {}

    public function getActiveItems(): array
    {
        return $this->repo->getActive();
    }

    public function getAllItems(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function create(array $data): array
    {
        $allowed = ['type', 'label', 'content', 'sort_order', 'is_active'];
        $insert  = array_intersect_key($data, array_flip($allowed));

        $id = $this->repo->create($insert);
        return ['success' => true, 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        $item = $this->repo->find($id);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }

        $allowed = ['type', 'label', 'content', 'sort_order', 'is_active'];
        $update  = array_intersect_key($data, array_flip($allowed));

        $this->repo->update($id, $update);
        return ['success' => true];
    }

    public function delete(int $id): array
    {
        $item = $this->repo->find($id);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        $this->repo->delete($id);
        return ['success' => true];
    }
}
