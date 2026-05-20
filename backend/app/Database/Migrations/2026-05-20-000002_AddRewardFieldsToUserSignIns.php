<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRewardFieldsToUserSignIns extends Migration
{
    public function up(): void
    {
        $fields = [
            'awarded_miles' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'sign_in_date',
            ],
            'streak_day_count' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'awarded_miles',
            ],
            'is_streak_bonus' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'streak_day_count',
            ],
        ];

        $this->forge->addColumn('user_sign_ins', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('user_sign_ins', ['awarded_miles', 'streak_day_count', 'is_streak_bonus']);
    }
}
