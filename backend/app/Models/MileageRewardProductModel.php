<?php

namespace App\Models;

use CodeIgniter\Model;

class MileageRewardProductModel extends Model
{
    protected $table         = 'mileage_reward_products';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'mileage_item_id', 'name', 'image_url', 'price', 'mileage_amount', 'miles_points', 'stock', 'is_active', 'sort_order', 'display_style',
    ];

    public function getActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getActiveByItemId(int $itemId): array
    {
        return $this->where('is_active', 1)
            ->where('mileage_item_id', $itemId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getAll(): array
    {
        return $this->orderBy('sort_order', 'ASC')->findAll();
    }
}
