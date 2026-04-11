<?php

namespace App\Models;

use CodeIgniter\Model;

class MailModel extends Model
{
    protected $table         = 'mails';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['subject', 'content', 'is_active', 'sort_order'];

    public function listActive(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function listAll(): array
    {
        return $this->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
