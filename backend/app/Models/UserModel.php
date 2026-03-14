<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'email', 'password_hash', 'full_name', 'phone',
        'avatar', 'dob', 'country',
        'tier', 'role', 'is_verified', 'verify_status',
        'verification_data', 'status',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }
}
