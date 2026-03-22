<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCodeFieldsToMileageRecords extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('mileage_records', [
            'code_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'source',
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'code_id',
            ],
        ]);

        $this->forge->addKey('code_id');
        $this->forge->processIndexes('mileage_records');
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_records', ['code_id', 'code']);
    }
}