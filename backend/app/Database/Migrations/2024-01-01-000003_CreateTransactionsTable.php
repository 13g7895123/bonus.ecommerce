<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'type'         => ['type' => 'ENUM', 'constraint' => ['deposit', 'withdrawal', 'transfer', 'adjustment'], 'null' => false],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => false],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'failed', 'cancelled'], 'default' => 'completed'],
            'description'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reference_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transactions');
    }

    public function down(): void
    {
        $this->forge->dropTable('transactions');
    }
}
