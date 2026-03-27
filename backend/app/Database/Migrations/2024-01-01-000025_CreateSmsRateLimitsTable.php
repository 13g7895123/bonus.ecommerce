<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmsRateLimitsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'window_start' => ['type' => 'DATETIME', 'null' => false],
            'send_count'   => ['type' => 'INT', 'unsigned' => true, 'default' => 1, 'null' => false],
            'blocked_until'=> ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('ip');
        $this->forge->addKey('blocked_until');
        $this->forge->createTable('sms_rate_limits');
    }

    public function down(): void
    {
        $this->forge->dropTable('sms_rate_limits');
    }
}
