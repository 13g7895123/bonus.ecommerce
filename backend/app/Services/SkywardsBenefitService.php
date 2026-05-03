<?php

namespace App\Services;

use App\Repositories\SkywardsBenefitRepository;

class SkywardsBenefitService
{
    public function __construct(
        private readonly SkywardsBenefitRepository $repo = new SkywardsBenefitRepository(),
    ) {}

    public function getActiveItems(?string $tier = null): array
    {
        return $this->repo->getActive($this->normalizeTier($tier));
    }

    public function getActiveItemForTier(?string $tier): ?array
    {
        $items = $this->getActiveItems($tier);
        return $items[0] ?? null;
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
        $allowed = ['tier', 'label', 'image_url', 'content', 'sort_order', 'is_active'];
        $insert  = array_intersect_key($data, array_flip($allowed));
        $insert['tier'] = $this->normalizeTier($insert['tier'] ?? null) ?? 'regular';
        $insert['type'] = 'rule';

        $id = $this->repo->create($insert);
        return ['success' => true, 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        $item = $this->repo->find($id);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }

        $allowed = ['tier', 'label', 'image_url', 'content', 'sort_order', 'is_active'];
        $update  = array_intersect_key($data, array_flip($allowed));
        if (array_key_exists('tier', $update)) {
            $update['tier'] = $this->normalizeTier($update['tier']) ?? 'regular';
        }
        $update['type'] = 'rule';

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

    private function normalizeTier(?string $tier): ?string
    {
        if ($tier === null || $tier === '') {
            return null;
        }

        if ($tier === 'blue') {
            return 'regular';
        }

        return in_array($tier, ['regular', 'silver', 'gold', 'platinum'], true) ? $tier : 'regular';
    }
}
