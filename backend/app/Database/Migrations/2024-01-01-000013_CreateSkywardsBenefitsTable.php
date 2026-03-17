<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSkywardsBenefitsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'type'       => ['type' => 'ENUM', 'constraint' => ['hint', 'rule', 'note'], 'default' => 'rule'],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'content'    => ['type' => 'TEXT', 'null' => false],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('skywards_benefits');
    }

    public function down(): void
    {
        $this->forge->dropTable('skywards_benefits');
    }
}
