<?php

namespace App\Models;

use CodeIgniter\Model;

class UserMailModel extends Model
{
    protected $table         = 'user_mails';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'mail_id', 'subject', 'content', 'is_read'];

    public function listForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
