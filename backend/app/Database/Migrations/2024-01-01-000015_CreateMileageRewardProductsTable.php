<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMileageRewardProductsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'image_url'      => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'mileage_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'stock'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'     => ['type' => 'INT', 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('mileage_reward_products');
    }

    public function down(): void
    {
        $this->forge->dropTable('mileage_reward_products');
    }
}
