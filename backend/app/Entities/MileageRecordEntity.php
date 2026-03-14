<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MileageRecordEntity extends Entity
{
    protected $dates = ['created_at'];
    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'amount'  => 'integer',
    ];
}
