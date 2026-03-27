<?php

namespace App\Services;

use App\Models\AppConfigModel;
use App\Models\PhoneVerificationModel;
use App\Repositories\ThirdPartyLogRepository;

/**
 * TopMessageService — 使用 TopMessage REST API 發送 OTP 簡訊
 *
 * API 文件：https://topmessage.com/documentation-api/send-message
 *
 * 架構說明：
 *   TopMessage 是純 SMS 閘道，不提供驗證碼管理。
 *   因此本服務在後端自行生成 6 位數 OTP 並儲存至 phone_verifications 資料表，
 *   再透過 TopMessage API 發送給使用者；驗證時比對資料庫中的驗證碼。
 *
 * 設定（透過 sadmin 後台儲存至 app_configs 資料表）：
 *   topmessage_api_key — TopMessage API Key
 *   topmessage_sender  — 發送者名稱（最多 11 個字元）
 */
class TopMessageService implements OtpProviderInterface
{
    private const API_ENDPOINT = 'https://api.topmessage.com/v1/messages';

    private string $apiKey;
    private string $sender;
    private PhoneVerificationModel $model;

    public function __construct()
    {
        $configModel    = model(AppConfigModel::class);
        $this->apiKey   = $configModel->getByKey('topmessage_api_key')['value'] ?? '';
        $this->sender   = $configModel->getByKey('topmessage_sender')['value'] ?? 'OTP';
        $this->model    = new PhoneVerificationModel();
    }

    /**
     * 生成 6 位數 OTP，儲存至 phone_verifications，並透過 TopMessage API 發送
     *
     * @param string $phone   收件電話（E.164 格式，例如 +886912345678）
     * @param array  $options 目前未使用（保留供未來擴充）
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $phone, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'TopMessage 設定不完整，請至 sadmin 後台設定 API Key 與寄件者名稱'];
        }

        $phone = $this->normalizeE164($phone);

        // 生成 6 位數 OTP
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $text   = "您的驗證碼為 {$code}，10 分鐘內有效，請勿告知他人。";
        $result = $this->callApi($phone, $text, 'sendOtp');

        log_message('info', '[TopMessageService] sendOtp To=' . $phone . ' HTTP=' . $result['http_code']);

        if ($result['http_code'] !== 200 || empty($result['body']['data'])) {
            $errorMsg = $result['body']['message'] ?? ('簡訊發送失敗（HTTP ' . $result['http_code'] . '）');
            log_message('error', '[TopMessageService] sendOtp failed: ' . json_encode($result['body']));
            return ['success' => false, 'message' => $errorMsg];
        }

        // 將此號碼舊的未使用紀錄標記為已失效
        $this->model
            ->where('phone', $phone)
            ->where('is_used', 0)
            ->set(['is_used' => 1])
            ->update();

        // 建立新的驗證紀錄
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $this->model->insert([
            'phone'      => $phone,
            'code'       => $code,
            'provider'   => 'topmessage',
            'attempts'   => 0,
            'is_used'    => 0,
            'expires_at' => $expiresAt,
        ]);

        return ['success' => true, 'message' => '驗證碼已發送'];
    }

    /**
     * 比對使用者輸入的 OTP 與資料庫中儲存的驗證碼
     *
     * @param string $phone 收件電話（E.164 格式）
     * @param string $code  使用者輸入的 6 位數驗證碼
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyOtp(string $phone, string $code): array
    {
        $phone = $this->normalizeE164($phone);

        $record = $this->model->findLatestValid($phone);

        if (!$record) {
            return ['success' => false, 'message' => '驗證碼不存在或已過期，請重新發送'];
        }

        // 更新嘗試次數
        $this->model->update($record['id'], ['attempts' => (int) $record['attempts'] + 1]);

        if ((int) $record['attempts'] + 1 >= 5) {
            $this->model->update($record['id'], ['is_used' => 1]);
            return ['success' => false, 'message' => '嘗試次數過多，請重新發送驗證碼'];
        }

        if ($record['code'] !== $code) {
            return ['success' => false, 'message' => '驗證碼錯誤，請重新輸入'];
        }

        // 驗證成功
        $this->model->update($record['id'], [
            'is_used'     => 1,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => '驗證成功'];
    }

    // ── 私有方法 ──────────────────────────────────────────────────────

    private function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->sender);
    }

    /**
     * 正規化電話號碼為 E.164 格式
     */
    private function normalizeE164(string $phone): string
    {
        return preg_replace('/^(\+\d{1,4})0(\d{6,11})$/', '$1$2', $phone);
    }

    /**
     * 遮蔽 TopMessage response body 中的 text 欄位，避免 OTP 明碼儲存進 DB
     */
    private function sanitizeResponse(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['data'])) {
            return $raw;
        }
        foreach ($decoded['data'] as &$item) {
            if (isset($item['text'])) {
                $item['text'] = '[REDACTED]';
            }
            if (isset($item['to'])) {
                $item['to'] = '[MASKED]';
            }
        }
        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 呼叫 TopMessage REST API 發送訊息，並記錄至 third_party_logs
     */
    private function callApi(string $phone, string $text, string $action = 'request'): array
    {
        $startTime = microtime(true);

        $payload = json_encode([
            'data' => [
                'from' => $this->sender,
                'to'   => [$phone],
                'text' => $text,
            ],
        ]);

        $ch = curl_init(self::API_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-TopMessage-Key: ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // 記錄時隱藏電話號碼與 API key（已在 header 中，不記錄進 request_body）
        $safePayload = json_encode([
            'data' => [
                'from' => $this->sender,
                'to'   => ['[MASKED]'],
                'text' => '[OTP_TEXT]',
            ],
        ]);

        try {
            (new ThirdPartyLogRepository())->create([
                'service'       => 'topmessage',
                'action'        => $action,
                'method'        => 'POST',
                'url'           => self::API_ENDPOINT,
                'request_body'  => $safePayload,
                'response_code' => $curlError ? 0 : $httpCode,
                'response_body' => $curlError ? $curlError : $this->sanitizeResponse((string) $response),
                'duration_ms'   => $durationMs,
                'success'       => (!$curlError && $httpCode === 200) ? 1 : 0,
                'error_message' => $curlError ?: null,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // 永遠不讓日誌記錄破壞主流程
        }

        if ($curlError) {
            log_message('error', '[TopMessageService] cURL error: ' . $curlError);
            return ['http_code' => 0, 'body' => ['message' => '網路錯誤：' . $curlError]];
        }

        return [
            'http_code' => $httpCode,
            'body'      => json_decode($response, true) ?? [],
        ];
    }
}
