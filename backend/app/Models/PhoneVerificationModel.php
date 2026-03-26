<?php

namespace App\Models;

use CodeIgniter\Model;

class PhoneVerificationModel extends Model
{
    protected $table         = 'phone_verifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'phone', 'code', 'attempts',
        'is_used', 'expires_at', 'verified_at',
    ];

    /**
     * 取得指定電話號碼最新一筆有效的未使用驗證碼
     */
    public function findLatestValid(string $phone): ?array
    {
        return $this
            ->where('phone', $phone)
            ->where('is_used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();
    }
}
