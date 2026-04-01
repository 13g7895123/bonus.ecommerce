<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMileageItemIdToRewardProducts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('mileage_reward_products', [
            'mileage_item_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'id',
            ],
        ]);

        // 將現有的所有商品掛到第一個里程兌換項目
        $this->db->query("
            UPDATE mileage_reward_products
            SET mileage_item_id = (
                SELECT id FROM mileage_redemption_items ORDER BY sort_order ASC, id ASC LIMIT 1
            )
            WHERE mileage_item_id IS NULL
        ");
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_reward_products', 'mileage_item_id');
    }
}
