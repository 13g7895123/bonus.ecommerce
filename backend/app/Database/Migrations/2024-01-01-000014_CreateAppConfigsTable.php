<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppConfigsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'key'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'value'      => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('app_configs');
    }

    public function down(): void
    {
        $this->forge->dropTable('app_configs');
    }
}
