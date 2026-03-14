<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'          => 'integer',
        'is_verified' => 'boolean',
    ];

    public function getFullName(): string
    {
        return $this->attributes['full_name'] ?? '';
    }

    public function isAdmin(): bool
    {
        return ($this->attributes['role'] ?? '') === 'admin';
    }

    public function toSafeArray(): array
    {
        $data = $this->toArray();
        unset($data['password_hash']);
        return $data;
    }
}
