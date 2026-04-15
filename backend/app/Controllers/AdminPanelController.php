<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Repositories\ApiLogRepository;
use App\Repositories\MileageRedemptionItemRepository;
use App\Repositories\SkywardsBenefitRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;
use App\Services\AdminService;
use App\Services\MileageRedemptionItemService;
use App\Services\MileageRewardOrderService;
use App\Services\MileageRewardProductService;
use App\Services\SkywardsBenefitService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Panel Controller — 免登入後台，提供 API 存取紀錄與使用者管理
 */
class AdminPanelController extends Controller
{
    private function json(mixed $data, int $code = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setJSON($data);
    }

    // ── Dashboard Stats ──────────────────────────────────────────────────────

    public function stats(): ResponseInterface
    {
        $userRepo = new UserRepository();
        $logRepo  = new ApiLogRepository();

        $userStats = $userRepo->paginate(1, 1);
        $logStats  = $logRepo->paginate(1, 1);

        // 最近 24 小時的請求數
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $recentLogs = $logRepo->paginate(1, 1, ['date_from' => $yesterday]);

        // 錯誤率 (4xx/5xx)
        $errorLogs = $logRepo->paginate(1, 1, ['date_from' => $yesterday, 'response_code' => 500]);

        return $this->json([
            'total_users'       => $userStats['total'],
            'total_api_logs'    => $logStats['total'],
            'requests_24h'      => $recentLogs['total'],
        ]);
    }

    // ── API Logs ──────────────────────────────────────────────────────────────

    public function logs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = [
            'method'        => $this->request->getGet('method'),
            'uri'           => $this->request->getGet('uri'),
            'user_id'       => $this->request->getGet('user_id'),
            'response_code' => $this->request->getGet('response_code'),
            'date_from'     => $this->request->getGet('date_from'),
            'date_to'       => $this->request->getGet('date_to'),
        ];

        $result = (new ApiLogRepository())->paginate($page, $limit, array_filter($filters));

        return $this->json([
            'page'  => $page,
            'limit' => $limit,
            ...$result,
        ]);
    }

    public function logDetail(int $id): ResponseInterface
    {
        $log = model(\App\Models\ApiLogModel::class)->find($id);
        if (!$log) {
            return $this->json(['message' => 'Log not found'], 404);
        }
        // Decode JSON fields for readability
        if (is_string($log['request_headers'])) {
            $log['request_headers'] = json_decode($log['request_headers'], true);
        }
        return $this->json($log);
    }

    // ── Users ─────────────────────────────────────────────────────────────────

    public function users(): ResponseInterface
    {
        $page   = (int) ($this->request->getGet('page') ?? 1);
        $limit  = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $result = (new UserRepository())->paginate($page, $limit);

        // Batch load wallets — single query, no N+1
        $userIds = array_column($result['items'], 'id');
        $wallets = [];
        if (!empty($userIds)) {
            $rawWallets = model(\App\Models\UserWalletModel::class)
                ->whereIn('user_id', $userIds)
                ->findAll();
            foreach ($rawWallets as $w) {
                $wallets[(int) $w['user_id']] = $w;
            }
        }

        $result['items'] = array_map(static function (array $u) use ($wallets): array {
            unset($u['password_hash']);
            $wallet = $wallets[$u['id']] ?? null;
            $u['balance']          = $wallet ? (float) $wallet['balance'] : 0;
            $u['miles_balance']    = $wallet ? (int) $wallet['miles_balance'] : 0;
            $u['has_bank_account'] = $wallet ? !empty($wallet['bank_account']) : false;
            return $u;
        }, $result['items']);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    public function deposit(int $userId): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $amount = $data['amount'] ?? null;

        if (!is_numeric($amount) || (float) $amount <= 0) {
            return $this->json(['message' => 'amount 必須為正數'], 400);
        }

        $result = (new AdminService())->adjustBalance(
            $userId,
            (float) $amount,
            $data['description'] ?? 'Admin 儲值 (demo)'
        );

        if (!$result['success']) {
            return $this->json(['message' => $result['message']], 400);
        }
        return $this->json(['message' => '儲值成功', 'balance' => $result['data']['balance']]);
    }

    public function userDetail(int $id): ResponseInterface
    {
        $user = (new UserRepository())->find($id);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        unset($user['password_hash']);
        if (isset($user['verification_data']) && is_string($user['verification_data'])) {
            $user['verification_data'] = json_decode($user['verification_data'], true);
        }
        // Attach wallet info
        $wallet = model(\App\Models\UserWalletModel::class)->where('user_id', $id)->first();
        $user['balance']          = $wallet ? (float) $wallet['balance'] : 0;
        $user['miles_balance']    = $wallet ? (int) $wallet['miles_balance'] : 0;
        $user['has_bank_account'] = $wallet ? !empty($wallet['bank_account']) : false;
        $user['bank_info']        = $wallet ? [
            'bank_name'         => $wallet['bank_name'] ?? null,
            'bank_branch'       => $wallet['bank_branch'] ?? null,
            'bank_account'      => $wallet['bank_account'] ?? null,
            'bank_account_name' => $wallet['bank_account_name'] ?? null,
        ] : null;
        return $this->json($user);
    }

    public function updateUserBank(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];
        $walletModel = model(\App\Models\UserWalletModel::class);
        $wallet = $walletModel->where('user_id', $id)->first();
        if (!$wallet) {
            return $this->json(['message' => '該使用者尚未建立錢包'], 404);
        }
        $walletModel->where('user_id', $id)->set([
            'bank_name'         => $data['bank_name'] ?? null,
            'bank_branch'       => $data['bank_branch'] ?? null,
            'bank_account'      => $data['bank_account'] ?? null,
            'bank_account_name' => $data['bank_account_name'] ?? null,
        ])->update();
        return $this->json(['success' => true, 'message' => '銀行資料已更新']);
    }

    public function deleteUserBank(int $id): ResponseInterface
    {
        $walletModel = model(\App\Models\UserWalletModel::class);
        $wallet = $walletModel->where('user_id', $id)->first();
        if (!$wallet) {
            return $this->json(['message' => '該使用者尚未建立錢包'], 404);
        }
        $walletModel->where('user_id', $id)->set([
            'bank_name'         => null,
            'bank_branch'       => null,
            'bank_account'      => null,
            'bank_account_name' => null,
            'bank_passbook_file_id' => null,
        ])->update();
        return $this->json(['success' => true, 'message' => '銀行資料已刪除']);
    }

    public function changePassword(int $userId): ResponseInterface
    {
        $data        = $this->request->getJSON(true) ?? [];
        $newPassword = $data['new_password'] ?? '';

        if (strlen($newPassword) < 6) {
            return $this->json(['message' => '密碼至少需要 6 個字元'], 400);
        }

        $repo = new UserRepository();
        $user = $repo->find($userId);
        if (!$user) {
            return $this->json(['message' => '找不到使用者'], 404);
        }

        $repo->update($userId, [
            'previous_password_hash' => $user['password_hash'] ?? null,
            'password_hash'          => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
        return $this->json(['success' => true, 'message' => '密碼已成功更新']);
    }

    public function changeWithdrawalPassword(int $userId): ResponseInterface
    {
        $data        = $this->request->getJSON(true) ?? [];
        $newPassword = $data['new_password'] ?? '';

        if (strlen($newPassword) < 4) {
            return $this->json(['message' => '提款密碼至少需要 4 個字元'], 400);
        }

        $walletRepo = new UserWalletRepository();
        $wallet     = $walletRepo->findByUserId($userId);
        if (!$wallet) {
            return $this->json(['message' => '該使用者尚未建立錢包'], 404);
        }

        $walletRepo->updateByUserId($userId, [
            'withdrawal_password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);
        return $this->json(['success' => true, 'message' => '提款密碼已成功更新']);
    }

    public function createUser(): ResponseInterface
    {
        $data     = $this->request->getJSON(true) ?? [];
        $email    = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $fullName = trim($data['full_name'] ?? '');
        $phone    = trim($data['phone'] ?? '');
        $role     = in_array($data['role'] ?? 'user', ['user', 'admin']) ? ($data['role'] ?? 'user') : 'user';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => '請輸入有效的 Email'], 400);
        }
        if (strlen($password) < 6) {
            return $this->json(['message' => '密碼至少需要 6 個字元'], 400);
        }

        if (model(UserModel::class)->where('email', $email)->first()) {
            return $this->json(['message' => 'Email 已被使用'], 409);
        }

        $repo   = new UserRepository();
        $userId = $repo->create([
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name'     => $fullName ?: null,
            'phone'         => $phone ?: null,
            'role'          => $role,
            'tier'          => 'regular',
            'status'        => 'active',
            'is_verified'   => 0,
            'verify_status' => 'none',
        ]);

        return $this->json(['success' => true, 'id' => $userId, 'message' => '使用者已建立'], 201);
    }

    public function userLogs(int $userId): ResponseInterface
    {
        $page   = (int) ($this->request->getGet('page') ?? 1);
        $limit  = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $result = (new ApiLogRepository())->paginate($page, $limit, ['user_id' => $userId]);
        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    // ── Mileage Redemption Items ──────────────────────────────────────────────

    public function mileageItems(): ResponseInterface
    {
        $items = (new MileageRedemptionItemService())->getAllItems();
        return $this->json(['items' => $items]);
    }

    public function createMileageItem(): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $result = (new MileageRedemptionItemService())->create($data);
        return $this->json($result, $result['success'] ? 201 : 400);
    }

    public function updateMileageItem(int $id): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $result = (new MileageRedemptionItemService())->update($id, $data);
        return $this->json($result, $result['success'] ? 200 : 404);
    }

    public function deleteMileageItem(int $id): ResponseInterface
    {
        $result = (new MileageRedemptionItemService())->delete($id);
        return $this->json($result, $result['success'] ? 200 : 404);
    }

    // ── Mileage Reward Products ────────────────────────────────────────────────

    public function mileageRewardProducts(): ResponseInterface
    {
        $items = (new MileageRewardProductService())->getAllProducts();
        return $this->json(['items' => $items]);
    }

    public function createMileageRewardProduct(): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $result = (new MileageRewardProductService())->create($data);
        return $this->json($result, $result['success'] ? 201 : 400);
    }

    public function updateMileageRewardProduct(int $id): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $result = (new MileageRewardProductService())->update($id, $data);
        return $this->json($result, $result['success'] ? 200 : 404);
    }

    public function deleteMileageRewardProduct(int $id): ResponseInterface
    {
        $result = (new MileageRewardProductService())->delete($id);
        return $this->json($result, $result['success'] ? 200 : 404);
    }

    // ── Mileage Reward Orders ─────────────────────────────────────────────────

    public function rewardOrders(): ResponseInterface
    {
        $status = $this->request->getGet('status') ?? '';
        $orders = (new MileageRewardOrderService())->getAllOrders($status);
        return $this->json(['items' => $orders, 'total' => count($orders)]);
    }

    public function reviewRewardOrder(int $id): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $action = $data['action'] ?? '';
        $note   = $data['note'] ?? null;
        $result = (new MileageRewardOrderService())->reviewOrder($id, $action, $note);
        return $this->json($result, $result['success'] ? 200 : 400);
    }

    // ── Skywards Benefits ─────────────────────────────────────────────────────

    public function skywardsBenefits(): ResponseInterface
    {
        $items = (new SkywardsBenefitService())->getAllItems();
        return $this->json(['items' => $items]);
    }

    public function createSkywardsBenefit(): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $result = (new SkywardsBenefitService())->create($data);
        return $this->json($result, $result['success'] ? 201 : 400);
    }

    public function updateSkywardsBenefit(int $id): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $result = (new SkywardsBenefitService())->update($id, $data);
        return $this->json($result, $result['success'] ? 200 : 404);
    }

    public function deleteSkywardsBenefit(int $id): ResponseInterface
    {
        $result = (new SkywardsBenefitService())->delete($id);
        return $this->json($result, $result['success'] ? 200 : 404);
    }

    // ── App Config (key-value settings) ──────────────────────────────────────

    public function getConfig(string $key): ResponseInterface
    {
        $row = model(\App\Models\AppConfigModel::class)->getByKey($key);
        return $this->json(['key' => $key, 'value' => $row['value'] ?? null]);
    }

    public function setConfig(string $key): ResponseInterface
    {
        $data  = $this->request->getJSON(true) ?? [];
        $value = $data['value'] ?? '';
        $ok    = model(\App\Models\AppConfigModel::class)->setByKey($key, (string) $value);
        return $this->json(['success' => $ok, 'key' => $key, 'value' => $value], $ok ? 200 : 500);
    }

    // ── KYC / Identity Verification ───────────────────────────────────────────

    public function kycList(): ResponseInterface
    {
        $status = $this->request->getGet('status') ?? 'pending';
        $repo   = new UserRepository();

        $users = model(\App\Models\UserModel::class)
            ->where('verify_status', $status)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Strip passwords, decode verification_data
        $users = array_map(static function (array $u): array {
            unset($u['password_hash']);
            if (isset($u['verification_data']) && is_string($u['verification_data'])) {
                $u['verification_data'] = json_decode($u['verification_data'], true);
            }
            return $u;
        }, $users);

        return $this->json(['items' => $users, 'total' => count($users)]);
    }

    public function kycReview(int $userId): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $action = $data['action'] ?? '';
        $reason = $data['reason'] ?? null;

        if (!in_array($action, ['approve', 'reject', 'revoke'], true)) {
            return $this->json(['message' => 'action 必須為 approve、reject 或 revoke'], 400);
        }

        $repo   = new UserRepository();
        $user   = $repo->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $updateData = [];
        if ($action === 'approve') {
            $updateData['verify_status'] = 'approved';
        } elseif ($action === 'revoke') {
            $updateData['verify_status'] = 'pending';
        } else {
            $updateData['verify_status'] = 'rejected';
            if ($reason) {
                $vd = isset($user['verification_data'])
                    ? (is_string($user['verification_data']) ? json_decode($user['verification_data'], true) : $user['verification_data'])
                    : [];
                $vd['reject_reason'] = $reason;
                $updateData['verification_data'] = json_encode($vd, JSON_UNESCAPED_UNICODE);
            }
        }

        $repo->update($userId, $updateData);
        $msg = match($action) { 'approve' => '已審核通過', 'revoke' => '已退回待審核', default => '已拒絕' };
        return $this->json(['success' => true, 'message' => $msg]);
    }

    // ── Mileage Codes ─────────────────────────────────────────────────────────

    public function mileageCodes(): ResponseInterface
    {
        $items = model(\App\Models\MileageCodeModel::class)
            ->orderBy('created_at', 'DESC')
            ->findAll();
        return $this->json(['items' => $items, 'total' => count($items)]);
    }

    public function mileageCodeRecords(): ResponseInterface
    {
      $builder = model(\App\Models\MileageRecordModel::class)
        ->select('mileage_records.id, mileage_records.user_id, mileage_records.amount, mileage_records.code, mileage_records.created_at, users.email, users.full_name')
        ->join('users', 'users.id = mileage_records.user_id', 'left')
        ->where('mileage_records.source', 'code_redeem')
        ->orderBy('mileage_records.created_at', 'DESC');

      $code = trim((string) ($this->request->getGet('code') ?? ''));
      if ($code !== '') {
        $escapedCode = \Config\Database::connect()->escapeLikeString($code);
        $builder->like('mileage_records.code', $escapedCode, 'both', false);
      }

      $userId = (int) ($this->request->getGet('user_id') ?? 0);
      if ($userId > 0) {
        $builder->where('mileage_records.user_id', $userId);
      }

      $items = $builder->findAll();
      return $this->json(['items' => $items, 'total' => count($items)]);
    }

    public function createMileageCode(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];
        $code = trim($data['code'] ?? '');
      $expiresAt = !empty($data['expires_at']) ? $data['expires_at'] : null;
        if (!$code) {
            return $this->json(['message' => 'code 為必填'], 400);
        }
        $miles = (int) ($data['miles_amount'] ?? 0);
        if ($miles <= 0) {
            return $this->json(['message' => 'miles_amount 必須大於 0'], 400);
        }

        $codeModel = model(\App\Models\MileageCodeModel::class);
        $escaped = \Config\Database::connect()->escape($code);
        if ($codeModel->where("BINARY `code` = {$escaped}", null, false)->first()) {
            return $this->json(['message' => '代碼已存在'], 409);
        }

        $id = $codeModel->insert([
            'code'        => $code,
            'description' => $data['description'] ?? null,
            'miles_amount'=> $miles,
            'usage_limit' => isset($data['usage_limit']) && $data['usage_limit'] !== '' ? (int) $data['usage_limit'] : null,
            'is_active'   => isset($data['is_active']) ? (int) $data['is_active'] : 1,
          'expires_at'  => $expiresAt,
        ]);

        return $this->json(['success' => true, 'id' => $id], 201);
    }

    public function updateMileageCode(int $id): ResponseInterface
    {
        $codeModel = model(\App\Models\MileageCodeModel::class);
        if (!$codeModel->find($id)) {
            return $this->json(['message' => 'Not found'], 404);
        }

        $data    = $this->request->getJSON(true) ?? [];
        $payload = [];
        if (isset($data['description']))  $payload['description']  = $data['description'];
        if (isset($data['miles_amount'])) $payload['miles_amount']  = (int) $data['miles_amount'];
        if (isset($data['usage_limit']))  $payload['usage_limit']   = $data['usage_limit'] !== '' ? (int) $data['usage_limit'] : null;
        if (isset($data['is_active']))    $payload['is_active']     = (int) $data['is_active'];
        if (isset($data['expires_at']))   $payload['expires_at']    = $data['expires_at'] ?: null;
        if (isset($data['code'])) {
            $code = trim($data['code']);
            $escapedCode = \Config\Database::connect()->escape($code);
            $dup  = $codeModel->where("BINARY `code` = {$escapedCode}", null, false)->where('id !=', $id)->first();
            if ($dup) {
                return $this->json(['message' => '代碼已存在'], 409);
            }
            $payload['code'] = $code;
        }

        $codeModel->update($id, $payload);
        return $this->json(['success' => true]);
    }

    public function deleteMileageCode(int $id): ResponseInterface
    {
        $codeModel = model(\App\Models\MileageCodeModel::class);
        if (!$codeModel->find($id)) {
            return $this->json(['message' => 'Not found'], 404);
        }
        $codeModel->delete($id);
        return $this->json(['success' => true]);
    }

    public function phoneVerifications(): ResponseInterface
    {
        $model  = new \App\Models\PhoneVerificationModel();
        $phone  = $this->request->getGet('phone');
        $status = $this->request->getGet('status');
        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit  = 50;

        $builder = $model->builder();

        if ($phone) {
            $builder->like('phone', $phone);
        }

        $now = date('Y-m-d H:i:s');
        if ($status === 'verified') {
            $builder->whereNotNull('verified_at');
        } elseif ($status === 'pending') {
            $builder->whereNull('verified_at')->where('expires_at >', $now)->where('is_used', 0);
        } elseif ($status === 'expired') {
            $builder->whereNull('verified_at')->where('expires_at <=', $now);
        } elseif ($status === 'used') {
            $builder->where('is_used', 1)->whereNull('verified_at');
        }

        $total  = $builder->countAllResults(false);
        $offset = ($page - 1) * $limit;
        $rows   = $builder->orderBy('created_at', 'DESC')->get($limit, $offset)->getResultArray();

        return $this->json([
            'data'  => $rows,
            'total' => $total,
            'page'  => $page,
            'pages' => ceil($total / $limit),
        ]);
    }

    // ── Customer Service ──────────────────────────────────────────────────────

    public function csConversations(): ResponseInterface
    {
        $db = \Config\Database::connect();

        // Get last message per ticket (distinct tickets with user info)
        $rows = $db->query("
            SELECT m.ticket_id, m.sender_id AS user_id,
                   u.email, u.full_name,
                   (SELECT content FROM customer_service_messages
                    WHERE ticket_id = m.ticket_id ORDER BY created_at DESC LIMIT 1) AS last_message,
                   (SELECT created_at FROM customer_service_messages
                    WHERE ticket_id = m.ticket_id ORDER BY created_at DESC LIMIT 1) AS last_at,
                   COUNT(m2.id) AS total_messages
            FROM customer_service_messages m
            INNER JOIN users u ON u.id = m.sender_id
            INNER JOIN customer_service_messages m2 ON m2.ticket_id = m.ticket_id
            WHERE m.sender_type = 'user'
            GROUP BY m.ticket_id, m.sender_id, u.email, u.full_name
            ORDER BY last_at DESC
        ")->getResultArray();

        return $this->json(['items' => $rows]);
    }

    public function csMessages(string $ticketId): ResponseInterface
    {
        $db    = \Config\Database::connect();
        $rows  = $db->table('customer_service_messages')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        return $this->json(['items' => $rows, 'ticket_id' => $ticketId]);
    }

    public function csSendMessage(string $ticketId): ResponseInterface
    {
        $data    = $this->request->getJSON(true) ?? [];
        $content = trim($data['content'] ?? '');

        if (!$content) {
            return $this->json(['message' => '訊息內容不得為空'], 400);
        }

        // Verify ticket exists
        $db  = \Config\Database::connect();
        $row = $db->table('customer_service_messages')->where('ticket_id', $ticketId)->get(1)->getRowArray();
        if (!$row) {
            return $this->json(['message' => '找不到此對話'], 404);
        }

        $createdAt = date('Y-m-d H:i:s');
        $db->table('customer_service_messages')->insert([
            'ticket_id'   => $ticketId,
            'sender_type' => 'admin',
            'sender_id'   => 0,
            'content'     => $content,
            'created_at'  => $createdAt,
        ]);
        $msgId = $db->insertID();

        // Push to connected user via WebSocket (fire-and-forget)
        \App\Libraries\WsNotifier::notify($ticketId, [
            'id'          => $msgId,
            'ticket_id'   => $ticketId,
            'sender_type' => 'admin',
            'sender_id'   => 0,
            'content'     => $content,
            'image_url'   => null,
            'created_at'  => $createdAt,
        ]);

        return $this->json(['success' => true, 'message' => '訊息已發送']);
    }

    public function smsLogs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = ['service_in' => ['twilio', 'firebase', 'topmessage']];

        if ($s = $this->request->getGet('service'))       $filters['service']       = $s;
        if ($a = $this->request->getGet('action'))        $filters['action']        = $a;
        if (($ok = $this->request->getGet('success')) !== null && $ok !== '') $filters['success'] = $ok;
        if ($df = $this->request->getGet('date_from'))    $filters['date_from']     = $df;
        if ($dt = $this->request->getGet('date_to'))      $filters['date_to']       = $dt;

        if (!empty($filters['service'])) {
            unset($filters['service_in']);
        }

        $result = (new \App\Repositories\ThirdPartyLogRepository())->paginate($page, $limit, $filters);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    // ── HTML Panel ────────────────────────────────────────────────────────────

    // ── Mails ─────────────────────────────────────────────────────────────────

    public function publicMailList(): ResponseInterface
    {
        $items = model(\App\Models\MailModel::class)->listActive();
        return $this->json(['items' => $items, 'total' => count($items)]);
    }

    public function mailList(): ResponseInterface
    {
        $items = model(\App\Models\MailModel::class)->listAll();
        return $this->json(['items' => $items, 'total' => count($items)]);
    }

    public function createMail(): ResponseInterface
    {
        $data    = $this->request->getJSON(true) ?? [];
        $subject = trim($data['subject'] ?? '');
        $content = trim($data['content'] ?? '');
        if (!$subject || !$content) {
            return $this->json(['message' => '主旨與內容不得為空'], 400);
        }
        $id = model(\App\Models\MailModel::class)->insert([
            'subject'    => $subject,
            'content'    => $content,
            'is_active'  => (int) ($data['is_active'] ?? 1),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        return $this->json(['id' => $id, 'message' => '信件已建立'], 201);
    }

    public function updateMail(int $id): ResponseInterface
    {
        $data  = $this->request->getJSON(true) ?? [];
        $model = model(\App\Models\MailModel::class);
        $mail  = $model->find($id);
        if (!$mail) {
            return $this->json(['message' => '信件不存在'], 404);
        }
        $update = [];
        if (isset($data['subject']))    $update['subject']    = trim($data['subject']);
        if (isset($data['content']))    $update['content']    = trim($data['content']);
        if (isset($data['is_active']))  $update['is_active']  = (int) $data['is_active'];
        if (isset($data['sort_order'])) $update['sort_order'] = (int) $data['sort_order'];
        $model->update($id, $update);
        return $this->json(['message' => '信件已更新']);
    }

    public function deleteMail(int $id): ResponseInterface
    {
        $model = model(\App\Models\MailModel::class);
        if (!$model->find($id)) {
            return $this->json(['message' => '信件不存在'], 404);
        }
        $model->delete($id);
        return $this->json(['message' => '信件已刪除']);
    }

    public function sendMailToUser(int $id): ResponseInterface
    {
        $mailModel = model(\App\Models\MailModel::class);
        $mail = $mailModel->find($id);
        if (!$mail) {
            return $this->json(['message' => '信件不存在'], 404);
        }

        $data   = $this->request->getJSON(true) ?? [];
        $userId = (int) ($data['user_id'] ?? 0);
        if (!$userId) {
            return $this->json(['message' => '請選擇使用者'], 400);
        }

        $user = model(\App\Models\UserModel::class)->find($userId);
        if (!$user) {
            return $this->json(['message' => '使用者不存在'], 404);
        }

        model(\App\Models\UserMailModel::class)->insert([
            'user_id' => $userId,
            'mail_id' => $id,
            'subject' => $mail['subject'],
            'content' => $mail['content'],
            'is_read' => 0,
        ]);

        return $this->json(['message' => '信件已發送']);
    }

    // ── Announcements ─────────────────────────────────────────────────────────

    public function announcementList(): ResponseInterface
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = min((int) ($this->request->getGet('limit') ?? 50), 100);
        $model = model(\App\Models\AnnouncementModel::class);
        $total = $model->countAll();
        $items = $model->orderBy('published_at', 'DESC')->paginate($limit, 'default', $page);
        return $this->json(['items' => $items ?? [], 'total' => $total]);
    }

    public function createAnnouncement(): ResponseInterface
    {
        $data  = $this->request->getJSON(true) ?? [];
        $title = trim($data['title'] ?? '');
        if (!$title) {
            return $this->json(['message' => '標題為必填'], 400);
        }
        $model = model(\App\Models\AnnouncementModel::class);
        $id    = $model->insert([
            'title'        => $title,
            'content'      => $data['content'] ?? '',
            'is_published' => isset($data['is_published']) ? (int) $data['is_published'] : 1,
            'published_at' => $data['published_at'] ?? date('Y-m-d H:i:s'),
        ], true);
        return $this->json(['success' => true, 'id' => $id], 201);
    }

    public function updateAnnouncement(int $id): ResponseInterface
    {
        $model = model(\App\Models\AnnouncementModel::class);
        if (!$model->find($id)) {
            return $this->json(['message' => '找不到公告'], 404);
        }
        $data    = $this->request->getJSON(true) ?? [];
        $payload = [];
        if (isset($data['title']))        $payload['title']        = trim($data['title']);
        if (isset($data['content']))      $payload['content']      = $data['content'];
        if (isset($data['is_published'])) $payload['is_published'] = (int) $data['is_published'];
        if (isset($data['published_at'])) $payload['published_at'] = $data['published_at'] ?: date('Y-m-d H:i:s');
        $model->update($id, $payload);
        return $this->json(['success' => true]);
    }

    public function deleteAnnouncement(int $id): ResponseInterface
    {
        $model = model(\App\Models\AnnouncementModel::class);
        if (!$model->find($id)) {
            return $this->json(['message' => '找不到公告'], 404);
        }
        $model->delete($id);
        return $this->json(['success' => true]);
    }

    public function index(): ResponseInterface
    {
        return $this->response->setBody($this->renderHtml());
    }

    private function renderHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>API 管理後台</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh}
  header{background:#1e293b;border-bottom:1px solid #334155;padding:1rem 2rem;display:flex;align-items:center;gap:1rem}
  header h1{font-size:1.25rem;font-weight:700;color:#f8fafc}
  header span{background:#3b82f6;color:#fff;font-size:.75rem;padding:.2rem .6rem;border-radius:9999px}
  .container{max-width:1400px;margin:0 auto;padding:1.5rem 2rem}
  .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
  .stat-card{background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.25rem}
  .stat-card .label{font-size:.8rem;color:#94a3b8;margin-bottom:.5rem}
  .stat-card .value{font-size:1.75rem;font-weight:700;color:#f8fafc}
  .panel{background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem}
  .panel h2{font-size:1rem;font-weight:600;margin-bottom:1rem;color:#f8fafc}
  .filters{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
  .filters input,.filters select{background:#0f172a;border:1px solid #334155;color:#e2e8f0;padding:.4rem .75rem;border-radius:.5rem;font-size:.85rem}
  .filters button{background:#3b82f6;color:#fff;border:none;padding:.4rem 1rem;border-radius:.5rem;cursor:pointer;font-size:.85rem}
  .filters button:hover{background:#2563eb}
  table{width:100%;border-collapse:collapse;font-size:.85rem}
  th{text-align:left;padding:.6rem .75rem;background:#0f172a;color:#94a3b8;font-weight:500;border-bottom:1px solid #334155}
  td{padding:.6rem .75rem;border-bottom:1px solid #1e293b;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  tr:hover td{background:#0f172a}
  .badge{display:inline-block;padding:.15rem .5rem;border-radius:.375rem;font-size:.75rem;font-weight:600}
  .badge-2xx{background:#14532d;color:#86efac}
  .badge-4xx{background:#431407;color:#fdba74}
  .badge-5xx{background:#450a0a;color:#fca5a5}
  .badge-get{background:#1e3a5f;color:#93c5fd}
  .badge-post{background:#14532d;color:#86efac}
  .badge-put{background:#713f12;color:#fde68a}
  .badge-delete{background:#450a0a;color:#fca5a5}
  .detail-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:50;overflow-y:auto}
  .detail-modal.open{display:flex;align-items:flex-start;justify-content:center;padding:2rem}
  .modal-box{background:#1e293b;border:1px solid #334155;border-radius:.75rem;width:100%;max-width:900px;padding:1.5rem}
  .modal-box h3{font-size:1rem;font-weight:600;margin-bottom:1rem;color:#f8fafc}
  .modal-box pre{background:#0f172a;border:1px solid #334155;border-radius:.5rem;padding:1rem;overflow-x:auto;font-size:.8rem;white-space:pre-wrap;word-break:break-all;max-height:400px;overflow-y:auto}
  .modal-close{float:right;background:#334155;border:none;color:#e2e8f0;padding:.3rem .75rem;border-radius:.375rem;cursor:pointer}
  .pagination{display:flex;gap:.5rem;align-items:center;margin-top:1rem}
  .pagination button{background:#1e293b;border:1px solid #334155;color:#e2e8f0;padding:.3rem .75rem;border-radius:.375rem;cursor:pointer}
  .pagination button:disabled{opacity:.4;cursor:not-allowed}
  .pagination button.active{background:#3b82f6;border-color:#3b82f6}
  .tabs{display:flex;gap:.5rem;margin-bottom:1.5rem}
  .tab{background:#1e293b;border:1px solid #334155;color:#94a3b8;padding:.5rem 1.25rem;border-radius:.5rem;cursor:pointer;font-size:.9rem}
  .tab.active{background:#3b82f6;border-color:#3b82f6;color:#fff}
  .section{display:none}.section.active{display:block}
</style>
</head>
<body>
<header>
  <h1>⚡ API 管理後台</h1>
  <span>免登入</span>
</header>
<div class="container">
  <div class="stats-grid" id="statsGrid"></div>

  <div class="tabs">
    <button class="tab active" onclick="switchTab('logs')">API 存取紀錄</button>
    <button class="tab" onclick="switchTab('users')">使用者管理</button>
  </div>

  <!-- API Logs Section -->
  <div class="section active" id="section-logs">
    <div class="panel">
      <h2>API 請求紀錄</h2>
      <div class="filters">
        <select id="f-method"><option value="">全部方法</option><option>GET</option><option>POST</option><option>PUT</option><option>DELETE</option><option>PATCH</option></select>
        <input id="f-uri" placeholder="URI 關鍵字" style="width:200px"/>
        <input id="f-user-id" placeholder="User ID" type="number" style="width:100px"/>
        <input id="f-code" placeholder="狀態碼" type="number" style="width:90px"/>
        <input id="f-date-from" type="datetime-local" title="起始時間"/>
        <input id="f-date-to" type="datetime-local" title="結束時間"/>
        <button onclick="loadLogs(1)">搜尋</button>
        <button onclick="clearFilters()">清除</button>
      </div>
      <table>
        <thead><tr><th>#</th><th>時間</th><th>方法</th><th>URI</th><th>IP</th><th>User</th><th>狀態</th><th>耗時</th><th>操作</th></tr></thead>
        <tbody id="logsBody"></tbody>
      </table>
      <div class="pagination" id="logsPagination"></div>
    </div>
  </div>

  <!-- Users Section -->
  <div class="section" id="section-users">
    <div class="panel">
      <h2>使用者列表</h2>
      <table>
        <thead><tr><th>ID</th><th>Email</th><th>姓名</th><th>角色</th><th>狀態</th><th>驗證</th><th>建立時間</th><th>操作</th></tr></thead>
        <tbody id="usersBody"></tbody>
      </table>
      <div class="pagination" id="usersPagination"></div>
    </div>
  </div>
</div>

<!-- Detail Modal -->
<div class="detail-modal" id="detailModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">✕ 關閉</button>
    <h3 id="modalTitle">Request / Response 詳情</h3>
    <pre id="modalContent"></pre>
  </div>
</div>

<script>
const BASE = '/admin-panel/api';
let logsPage = 1, usersPage = 1;

async function api(path) {
  const r = await fetch(BASE + path);
  return r.json();
}

async function loadStats() {
  const d = await api('/stats');
  document.getElementById('statsGrid').innerHTML = [
    ['使用者總數', d.total_users],
    ['API 紀錄總數', d.total_api_logs],
    ['24h 請求數', d.requests_24h],
  ].map(([l,v]) => `<div class="stat-card"><div class="label">${l}</div><div class="value">${(v||0).toLocaleString()}</div></div>`).join('');
}

function methodBadge(m) {
  return `<span class="badge badge-${m?.toLowerCase()}">${m}</span>`;
}
function codeBadge(c) {
  const cls = c >= 500 ? '5xx' : c >= 400 ? '4xx' : '2xx';
  return `<span class="badge badge-${cls}">${c}</span>`;
}

async function loadLogs(page = 1) {
  logsPage = page;
  const params = new URLSearchParams({ page, limit: 20 });
  const add = (k, id) => { const v = document.getElementById(id)?.value; if(v) params.set(k,v); };
  add('method','f-method'); add('uri','f-uri'); add('user_id','f-user-id');
  add('response_code','f-code'); add('date_from','f-date-from'); add('date_to','f-date-to');

  const d = await api('/logs?' + params);
  const tbody = document.getElementById('logsBody');
  tbody.innerHTML = (d.items || []).map(r => `
    <tr>
      <td>${r.id}</td>
      <td>${r.created_at}</td>
      <td>${methodBadge(r.method)}</td>
      <td title="${r.uri}">${r.uri}</td>
      <td>${r.ip_address}</td>
      <td>${r.user_id ?? '-'}</td>
      <td>${codeBadge(r.response_code)}</td>
      <td>${r.duration_ms}ms</td>
      <td><button onclick="showLog(${r.id})" style="background:#334155;border:none;color:#e2e8f0;padding:.2rem .5rem;border-radius:.375rem;cursor:pointer">詳情</button></td>
    </tr>`).join('');
  renderPagination('logsPagination', page, d.total, 20, loadLogs);
}

async function showLog(id) {
  const d = await api('/logs/' + id);
  document.getElementById('modalTitle').textContent = `Log #${id} — ${d.method} ${d.uri}`;
  document.getElementById('modalContent').textContent = JSON.stringify({
    request_headers: d.request_headers,
    request_body: tryParse(d.request_body),
    response_code: d.response_code,
    response_body: tryParse(d.response_body),
    duration_ms: d.duration_ms,
  }, null, 2);
  document.getElementById('detailModal').classList.add('open');
}

function tryParse(s) {
  try { return JSON.parse(s); } catch { return s; }
}

async function loadUsers(page = 1) {
  usersPage = page;
  const d = await api('/users?page=' + page + '&limit=20');
  document.getElementById('usersBody').innerHTML = (d.items || []).map(u => `
    <tr>
      <td>${u.id}</td>
      <td>${u.email}</td>
      <td>${u.full_name ?? '-'}</td>
      <td><span class="badge ${u.role==='admin'?'badge-5xx':'badge-2xx'}">${u.role}</span></td>
      <td>${u.status}</td>
      <td>${u.verify_status}</td>
      <td>${u.created_at}</td>
      <td><button onclick="showUserLogs(${u.id})" style="background:#334155;border:none;color:#e2e8f0;padding:.2rem .5rem;border-radius:.375rem;cursor:pointer">請求紀錄</button></td>
    </tr>`).join('');
  renderPagination('usersPagination', page, d.total, 20, loadUsers);
}

async function showUserLogs(userId) {
  switchTab('logs');
  document.getElementById('f-user-id').value = userId;
  loadLogs(1);
}

function renderPagination(containerId, current, total, limit, fn) {
  const pages = Math.ceil(total / limit);
  const el = document.getElementById(containerId);
  let html = `<button onclick="${fn.name}(${current-1})" ${current<=1?'disabled':''}>◀ 上一頁</button>`;
  html += `<span style="color:#94a3b8;font-size:.85rem">${current} / ${pages || 1} 頁（共 ${total} 筆）</span>`;
  html += `<button onclick="${fn.name}(${current+1})" ${current>=pages?'disabled':''}>下一頁 ▶</button>`;
  el.innerHTML = html;
}

function switchTab(tab) {
  document.querySelectorAll('.tab').forEach((t,i) => t.classList.toggle('active', ['logs','users'][i] === tab));
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('section-' + tab).classList.add('active');
  if (tab === 'logs') loadLogs(logsPage);
  if (tab === 'users') loadUsers(usersPage);
}

function closeModal() {
  document.getElementById('detailModal').classList.remove('open');
}

function clearFilters() {
  ['f-method','f-uri','f-user-id','f-code','f-date-from','f-date-to'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  loadLogs(1);
}

document.getElementById('detailModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Auto-refresh every 30 seconds
setInterval(() => { loadStats(); if (logsPage === 1) loadLogs(1); }, 30000);

loadStats();
loadLogs(1);
</script>
</body>
</html>
HTML;
    }
}
