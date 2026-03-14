<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class AnnouncementEntity extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'published_at'];
    protected $casts = [
        'id'           => 'integer',
        'is_published' => 'boolean',
    ];
}
