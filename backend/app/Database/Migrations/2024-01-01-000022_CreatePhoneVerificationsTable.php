<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhoneVerificationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'phone'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 6, 'null' => false],
            'attempts'    => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'is_used'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'expires_at'  => ['type' => 'DATETIME', 'null' => false],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('phone');
        $this->forge->createTable('phone_verifications');
    }

    public function down(): void
    {
        $this->forge->dropTable('phone_verifications');
    }
}
