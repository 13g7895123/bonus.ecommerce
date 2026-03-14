<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table         = 'files';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'uuid',
        'user_id',
        'type',
        'original_name',
        'stored_name',
        'path',
        'url',
        'mime_type',
        'size',
        'is_public',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByUuid(string $uuid): ?array
    {
        return $this->where('uuid', $uuid)->first();
    }

    public function findByUserId(int $userId, string $type = null): array
    {
        $q = $this->where('user_id', $userId);
        if ($type) {
            $q->where('type', $type);
        }
        return $q->orderBy('created_at', 'DESC')->findAll();
    }
}
