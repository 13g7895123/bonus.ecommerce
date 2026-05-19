<?php

namespace App\Models;

use CodeIgniter\Model;

class SignInCampaignModel extends Model
{
    protected $table         = 'sign_in_campaigns';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['year', 'month', 'title', 'is_active'];
}
