<?php

namespace App\Models;

use CodeIgniter\Model;

class ThirdPartyLogModel extends Model
{
    protected $table         = 'third_party_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'service', 'action', 'method', 'url',
        'request_body', 'response_code', 'response_body',
        'duration_ms', 'success', 'error_message', 'created_at',
    ];

    public function getPaginated(int $page, int $limit, array $filters = []): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if (!empty($filters['service'])) {
            $builder->where('service', $filters['service']);
        }
        if (!empty($filters['action'])) {
            $builder->like('action', $filters['action']);
        }
        if (!empty($filters['response_code'])) {
            $builder->where('response_code', (int) $filters['response_code']);
        }
        if (isset($filters['success']) && $filters['success'] !== '') {
            $builder->where('success', (int) $filters['success']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        $total = $builder->countAllResults(false);
        $items = $builder->limit($limit, ($page - 1) * $limit)->findAll();

        return ['items' => $items, 'total' => $total];
    }
}
