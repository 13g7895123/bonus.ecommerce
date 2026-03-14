<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarDobCountryToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'avatar'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'phone'],
            'dob'     => ['type' => 'DATE', 'null' => true, 'after' => 'avatar'],
            'country' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'dob'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['avatar', 'dob', 'country']);
    }
}
