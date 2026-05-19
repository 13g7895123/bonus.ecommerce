<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\AnnouncementService;
use App\Services\UserService;

class UserController extends BaseApiController
{
    public function me()
    {
        $profile = (new UserService())->getProfile(Auth::id());
        if (!$profile) {
            return $this->error('找不到使用者', 404);
        }
        return $this->success($profile);
    }

    public function updateMe()
    {
        $data   = $this->getJson();
        $result = (new UserService())->updateProfile(Auth::id(), $data);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success($result['data'], '個人資料已更新');
    }

    public function verify()
    {
        // 前端傳 JSON（file ID 已預先上傳），用 getJson() 讀取
        $json = $this->getJson(true);  // true = assoc array
        $result = (new UserService())->submitVerification(Auth::id(), [], $json ?? []);
        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }
        return $this->success(null, $result['message']);
    }

    public function changePassword()
    {
        $data    = $this->getJson();
        $new     = $data['new_password']     ?? '';
        $confirm = $data['confirm_password'] ?? '';

        if (!$new || !$confirm) {
            return $this->error('請填寫所有欄位', 422);
        }

        if ($new !== $confirm) {
            return $this->error('新密碼與確認密碼不一致', 422);
        }

        $result = (new UserService())->changePassword(Auth::id(), $new);
        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }
        return $this->success(null, $result['message']);
    }

    public function uploadAvatar()
    {
        $file   = $this->request->getFile('avatar');
        $result = (new UserService())->uploadAvatar(Auth::id(), $file);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success(['avatar_url' => $result['avatar_url']], '頭像已更新');
    }

    public function sendVerificationEmail()
    {
        // TODO: 串接寄信功能（SMTP / 第三方 mail service）
        // 目前直接回傳成功，待日後實作以下流程：
        //   1. 產生 email_verify_token(uuid) 並寫入 users 表
        //   2. 寄送驗證連結至使用者信箱
        //   3. 另建 GET /users/me/verify-email?token=xxx 驗證 token 並更新 is_verified=1
        return $this->success(null, '驗證信已發送，請查收電子郵件');
    }

    public function myMails()
    {
        $items = model(\App\Models\UserMailModel::class)->listForUser(Auth::id());
        return $this->success(['items' => $items, 'total' => count($items)]);
    }

    public function markMailRead(int $id)
    {
        $model = model(\App\Models\UserMailModel::class);
        $mail  = $model->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$mail) {
            return $this->error('信件不存在', 404);
        }
        $model->update($id, ['is_read' => 1]);
        return $this->success(null, '已標記為已讀');
    }

    public function notifications()
    {
        $userId = Auth::id();
        $announcementModel = model(\App\Models\AnnouncementModel::class);
        $mailModel         = model(\App\Models\UserMailModel::class);
        $readModel         = model(\App\Models\AnnouncementReadModel::class);

        $announcements = $announcementModel->where('is_published', 1)
            ->orderBy('is_pinned', 'DESC')
            ->orderBy('published_at', 'DESC')
            ->findAll(50);

        $announcementIds = array_map(static fn(array $item): int => (int) $item['id'], $announcements);
        $readIds = [];

        if (!empty($announcementIds)) {
            $reads = $readModel->where('user_id', $userId)
                ->whereIn('announcement_id', $announcementIds)
                ->findAll();
            $readIds = array_map(static fn(array $row): int => (int) $row['announcement_id'], $reads);
        }

        $announcementItems = array_map(static function (array $item) use ($readIds): array {
            $plain = html_entity_decode(strip_tags($item['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plain = trim((string) preg_replace('/\s+/u', ' ', $plain));

            return [
                'id'           => (int) $item['id'],
                'title'        => $item['title'],
                'content'      => $item['content'],
                'content_text' => $plain,
                'published_at' => $item['published_at'],
                'is_pinned'    => (int) ($item['is_pinned'] ?? 0),
                'is_read'      => in_array((int) $item['id'], $readIds, true) ? 1 : 0,
            ];
        }, $announcements);

        $mailItems = array_map(static fn(array $item): array => [
            'id'         => (int) $item['id'],
            'subject'    => $item['subject'],
            'content'    => $item['content'],
            'created_at' => $item['created_at'],
            'is_read'    => (int) $item['is_read'],
        ], $mailModel->listForUser($userId));

        return $this->success([
            'announcements' => [
                'items'        => $announcementItems,
                'unread_count' => count(array_filter($announcementItems, static fn(array $item): bool => (int) $item['is_read'] === 0)),
            ],
            'mails' => [
                'items'        => $mailItems,
                'unread_count' => count(array_filter($mailItems, static fn(array $item): bool => (int) $item['is_read'] === 0)),
            ],
        ]);
    }

    public function markAnnouncementRead(int $id)
    {
        $ok = (new AnnouncementService())->markRead(Auth::id(), $id);
        if (!$ok) {
            return $this->error('公告不存在', 404);
        }
        return $this->success(null, '已標記為已讀');
    }

    public function signInStatus()
    {
        $campaignModel = model(\App\Models\SignInCampaignModel::class);
        $recordModel   = model(\App\Models\UserSignInModel::class);

        $now   = new \DateTimeImmutable('now');
        $year  = (int) $now->format('Y');
        $month = (int) $now->format('n');
        $title = sprintf('%d年【%d】月期 簽到活動', $year, $month);

        $campaign = $campaignModel->where('year', $year)->where('month', $month)->first();
        if (!$campaign) {
            $campaignId = $campaignModel->insert([
                'year'      => $year,
                'month'     => $month,
                'title'     => $title,
                'is_active' => 1,
            ], true);
            $campaign = $campaignModel->find($campaignId);
        }

        $records = $recordModel
            ->where('user_id', Auth::id())
            ->where('campaign_id', $campaign['id'])
            ->findAll();

        $signedDates = array_values(array_map(static fn(array $row): string => $row['sign_in_date'], $records));
        $today = $now->format('Y-m-d');

        return $this->success([
            'campaign' => [
                'id'    => (int) $campaign['id'],
                'year'  => (int) $campaign['year'],
                'month' => (int) $campaign['month'],
                'title' => $campaign['title'],
            ],
            'today'            => $today,
            'signed_dates'     => $signedDates,
            'signed_count'     => count($signedDates),
            'has_signed_today' => in_array($today, $signedDates, true),
        ]);
    }

    public function signInToday()
    {
        $campaignModel = model(\App\Models\SignInCampaignModel::class);
        $recordModel   = model(\App\Models\UserSignInModel::class);

        $today = (new \DateTimeImmutable('now'))->format('Y-m-d');
        $year  = (int) substr($today, 0, 4);
        $month = (int) substr($today, 5, 2);

        $campaign = $campaignModel->where('year', $year)->where('month', $month)->first();
        if (!$campaign) {
            $campaignId = $campaignModel->insert([
                'year'      => $year,
                'month'     => $month,
                'title'     => sprintf('%d年【%d】月期 簽到活動', $year, $month),
                'is_active' => 1,
            ], true);
            $campaign = $campaignModel->find($campaignId);
        }

        $exists = $recordModel
            ->where('user_id', Auth::id())
            ->where('campaign_id', $campaign['id'])
            ->where('sign_in_date', $today)
            ->first();

        if ($exists) {
            return $this->success(['sign_in_date' => $today], '今日已簽到');
        }

        $recordModel->insert([
            'user_id'      => Auth::id(),
            'campaign_id'  => $campaign['id'],
            'sign_in_date' => $today,
        ]);

        return $this->success(['sign_in_date' => $today], '簽到成功');
    }
}
