<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurchaseTargetToRewardProducts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('mileage_reward_products', [
            'purchase_target' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
                'after'    => 'stock',
                'comment'  => '已購買人數達 N 顯示用門檻',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_reward_products', 'purchase_target');
    }
}
