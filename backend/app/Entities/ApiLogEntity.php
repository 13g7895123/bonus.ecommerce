<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ApiLogEntity extends Entity
{
    protected $dates = ['created_at'];
    protected $casts = [
        'id'            => 'integer',
        'user_id'       => '?integer',
        'response_code' => '?integer',
        'duration_ms'   => '?integer',
    ];
}
