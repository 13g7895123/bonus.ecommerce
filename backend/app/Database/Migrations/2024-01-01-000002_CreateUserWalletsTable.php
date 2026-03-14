<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserWalletsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'                  => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'balance'                  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'miles_balance'            => ['type' => 'INT', 'default' => 0],
            'withdrawal_password_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'bank_name'                => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'bank_account'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'bank_account_name'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
            'updated_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_wallets');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_wallets');
    }
}
