<?php

namespace App\Services;

/**
 * TwilioService - 使用 Twilio Verify API 發送與驗證 OTP
 *
 * 需要在 .env 設定：
 *   TWILIO_ACCOUNT_SID      = ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   TWILIO_AUTH_TOKEN       = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   TWILIO_VERIFY_SERVICE_SID = VSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * 如何取得 Verify Service SID：
 *   Twilio Console → Verify → Services → Create new Service → 複製 Service SID
 */
class TwilioService
{
    private string $accountSid;
    private string $authToken;
    private string $verifyServiceSid;

    public function __construct()
    {
        $this->accountSid       = env('TWILIO_ACCOUNT_SID', '');
        $this->authToken        = env('TWILIO_AUTH_TOKEN', '');
        $this->verifyServiceSid = env('TWILIO_VERIFY_SERVICE_SID', '');
    }

    /**
     * 發送 OTP 驗證碼（透過 Twilio Verify）
     *
     * @param string $to      收件電話（E.164 格式，例如 +886912345678）
     * @param string $channel 發送管道：sms（預設）或 call
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $to, string $channel = 'sms'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Twilio 設定不完整，請確認 .env 中的 TWILIO_* 環境變數'];
        }

        $url = sprintf(
            'https://verify.twilio.com/v2/Services/%s/Verifications',
            $this->verifyServiceSid
        );

        $result = $this->curlPost($url, ['To' => $to, 'Channel' => $channel]);

        if ($result['http_code'] === 201) {
            return ['success' => true, 'message' => '驗證碼已發送'];
        }

        $errorMsg = $result['body']['message'] ?? ('簡訊發送失敗（HTTP ' . $result['http_code'] . '）');
        log_message('error', '[TwilioService] sendOtp failed: ' . json_encode($result['body']));

        return ['success' => false, 'message' => $errorMsg];
    }

    /**
     * 驗證使用者輸入的 OTP
     *
     * @param string $to   收件電話（E.164 格式）
     * @param string $code 使用者輸入的 6 位數驗證碼
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyOtp(string $to, string $code): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Twilio 設定不完整'];
        }

        $url = sprintf(
            'https://verify.twilio.com/v2/Services/%s/VerificationChecks',
            $this->verifyServiceSid
        );

        $result = $this->curlPost($url, ['To' => $to, 'Code' => $code]);

        if ($result['http_code'] === 200 && ($result['body']['status'] ?? '') === 'approved') {
            return ['success' => true, 'message' => '驗證成功'];
        }

        if ($result['http_code'] === 404) {
            return ['success' => false, 'message' => '驗證碼不存在或已過期'];
        }

        $status = $result['body']['status'] ?? 'unknown';
        if ($status === 'pending') {
            return ['success' => false, 'message' => '驗證碼錯誤，請重新輸入'];
        }

        log_message('error', '[TwilioService] verifyOtp failed: ' . json_encode($result['body']));
        return ['success' => false, 'message' => '驗證失敗'];
    }

    // ── 私有方法 ──────────────────────────────────────────────────────

    private function isConfigured(): bool
    {
        return !empty($this->accountSid)
            && !empty($this->authToken)
            && !empty($this->verifyServiceSid);
    }

    private function curlPost(string $url, array $fields): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->accountSid . ':' . $this->authToken,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', '[TwilioService] cURL error: ' . $curlError);
            return ['http_code' => 0, 'body' => ['message' => '網路錯誤：' . $curlError]];
        }

        return [
            'http_code' => $httpCode,
            'body'      => json_decode($response, true) ?? [],
        ];
    }
}
