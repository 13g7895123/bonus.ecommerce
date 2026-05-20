<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRewardConfigToSignInCampaigns extends Migration
{
    public function up(): void
    {
        $fields = [
            'base_reward_miles' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'title',
            ],
            'streak_days' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'base_reward_miles',
            ],
            'streak_bonus_miles' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'streak_days',
            ],
        ];

        $this->forge->addColumn('sign_in_campaigns', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sign_in_campaigns', ['base_reward_miles', 'streak_days', 'streak_bonus_miles']);
    }
}
