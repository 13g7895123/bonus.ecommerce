<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSignInCampaignsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
            ],
            'month' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['year', 'month']);
        $this->forge->createTable('sign_in_campaigns', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('sign_in_campaigns', true);
    }
}
