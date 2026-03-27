<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateThirdPartyLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'service'         => ['type' => 'VARCHAR', 'constraint' => 50],   // e.g. twilio, firebase
            'action'          => ['type' => 'VARCHAR', 'constraint' => 100],  // e.g. sendOtp, verifyOtp
            'method'          => ['type' => 'VARCHAR', 'constraint' => 10],
            'url'             => ['type' => 'VARCHAR', 'constraint' => 500],
            'request_body'    => ['type' => 'TEXT', 'null' => true],
            'response_code'   => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'response_body'   => ['type' => 'LONGTEXT', 'null' => true],
            'duration_ms'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'success'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'error_message'   => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['service', 'created_at']);
        $this->forge->createTable('third_party_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('third_party_logs');
    }
}
