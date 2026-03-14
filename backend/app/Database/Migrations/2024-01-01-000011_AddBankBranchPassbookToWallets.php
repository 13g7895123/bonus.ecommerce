<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankBranchPassbookToWallets extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('user_wallets', [
            'bank_branch' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'bank_name',
            ],
            'bank_passbook_file_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'bank_account_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('user_wallets', ['bank_branch', 'bank_passbook_file_id']);
    }
}
