<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPinnedToAnnouncements extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('announcements', [
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_published',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('announcements', 'is_pinned');
    }
}
