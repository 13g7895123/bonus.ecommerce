<?php

namespace App\Models;

use CodeIgniter\Model;

class UserWalletModel extends Model
{
    protected $table         = 'user_wallets';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'balance', 'miles_balance',
        'withdrawal_password_hash',
        'bank_name', 'bank_account', 'bank_account_name',
    ];

    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    public function updateByUserId(int $userId, array $data): bool
    {
        return $this->where('user_id', $userId)->set($data)->update();
    }
}
