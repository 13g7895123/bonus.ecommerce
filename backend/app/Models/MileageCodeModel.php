<?php

namespace App\Models;

use CodeIgniter\Model;

class MileageCodeModel extends Model
{
    protected $table      = 'mileage_codes';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'code', 'description', 'miles_amount',
        'usage_limit', 'used_count', 'is_active', 'expires_at',
    ];
    protected $useTimestamps = true;

    /**
     * 查找有效代碼（啟用、未超出使用次數、未過期）
     */
    public function findValidCode(string $code): ?array
    {
        $escaped = $this->db->escape($code);
        return $this
            ->where("BINARY `code` = {$escaped}", null, false)
            ->where('is_active', 1)
            ->groupStart()
                ->where('usage_limit IS NULL')
                ->orWhere('used_count < usage_limit')
            ->groupEnd()
            ->groupStart()
                ->where('expires_at IS NULL')
                ->orWhere('expires_at >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->first();
    }

    public function incrementUsed(int $id): void
    {
        $this->set('used_count', 'used_count + 1', false)->update($id);
    }
}
