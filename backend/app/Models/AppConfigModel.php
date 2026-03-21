<?php

namespace App\Models;

use CodeIgniter\Model;

class AppConfigModel extends Model
{
    protected $table      = 'app_configs';
    protected $primaryKey = 'id';

    protected $allowedFields = ['key', 'value'];
    protected $useTimestamps = true;

    public function getByKey(string $key): ?array
    {
        return $this->where('key', $key)->first();
    }

    public function setByKey(string $key, string $value): bool
    {
        $existing = $this->getByKey($key);
        if ($existing) {
            return (bool) $this->update($existing['id'], ['value' => $value]);
        }
        return (bool) $this->insert(['key' => $key, 'value' => $value]);
    }
}
