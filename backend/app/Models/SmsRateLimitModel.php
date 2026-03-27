<?php

namespace App\Models;

use CodeIgniter\Model;

class SmsRateLimitModel extends Model
{
    protected $table         = 'sms_rate_limits';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['ip', 'window_start', 'send_count', 'blocked_until'];

    public function findByIp(string $ip): ?array
    {
        return $this->where('ip', $ip)->first();
    }
}
