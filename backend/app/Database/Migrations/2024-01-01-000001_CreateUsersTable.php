<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'password_hash'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'full_name'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'tier'              => ['type' => 'ENUM', 'constraint' => ['regular', 'silver', 'gold', 'platinum'], 'default' => 'regular'],
            'role'              => ['type' => 'ENUM', 'constraint' => ['user', 'admin'], 'default' => 'user'],
            'is_verified'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'verify_status'     => ['type' => 'ENUM', 'constraint' => ['none', 'pending', 'verified', 'rejected'], 'default' => 'none'],
            'verification_data' => ['type' => 'JSON', 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'suspended'], 'default' => 'active'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users');
    }
}
