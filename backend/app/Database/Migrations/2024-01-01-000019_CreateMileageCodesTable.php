<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMileageCodesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'miles_amount'=> ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'usage_limit' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'comment' => 'NULL = unlimited'],
            'used_count'  => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'expires_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('mileage_codes');
    }

    public function down(): void
    {
        $this->forge->dropTable('mileage_codes');
    }
}
