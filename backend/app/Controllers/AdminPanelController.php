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
        return $this->json($user);
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

    // ── HTML Panel ────────────────────────────────────────────────────────────

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
