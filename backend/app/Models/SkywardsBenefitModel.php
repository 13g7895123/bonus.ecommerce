<?php

namespace App\Models;

use CodeIgniter\Model;

class SkywardsBenefitModel extends Model
{
    protected $table         = 'skywards_benefits';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['type', 'label', 'content', 'sort_order', 'is_active'];

    public function getActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getAll(): array
    {
        return $this->orderBy('sort_order', 'ASC')->findAll();
    }
}
