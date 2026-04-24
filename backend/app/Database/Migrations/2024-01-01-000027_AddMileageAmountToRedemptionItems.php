<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMileageAmountToRedemptionItems extends Migration
{
    public function up(): void
    {
        $fields = [
            'mileage_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'default'    => '0.00',
                'after'      => 'sort_order',
            ],
        ];
        $this->forge->addColumn('mileage_redemption_items', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_redemption_items', 'mileage_amount');
    }
}
