<?php

namespace App\Services;

use App\Repositories\MileageRedemptionItemRepository;

class MileageRedemptionItemService
{
    public function __construct(
        private readonly MileageRedemptionItemRepository $repo = new MileageRedemptionItemRepository(),
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
        $allowed = ['name', 'short_desc', 'details', 'logo_letter', 'logo_color',
                    'is_featured', 'featured_label', 'is_active', 'sort_order'];
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

        $allowed = ['name', 'short_desc', 'details', 'logo_letter', 'logo_color',
                    'is_featured', 'featured_label', 'is_active', 'sort_order'];
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
