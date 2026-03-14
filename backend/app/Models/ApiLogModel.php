<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiLogModel extends Model
{
    protected $table         = 'api_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'method', 'uri', 'ip_address', 'user_id',
        'request_headers', 'request_body',
        'response_code', 'response_body',
        'duration_ms', 'created_at',
    ];

    public function getPaginated(int $page, int $limit, array $filters = []): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if (!empty($filters['method'])) {
            $builder->where('method', strtoupper($filters['method']));
        }
        if (!empty($filters['uri'])) {
            $builder->like('uri', $filters['uri']);
        }
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', (int) $filters['user_id']);
        }
        if (!empty($filters['response_code'])) {
            $builder->where('response_code', (int) $filters['response_code']);
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
