<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMilesPointsToRewardProducts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('mileage_reward_products', [
            'miles_points' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'mileage_amount',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_reward_products', 'miles_points');
    }
}
