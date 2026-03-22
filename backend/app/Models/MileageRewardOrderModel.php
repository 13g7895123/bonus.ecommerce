<?php

namespace App\Models;

use CodeIgniter\Model;

class MileageRewardOrderModel extends Model
{
    protected $table         = 'mileage_reward_orders';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'product_id', 'product_name', 'product_image_url',
        'quantity', 'unit_price', 'unit_miles_points',
        'total_price', 'total_miles_points', 'mileage_reward_amount',
        'status', 'review_note', 'reviewed_at',
    ];

    public function getByUserId(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function getPendingByUserId(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'pending_review')
            ->findAll();
    }

    public function getAll(string $status = ''): array
    {
        $builder = $this->orderBy('created_at', 'DESC');
        if ($status) {
            $builder->where('status', $status);
        }
        return $builder->findAll();
    }
}
