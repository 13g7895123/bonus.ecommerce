<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMileageRedemptionItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'short_desc'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'details'        => ['type' => 'TEXT', 'null' => true],
            'logo_letter'    => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'S'],
            'logo_color'     => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => '#ffffff'],
            'is_featured'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'featured_label' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => '精選'],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'     => ['type' => 'INT', 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('mileage_redemption_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('mileage_redemption_items');
    }
}
