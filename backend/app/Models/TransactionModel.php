<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table         = 'transactions';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id', 'type', 'amount', 'status',
        'description', 'reference_id', 'created_at',
    ];

    /**
     * @return array{items: array, total: int}
     */
    public function getByUserId(int $userId, ?string $type = null, int $page = 1, int $limit = 20): array
    {
        $builder = $this->where('user_id', $userId);
        if ($type) {
            $builder = $builder->where('type', $type);
        }
        $total = $builder->countAllResults(false);
        $items = $builder->orderBy('created_at', 'DESC')
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
