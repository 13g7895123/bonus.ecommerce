<?php

namespace App\Services;

use App\Repositories\MileageRewardProductRepository;

class MileageRewardProductService
{
    public function __construct(
        private readonly MileageRewardProductRepository $repo = new MileageRewardProductRepository(),
    ) {}

    public function getActiveProducts(): array
    {
        return $this->repo->getActive();
    }

    public function getActiveByItemId(int $itemId): array
    {
        return $this->repo->getActiveByItemId($itemId);
    }

    public function getAllByItemId(int $itemId): array
    {
        return $this->repo->getAllByItemId($itemId);
    }

    public function getAllProducts(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function create(array $data): array
    {
        $allowed = ['mileage_item_id', 'name', 'image_url', 'price', 'mileage_amount', 'miles_points', 'stock', 'purchase_target', 'is_active', 'sort_order'];
        $insert  = array_intersect_key($data, array_flip($allowed));

        $id = $this->repo->create($insert);
        return ['success' => true, 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        $item = $this->repo->find($id);
        if (!$item) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        $allowed = ['mileage_item_id', 'name', 'image_url', 'price', 'mileage_amount', 'miles_points', 'stock', 'purchase_target', 'is_active', 'sort_order'];
        $update  = array_intersect_key($data, array_flip($allowed));

        $this->repo->update($id, $update);
        return ['success' => true];
    }

    public function delete(int $id): array
    {
        $item = $this->repo->find($id);
        if (!$item) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        $this->repo->delete($id);
        return ['success' => true];
    }
}
