<?php

namespace App\Models;

use CodeIgniter\Model;

class SkywardsBenefitModel extends Model
{
    protected $table         = 'skywards_benefits';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['type', 'tier', 'label', 'image_url', 'content', 'sort_order', 'is_active'];

    public function getActive(?string $tier = null): array
    {
        $builder = $this->where('is_active', 1)
            ->where('type', 'rule');

        if ($tier !== null) {
            $builder->where('tier', $tier);
        }

        return $builder
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getAll(): array
    {
        return $this->where('type', 'rule')->orderBy('sort_order', 'ASC')->findAll();
    }
}
