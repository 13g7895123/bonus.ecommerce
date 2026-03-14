<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CustomerServiceMessageEntity extends Entity
{
    protected $dates = ['created_at', 'read_at'];
    protected $casts = [
        'id'        => 'integer',
        'sender_id' => 'integer',
    ];
}
