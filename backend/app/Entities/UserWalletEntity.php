<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UserWalletEntity extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'             => 'integer',
        'user_id'        => 'integer',
        'balance'        => 'float',
        'miles_balance'  => 'integer',
    ];

    public function getMaskedBankAccount(): ?string
    {
        $acc = $this->attributes['bank_account'] ?? null;
        if (!$acc) {
            return null;
        }
        return str_repeat('*', max(0, strlen($acc) - 4)) . substr($acc, -4);
    }

    public function hasBankAccount(): bool
    {
        return !empty($this->attributes['bank_account']);
    }

    public function hasWithdrawalPassword(): bool
    {
        return !empty($this->attributes['withdrawal_password_hash']);
    }

    public function toPublicArray(): array
    {
        return [
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'balance'              => (float) ($this->attributes['balance'] ?? 0),
            'miles_balance'        => (int) ($this->attributes['miles_balance'] ?? 0),
            'has_bank_account'     => $this->hasBankAccount(),
            'has_withdrawal_pw'    => $this->hasWithdrawalPassword(),
            'bank_account_masked'  => $this->getMaskedBankAccount(),
            'bank_name'            => $this->attributes['bank_name'] ?? null,
            'bank_account_name'    => $this->attributes['bank_account_name'] ?? null,
        ];
    }
}
