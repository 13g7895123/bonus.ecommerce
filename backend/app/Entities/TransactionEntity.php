<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TransactionEntity extends Entity
{
    protected $dates = ['created_at'];
    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'amount'  => 'float',
    ];
}
