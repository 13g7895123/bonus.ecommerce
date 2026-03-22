<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDisplayStyleToRewardProducts extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE mileage_reward_products ADD COLUMN display_style VARCHAR(20) NOT NULL DEFAULT 'default' AFTER sort_order");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE mileage_reward_products DROP COLUMN display_style");
    }
}
