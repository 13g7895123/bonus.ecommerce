<?php

namespace App\Models;

use CodeIgniter\Model;

class MileageRedemptionItemModel extends Model
{
    protected $table         = 'mileage_redemption_items';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name', 'short_desc', 'details', 'logo_letter', 'logo_color',
        'is_featured', 'featured_label', 'is_active', 'sort_order',
    ];

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
