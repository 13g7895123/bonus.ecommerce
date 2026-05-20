<?php

namespace App\Models;

use CodeIgniter\Model;

class SignInCampaignModel extends Model
{
    protected $table         = 'sign_in_campaigns';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['year', 'month', 'title', 'base_reward_miles', 'streak_days', 'streak_bonus_miles', 'is_active'];
}
