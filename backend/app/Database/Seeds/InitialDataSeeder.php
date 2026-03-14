<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedAnnouncements();
    }

    private function seedUsers(): void
    {
        $users = [
            [
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('Admin123!', PASSWORD_BCRYPT),
                'full_name'     => 'System Admin',
                'phone'         => '0900000000',
                'tier'          => 'gold',
                'role'          => 'admin',
                'is_verified'   => 1,
                'verify_status' => 'verified',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'email'         => 'user@test.com',
                'password_hash' => password_hash('Test123!', PASSWORD_BCRYPT),
                'full_name'     => 'Test User',
                'phone'         => '0911111111',
                'tier'          => 'silver',
                'role'          => 'user',
                'is_verified'   => 0,
                'verify_status' => 'none',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($users as $user) {
            $existing = $this->db->table('users')->where('email', $user['email'])->get()->getRow();
            if ($existing) {
                continue;
            }

            $this->db->table('users')->insert($user);
            $userId = $this->db->insertID();

            $balance      = ($user['role'] === 'admin') ? 0 : 5000.00;
            $milesBalance = ($user['role'] === 'admin') ? 0 : 500;

            $this->db->table('user_wallets')->insert([
                'user_id'       => $userId,
                'balance'       => $balance,
                'miles_balance' => $milesBalance,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            if ($user['role'] === 'user') {
                $this->db->table('mileage_records')->insert([
                    'user_id'    => $userId,
                    'type'       => 'earn',
                    'amount'     => 500,
                    'source'     => 'registration_bonus',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seedAnnouncements(): void
    {
        $count = $this->db->table('announcements')->countAll();
        if ($count > 0) {
            return;
        }

        $announcements = [
            [
                'title'        => '系統維護公告',
                'content'      => "親愛的會員您好，\n\n本系統將於近期進行例行維護作業，維護期間服務可能短暫中斷，造成不便敬請見諒。\n\n維護時間：凌晨 02:00 – 04:00\n\n感謝您的耐心等候，如有任何疑問請聯繫客服。",
                'is_published' => 1,
                'published_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'created_at'   => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at'   => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'title'        => '新功能上線：里程兌換',
                'content'      => "親愛的會員您好，\n\n全新里程兌換功能正式上線！您現在可以將累積的里程數兌換為現金回饋或商品折扣。\n\n立即前往「里程兌換」頁面探索更多優惠。",
                'is_published' => 1,
                'published_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'created_at'   => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at'   => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'title'        => '會員等級制度說明',
                'content'      => "親愛的會員您好，\n\n我們的會員等級分為：普通会員、銀卡會員、金卡會員、白金會員。\n\n每個等級享有不同的優惠與服務，累積消費金額即可自動升級。",
                'is_published' => 1,
                'published_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
                'created_at'   => date('Y-m-d H:i:s', strtotime('-14 days')),
                'updated_at'   => date('Y-m-d H:i:s', strtotime('-14 days')),
            ],
        ];

        $this->db->table('announcements')->insertBatch($announcements);
    }
}
