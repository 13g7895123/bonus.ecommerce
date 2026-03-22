<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMileageRewardOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'              => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'product_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'product_name'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'product_image_url'    => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'quantity'             => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'unit_price'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'unit_miles_points'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'total_price'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'total_miles_points'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'mileage_reward_amount'=> ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending_review'],
            'review_note'          => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'reviewed_at'          => ['type' => 'DATETIME', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('product_id');
        $this->forge->createTable('mileage_reward_orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('mileage_reward_orders');
    }
}
