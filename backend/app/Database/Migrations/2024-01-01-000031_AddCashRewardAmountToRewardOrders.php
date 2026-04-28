<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashRewardAmountToRewardOrders extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('mileage_reward_orders', [
            'cash_reward_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'after'      => 'mileage_reward_amount',
                'comment'    => '審核通過後將以現金形式發放給使用者的回饋金額',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('mileage_reward_orders', 'cash_reward_amount');
    }
}
