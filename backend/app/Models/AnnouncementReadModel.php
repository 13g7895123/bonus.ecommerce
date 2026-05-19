<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementReadModel extends Model
{
    protected $table         = 'announcement_reads';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'announcement_id', 'read_at'];
}
