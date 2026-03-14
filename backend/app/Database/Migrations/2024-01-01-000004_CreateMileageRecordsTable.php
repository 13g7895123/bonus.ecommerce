<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMileageRecordsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'type'       => ['type' => 'ENUM', 'constraint' => ['earn', 'redeem'], 'null' => false],
            'amount'     => ['type' => 'INT', 'null' => false],
            'source'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('mileage_records');
    }

    public function down(): void
    {
        $this->forge->dropTable('mileage_records');
    }
}
