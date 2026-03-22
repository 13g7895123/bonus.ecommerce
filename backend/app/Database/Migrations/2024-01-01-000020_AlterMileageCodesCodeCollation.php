<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMileageCodesCodeCollation extends Migration
{
    public function up(): void
    {
        $this->db->query(
            "ALTER TABLE `mileage_codes`
             MODIFY `code` VARCHAR(100)
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL"
        );
    }

    public function down(): void
    {
        $this->db->query(
            "ALTER TABLE `mileage_codes`
             MODIFY `code` VARCHAR(100)
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL"
        );
    }
}
