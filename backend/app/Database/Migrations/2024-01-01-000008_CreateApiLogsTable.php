<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'method'          => ['type' => 'VARCHAR', 'constraint' => 10],
            'uri'             => ['type' => 'VARCHAR', 'constraint' => 500],
            'ip_address'      => ['type' => 'VARCHAR', 'constraint' => 45],
            'user_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'request_headers' => ['type' => 'JSON', 'null' => true],
            'request_body'    => ['type' => 'LONGTEXT', 'null' => true],
            'response_code'   => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'response_body'   => ['type' => 'LONGTEXT', 'null' => true],
            'duration_ms'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['method', 'created_at']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('api_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('api_logs');
    }
}
