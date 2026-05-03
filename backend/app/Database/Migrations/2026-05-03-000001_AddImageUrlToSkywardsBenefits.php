<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageUrlToSkywardsBenefits extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('image_url', 'skywards_benefits')) {
            $this->forge->addColumn('skywards_benefits', [
                'image_url' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 512,
                    'null'       => true,
                    'after'      => 'label',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('image_url', 'skywards_benefits')) {
            $this->forge->dropColumn('skywards_benefits', 'image_url');
        }
    }
}