<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSignInModel extends Model
{
    protected $table         = 'user_sign_ins';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'campaign_id', 'sign_in_date'];
}
