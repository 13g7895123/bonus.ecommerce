<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarFileIdToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'avatar_file_id' => [
                'type'     => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null'     => true,
                'after'    => 'avatar',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'avatar_file_id');
    }
}
