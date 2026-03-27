<?php

namespace App\Services;

use App\Models\PhoneVerificationModel;
use App\Repositories\ThirdPartyLogRepository;

/**
 * FirebaseOtpService — 使用 Firebase Authentication Phone Auth 驗證 OTP
 *
 * 架構說明：
 *   Firebase Phone Auth (Web) 的 OTP 簡訊必須由前端 Firebase JS SDK 發送，
 *   因為 Firebase Identity Toolkit REST API 的 sendVerificationCode 端點
 *   要求前端 reCAPTCHA 驗證（用來確保是真實瀏覽器環境）。
 *
 *   因此本服務採用「前端發送、後端驗證」的混合架構：
 *
 *   1. 前端使用 Firebase JS SDK (signInWithPhoneNumber) 觸發簡訊發送，
 *      取得 verificationId（即 Firebase 的 sessionInfo）。
 *   2. 前端將 verificationId 連同電話號碼傳至後端 sendPhoneOtp API。
 *   3. 本服務的 sendOtp() 接收 session_info 並寫入 phone_verifications 資料表。
 *   4. 使用者輸入驗證碼後，前端傳送 phone + code 至 verifyPhoneOtp API。
 *   5. 本服務的 verifyOtp() 從資料庫取出 session_info，
 *      呼叫 Firebase REST API 完成後端側驗證。
 *
 * 需要在 .env 設定（後端）：
 *   FIREBASE_WEB_API_KEY = AIza...
 *
 * 需要在前端 .env 設定：
 *   VITE_FIREBASE_API_KEY      = AIza...
 *   VITE_FIREBASE_AUTH_DOMAIN  = your-project.firebaseapp.com
 *   VITE_FIREBASE_PROJECT_ID   = your-project-id
 */
class FirebaseOtpService implements OtpProviderInterface
{
    private string $webApiKey;
    private PhoneVerificationModel $model;

    public function __construct()
    {
        $this->webApiKey = env('FIREBASE_WEB_API_KEY', '');
        $this->model     = new PhoneVerificationModel();
    }

    /**
     * 登記 Firebase OTP 發送紀錄
     *
     * 本方法不主動呼叫 Firebase API 發送簡訊（由前端 SDK 負責）；
     * 僅接收前端傳來的 session_info（Firebase verificationId）並寫入資料庫。
     *
     * @param string $phone   收件電話（E.164 格式）
     * @param array  $options 必須包含 'session_info'（前端 Firebase SDK 回傳的 verificationId）
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $phone, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Firebase 設定不完整，請確認 .env 中的 FIREBASE_WEB_API_KEY'];
        }

        $sessionInfo = trim($options['session_info'] ?? '');
        if (empty($sessionInfo)) {
            return ['success' => false, 'message' => '缺少 Firebase session_info（verificationId），請確認前端已使用 Firebase SDK 發送驗證碼'];
        }

        $phone = $this->normalizeE164($phone);

        // 將此號碼舊的未使用紀錄標記為已失效
        $this->model
            ->where('phone', $phone)
            ->where('provider', 'firebase')
            ->where('is_used', 0)
            ->set(['is_used' => 1])
            ->update();

        // 建立新的驗證紀錄，session_info 存放 Firebase verificationId
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $this->model->insert([
            'phone'        => $phone,
            'code'         => 'FIREBASE_OTP',
            'provider'     => 'firebase',
            'session_info' => $sessionInfo,
            'attempts'     => 0,
            'is_used'      => 0,
            'expires_at'   => $expiresAt,
        ]);

        log_message('info', '[FirebaseOtpService] sendOtp registered phone=' . $phone);
        return ['success' => true, 'message' => '驗證碼已發送（Firebase）'];
    }

    /**
     * 透過 Firebase Identity Toolkit REST API 驗證 OTP
     *
     * @param string $phone 收件電話（E.164 格式）
     * @param string $code  使用者輸入的 6 位數驗證碼
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyOtp(string $phone, string $code): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Firebase 設定不完整'];
        }

        $phone = $this->normalizeE164($phone);

        // 從資料庫取得最新有效的 Firebase 驗證紀錄
        $record = $this->model
            ->where('phone', $phone)
            ->where('provider', 'firebase')
            ->where('is_used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => '驗證碼不存在或已過期，請重新發送'];
        }

        // 更新嘗試次數
        $this->model->update($record['id'], ['attempts' => (int) $record['attempts'] + 1]);

        $sessionInfo = $record['session_info'] ?? '';
        if (empty($sessionInfo)) {
            return ['success' => false, 'message' => '驗證紀錄異常，請重新發送驗證碼'];
        }

        // 呼叫 Firebase Identity Toolkit REST API 進行後端側驗證
        $url    = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key=' . urlencode($this->webApiKey);
        $result = $this->curlPost($url, [
            'sessionInfo' => $sessionInfo,
            'code'        => $code,
        ], 'verifyOtp');

        log_message('info', '[FirebaseOtpService] verifyOtp phone=' . $phone . ' HTTP=' . $result['http_code']);

        if ($result['http_code'] === 200 && !empty($result['body']['idToken'])) {
            // 驗證成功
            $this->model->update($record['id'], [
                'is_used'     => 1,
                'verified_at' => date('Y-m-d H:i:s'),
            ]);
            return ['success' => true, 'message' => '驗證成功'];
        }

        $errorMsg = $result['body']['error']['message'] ?? null;
        log_message('error', '[FirebaseOtpService] verifyOtp failed: ' . json_encode($result['body']));

        return match ($errorMsg) {
            'INVALID_CODE'                     => ['success' => false, 'message' => '驗證碼錯誤，請重新輸入'],
            'CODE_EXPIRED'                     => ['success' => false, 'message' => '驗證碼已過期，請重新發送'],
            'SESSION_EXPIRED'                  => ['success' => false, 'message' => '驗證階段已過期，請重新發送'],
            'TOO_MANY_ATTEMPTS_TRY_LATER'      => ['success' => false, 'message' => '嘗試次數過多，請稍後再試'],
            default                            => ['success' => false, 'message' => '驗證失敗，請稍後再試'],
        };
    }

    // ── 私有方法 ──────────────────────────────────────────────────────

    private function isConfigured(): bool
    {
        return !empty($this->webApiKey);
    }

    private function normalizeE164(string $phone): string
    {
        return preg_replace('/^(\+\d{1,4})0(\d{6,11})$/', '$1$2', $phone);
    }

    private function curlPost(string $url, array $payload, string $action = 'request'): array
    {
        $startTime = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Strip API key from logged URL
        $logUrl = preg_replace('/[?&]key=[^&]*/', '?key=[REDACTED]', $url);
        // Mask sensitive fields
        $safePayload = $payload;
        unset($safePayload['sessionInfo'], $safePayload['code']);

        // Mask response (remove idToken)
        $responseBody = $response;
        $responseDecoded = json_decode((string) $response, true);
        if (is_array($responseDecoded)) {
            unset($responseDecoded['idToken'], $responseDecoded['refreshToken']);
            $responseBody = json_encode($responseDecoded, JSON_UNESCAPED_UNICODE);
        }

        try {
            (new ThirdPartyLogRepository())->create([
                'service'       => 'firebase',
                'action'        => $action,
                'method'        => 'POST',
                'url'           => $logUrl,
                'request_body'  => json_encode($safePayload, JSON_UNESCAPED_UNICODE),
                'response_code' => $curlError ? 0 : $httpCode,
                'response_body' => $curlError ? $curlError : $responseBody,
                'duration_ms'   => $durationMs,
                'success'       => (!$curlError && $httpCode === 200) ? 1 : 0,
                'error_message' => $curlError ?: null,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Never let logging break the service
        }

        if ($curlError) {
            log_message('error', '[FirebaseOtpService] cURL error: ' . $curlError);
            return ['http_code' => 0, 'body' => ['error' => ['message' => '網路錯誤：' . $curlError]]];
        }

        return [
            'http_code' => $httpCode,
            'body'      => json_decode($response, true) ?? [],
        ];
    }
}
