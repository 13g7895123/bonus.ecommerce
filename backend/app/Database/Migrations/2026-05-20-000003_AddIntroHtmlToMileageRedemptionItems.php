<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIntroHtmlToMileageRedemptionItems extends Migration
{
    public function up(): void
    {
        $fields = [
            'intro_html' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'details',
            ],
        ];

        $this->forge->addColumn('mileage_redemption_items', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_redemption_items', 'intro_html');
    }
}
