<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 為 phone_verifications 資料表新增：
 *   - provider    VARCHAR(20)  記錄使用的 OTP 提供者（twilio / firebase）
 *   - session_info TEXT        儲存 Firebase verificationId 或其他提供者的 session 識別碼
 *
 * 同時修正 code 欄位長度：原為 VARCHAR(6) 已不足以儲存
 * 'TWILIO_VERIFY'（12 字元）或 'FIREBASE_OTP'（12 字元）等識別碼，
 * 調整為 VARCHAR(512) 以兼容各提供者。
 *
 * 並新增預設 sms_provider 設定至 app_configs（若尚未存在）。
 */
class AddProviderFieldsToPhoneVerifications extends Migration
{
    public function up(): void
    {
        // ── phone_verifications 欄位調整 ─────────────────────────────
        $this->forge->modifyColumn('phone_verifications', [
            'code' => [
                'name'       => 'code',
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => false,
            ],
        ]);

        $fields = [
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'twilio',
                'after'      => 'code',
            ],
            'session_info' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'provider',
            ],
        ];
        $this->forge->addColumn('phone_verifications', $fields);

        // ── app_configs 預設值：sms_provider = twilio ─────────────────
        $db = \Config\Database::connect();
        $existing = $db->table('app_configs')->where('key', 'sms_provider')->get()->getRowArray();
        if (!$existing) {
            $db->table('app_configs')->insert([
                'key'        => 'sms_provider',
                'value'      => 'twilio',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('phone_verifications', ['provider', 'session_info']);

        $this->forge->modifyColumn('phone_verifications', [
            'code' => [
                'name'       => 'code',
                'type'       => 'VARCHAR',
                'constraint' => 6,
                'null'       => false,
            ],
        ]);

        \Config\Database::connect()
            ->table('app_configs')
            ->where('key', 'sms_provider')
            ->delete();
    }
}
