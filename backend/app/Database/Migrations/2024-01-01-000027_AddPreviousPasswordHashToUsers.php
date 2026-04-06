<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPreviousPasswordHashToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'previous_password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'password_hash',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'previous_password_hash');
    }
}
