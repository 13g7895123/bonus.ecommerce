<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageUrlToSkywardsBenefits extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('tier', 'skywards_benefits')) {
            $this->forge->addColumn('skywards_benefits', [
                'tier' => [
                    'type'       => 'ENUM',
                    'constraint' => ['regular', 'silver', 'gold', 'platinum'],
                    'null'       => true,
                    'after'      => 'type',
                ],
            ]);
        }

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

        $this->db->table('skywards_benefits')->where('label', '銀卡')->update(['tier' => 'silver', 'type' => 'rule']);
        $this->db->table('skywards_benefits')->where('label', '金卡')->update(['tier' => 'gold', 'type' => 'rule']);
        $this->db->table('skywards_benefits')->where('label', '白金卡')->update(['tier' => 'platinum', 'type' => 'rule']);
        $this->db->table('skywards_benefits')->whereIn('type', ['hint', 'note'])->update(['is_active' => 0]);

        $regularCount = $this->db->table('skywards_benefits')
            ->where('type', 'rule')
            ->where('tier', 'regular')
            ->countAllResults();

        if ($regularCount === 0) {
            $now = date('Y-m-d H:i:s');
            $this->db->table('skywards_benefits')->insert([
                'type'       => 'rule',
                'tier'       => 'regular',
                'label'      => '藍卡權益',
                'image_url'  => null,
                'content'    => '歡迎成為 Skywards 會員，累積里程即可解鎖更多會員權益。',
                'sort_order' => 1,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('image_url', 'skywards_benefits')) {
            $this->forge->dropColumn('skywards_benefits', 'image_url');
        }

        if ($this->db->fieldExists('tier', 'skywards_benefits')) {
            $this->forge->dropColumn('skywards_benefits', 'tier');
        }
    }
}