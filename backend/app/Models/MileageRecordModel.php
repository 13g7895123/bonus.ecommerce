<?php

namespace App\Models;

use CodeIgniter\Model;

class MileageRecordModel extends Model
{
    protected $table         = 'mileage_records';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'type', 'amount', 'source', 'created_at'];

    /**
     * @return array{items: array, total: int}
     */
    public function getByUserId(int $userId, int $page = 1, int $limit = 20): array
    {
        $total = $this->where('user_id', $userId)->countAllResults(false);
        $items = $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->findAll();

        return ['items' => $items, 'total' => $total];
    }

    public function insert($data = null, bool $returnID = true): bool|int|string
    {
        if (is_array($data) && empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return parent::insert($data, $returnID);
    }
}
