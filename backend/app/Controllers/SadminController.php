<?php

namespace App\Controllers;

use App\Models\AppConfigModel;
use App\Repositories\ApiLogRepository;
use App\Repositories\ThirdPartyLogRepository;
use App\Services\OtpProviderFactory;
use App\Services\SmsRateLimiter;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SadminController — 超級管理後台，顯示所有 API 紀錄（含第三方外部呼叫）
 *
 * 路由前綴: /api/v1/sadmin
 * 本控制器不要求登入，與 AdminPanelController 相同設計（內部工具）。
 */
class SadminController extends Controller
{
    private function json(mixed $data, int $code = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setJSON($data);
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats(): ResponseInterface
    {
        $apiRepo = new ApiLogRepository();
        $tpRepo  = new ThirdPartyLogRepository();

        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

        $totalApi   = $apiRepo->paginate(1, 1);
        $recentApi  = $apiRepo->paginate(1, 1, ['date_from' => $yesterday]);
        $totalTp    = $tpRepo->paginate(1, 1);
        $recentTp   = $tpRepo->paginate(1, 1, ['date_from' => $yesterday]);
        $failedTp   = $tpRepo->paginate(1, 1, ['date_from' => $yesterday, 'success' => '0']);

        return $this->json([
            'total_api_logs'         => $totalApi['total'],
            'api_requests_24h'       => $recentApi['total'],
            'total_third_party_logs' => $totalTp['total'],
            'third_party_calls_24h'  => $recentTp['total'],
            'third_party_failures_24h' => $failedTp['total'],
        ]);
    }

    // ── Incoming API Logs ─────────────────────────────────────────────────────

    public function apiLogs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = array_filter([
            'method'        => $this->request->getGet('method'),
            'uri'           => $this->request->getGet('uri'),
            'user_id'       => $this->request->getGet('user_id'),
            'response_code' => $this->request->getGet('response_code'),
            'date_from'     => $this->request->getGet('date_from'),
            'date_to'       => $this->request->getGet('date_to'),
        ]);

        $result = (new ApiLogRepository())->paginate($page, $limit, $filters);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    public function apiLogDetail(int $id): ResponseInterface
    {
        $log = model(\App\Models\ApiLogModel::class)->find($id);
        if (!$log) {
            return $this->json(['message' => 'Log not found'], 404);
        }
        if (is_string($log['request_headers'])) {
            $log['request_headers'] = json_decode($log['request_headers'], true);
        }
        return $this->json($log);
    }

    // ── Third-Party API Logs ──────────────────────────────────────────────────

    public function thirdPartyLogs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = [];

        if ($s = $this->request->getGet('service'))       $filters['service']       = $s;
        if ($a = $this->request->getGet('action'))        $filters['action']        = $a;
        if ($c = $this->request->getGet('response_code')) $filters['response_code'] = $c;
        if (($ok = $this->request->getGet('success')) !== null) $filters['success'] = $ok;
        if ($df = $this->request->getGet('date_from'))    $filters['date_from']     = $df;
        if ($dt = $this->request->getGet('date_to'))      $filters['date_to']       = $dt;

        $result = (new ThirdPartyLogRepository())->paginate($page, $limit, $filters);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    public function thirdPartyLogDetail(int $id): ResponseInterface
    {
        $log = model(\App\Models\ThirdPartyLogModel::class)->find($id);
        if (!$log) {
            return $this->json(['message' => 'Log not found'], 404);
        }
        return $this->json($log);
    }

    // ── SMS Provider ──────────────────────────────────────────────────────────

    public function smsProviderInfo(): ResponseInterface
    {
        $dbOverride  = OtpProviderFactory::dbOverride();
        $envProvider = OtpProviderFactory::envProvider();
        $active      = OtpProviderFactory::activeProvider();

        return $this->json([
            'env_value'   => $envProvider,
            'db_override' => $dbOverride,
            'active'      => $active,
            'source'      => $dbOverride !== null ? 'db' : 'env',
        ]);
    }

    public function setSmsProvider(): ResponseInterface
    {
        $data     = $this->request->getJSON(true) ?? [];
        $provider = trim($data['provider'] ?? '');

        if (!in_array($provider, ['twilio', 'firebase', 'topmessage'], true)) {
            return $this->json(['message' => 'Invalid provider. Must be twilio, firebase or topmessage.'], 400);
        }

        model(AppConfigModel::class)->setByKey('sms_provider', $provider);

        return $this->json([
            'success'  => true,
            'active'   => $provider,
            'source'   => 'db',
        ]);
    }

    public function resetSmsProvider(): ResponseInterface
    {
        OtpProviderFactory::clearDbOverride();
        $envProvider = OtpProviderFactory::envProvider();

        return $this->json([
            'success'  => true,
            'active'   => $envProvider,
            'source'   => 'env',
        ]);
    }

    // ── TopMessage Config ─────────────────────────────────────────────────────

    public function topmessageConfig(): ResponseInterface
    {
        $configModel = model(AppConfigModel::class);
        $apiKeyRow   = $configModel->getByKey('topmessage_api_key');
        $senderRow   = $configModel->getByKey('topmessage_sender');

        $apiKey = $apiKeyRow['value'] ?? '';
        $sender = $senderRow['value'] ?? '';

        // 回傳時遮罩 API Key（只顯示後 4 碼）
        $maskedKey = '';
        if (!empty($apiKey)) {
            $len = strlen($apiKey);
            $maskedKey = str_repeat('*', max(0, $len - 4)) . substr($apiKey, -4);
        }

        return $this->json([
            'api_key_set'  => !empty($apiKey),
            'api_key_mask' => $maskedKey,
            'sender'       => $sender,
        ]);
    }

    public function saveTopmessageConfig(): ResponseInterface
    {
        $data   = $this->request->getJSON(true) ?? [];
        $apiKey = trim($data['api_key'] ?? '');
        $sender = trim($data['sender'] ?? '');

        if (empty($sender)) {
            return $this->json(['message' => '寄件者名稱不可為空'], 400);
        }
        if (mb_strlen($sender) > 11) {
            return $this->json(['message' => '寄件者名稱最多 11 個字元'], 400);
        }

        $configModel = model(AppConfigModel::class);

        if (!empty($apiKey)) {
            $configModel->setByKey('topmessage_api_key', $apiKey);
        } else {
            // 未提供新 key 時，確認 DB 中已存在舊 key
            $existing = $configModel->getByKey('topmessage_api_key');
            if (empty($existing['value'])) {
                return $this->json(['message' => '首次設定時必須提供 API Key'], 400);
            }
        }

        $configModel->setByKey('topmessage_sender', $sender);

        return $this->json(['success' => true, 'message' => 'TopMessage 設定已儲存']);
    }

    // ── SMS Logs ──────────────────────────────────────────────────────────────

    public function smsLogs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = ['service_in' => ['twilio', 'firebase', 'topmessage']];

        if ($s = $this->request->getGet('service'))       $filters['service']       = $s;
        if ($a = $this->request->getGet('action'))        $filters['action']        = $a;
        if ($c = $this->request->getGet('response_code')) $filters['response_code'] = $c;
        if (($ok = $this->request->getGet('success')) !== null && $ok !== '') $filters['success'] = $ok;
        if ($df = $this->request->getGet('date_from'))    $filters['date_from']     = $df;
        if ($dt = $this->request->getGet('date_to'))      $filters['date_to']       = $dt;

        // 如果有指定 service，移除 service_in 改用精確 where
        if (!empty($filters['service'])) {
            unset($filters['service_in']);
        }

        $result = (new ThirdPartyLogRepository())->paginate($page, $limit, $filters);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    // ── SMS Security Settings ─────────────────────────────────────────────────

    public function smsSecurityConfig(): ResponseInterface
    {
        $rl     = new SmsRateLimiter();
        $config = $rl->getConfig();

        return $this->json([
            'enabled' => $config['enabled'],
            'window'  => $config['window'],
            'max'     => $config['max'],
            'block'   => $config['block'],
        ]);
    }

    public function saveSmsSecurityConfig(): ResponseInterface
    {
        $data    = $this->request->getJSON(true) ?? [];
        $model   = model(AppConfigModel::class);

        $enabled = isset($data['enabled']) ? ($data['enabled'] ? '1' : '0') : null;
        $window  = isset($data['window'])  ? (int) $data['window']  : null;
        $max     = isset($data['max'])     ? (int) $data['max']     : null;
        $block   = isset($data['block'])   ? (int) $data['block']   : null;

        if ($enabled !== null) {
            $model->setByKey('sms_rate_limit_enabled', $enabled);
        }
        if ($window !== null) {
            if ($window < 1 || $window > 1440) {
                return $this->json(['message' => '時間窗口需介於 1～1440 分鐘'], 400);
            }
            $model->setByKey('sms_rate_limit_window', (string) $window);
        }
        if ($max !== null) {
            if ($max < 1 || $max > 100) {
                return $this->json(['message' => '最大發送次數需介於 1～100'], 400);
            }
            $model->setByKey('sms_rate_limit_max', (string) $max);
        }
        if ($block !== null) {
            if ($block < 1 || $block > 10080) {
                return $this->json(['message' => '封鎖時間需介於 1～10080 分鐘'], 400);
            }
            $model->setByKey('sms_rate_limit_block', (string) $block);
        }

        return $this->json(['success' => true, 'message' => 'SMS 安全設定已儲存']);
    }

    public function smsBlockedIps(): ResponseInterface
    {
        $blocked = SmsRateLimiter::blockedIps();
        return $this->json(['items' => $blocked, 'total' => count($blocked)]);
    }

    public function smsUnblockIp(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];
        $ip   = trim($data['ip'] ?? '');

        if (!$ip) {
            return $this->json(['message' => 'IP 不可為空'], 400);
        }

        $ok = SmsRateLimiter::unblockIp($ip);
        if (!$ok) {
            return $this->json(['message' => '查無此 IP 紀錄'], 404);
        }

        return $this->json(['success' => true, 'message' => "已解封 {$ip}"]);
    }

    public function smsClearRateLimits(): ResponseInterface
    {
        $count = SmsRateLimiter::clearAll();
        return $this->json(['success' => true, 'cleared' => $count, 'message' => "已清除 {$count} 筆速率限制紀錄"]);
    }

    // ── SMS Verification Mode ─────────────────────────────────────────────────

    /**
     * GET /api/v1/sadmin/sms-verification-mode
     * 取得「是否強制驗證簡訊才能完成註冊」的開關狀態。
     * 預設 enabled = true（正式模式，需驗證 OTP）。
     */
    public function getSmsVerificationMode(): ResponseInterface
    {
        $row     = model(AppConfigModel::class)->getByKey('sms_verification_required');
        $enabled = ($row['value'] ?? '1') === '1';

        return $this->json(['enabled' => $enabled]);
    }

    /**
     * POST /api/v1/sadmin/sms-verification-mode
     * Body: { "enabled": true | false }
     * 設定是否強制驗證簡訊。關閉時，前台發送驗證碼後自動完成註冊（測試模式）。
     */
    public function setSmsVerificationMode(): ResponseInterface
    {
        $data    = $this->request->getJSON(true) ?? [];
        if (!array_key_exists('enabled', $data)) {
            return $this->json(['message' => '缺少 enabled 參數'], 400);
        }

        $enabled = (bool) $data['enabled'];
        model(AppConfigModel::class)->setByKey('sms_verification_required', $enabled ? '1' : '0');

        return $this->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled ? 'SMS 驗證已啟用（正式模式）' : 'SMS 驗證已停用（測試模式）',
        ]);
    }

}
