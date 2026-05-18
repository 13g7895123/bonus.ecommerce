<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRegisterInfoToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'register_ip'     => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true, 'after' => 'status'],
            'register_device' => ['type' => 'TEXT', 'null' => true, 'after' => 'register_ip'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['register_ip', 'register_device']);
    }
}
