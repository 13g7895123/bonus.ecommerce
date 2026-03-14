<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFilesTable extends Migration
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
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => false,
            ],
            'user_id' => [
                'type'     => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null'     => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['avatar', 'kyc', 'cs_message', 'general'],
                'default'    => 'general',
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'stored_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'size' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'is_public' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('user_id');
        $this->forge->addKey('type');
        $this->forge->createTable('files');
    }

    public function down(): void
    {
        $this->forge->dropTable('files', true);
    }
}
