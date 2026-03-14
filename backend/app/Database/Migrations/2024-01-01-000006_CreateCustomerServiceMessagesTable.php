<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerServiceMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ticket_id'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'sender_type' => ['type' => 'ENUM', 'constraint' => ['user', 'admin'], 'null' => false],
            'sender_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'content'     => ['type' => 'TEXT', 'null' => true],
            'image_path'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'read_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->createTable('customer_service_messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('customer_service_messages');
    }
}
