<?php

namespace App\Services;

use App\Models\PhoneVerificationModel;

/**
 * TwilioService - 使用 Twilio Verify API 發送與驗證 OTP，並將操作紀錄儲存至 phone_verifications 資料表
 *
 * 需要在 .env 設定：
 *   TWILIO_ACCOUNT_SID        = ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   TWILIO_AUTH_TOKEN         = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   TWILIO_VERIFY_SERVICE_SID = VSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * 如何取得 Verify Service SID：
 *   Twilio Console → Verify → Services → Create new Service → 複製 Service SID
 *
 * 備註：Twilio Verify 由 Twilio 管理實際驗證碼內容，資料庫 code 欄位固定儲存 'TWILIO_VERIFY' 作為識別。
 */
class TwilioService implements OtpProviderInterface
{
    private string $accountSid;
    private string $authToken;
    private string $verifyServiceSid;
    private PhoneVerificationModel $model;

    public function __construct()
    {
        $this->accountSid       = env('TWILIO_ACCOUNT_SID', '');
        $this->authToken        = env('TWILIO_AUTH_TOKEN', '');
        $this->verifyServiceSid = env('TWILIO_VERIFY_SERVICE_SID', '');
        $this->model            = new PhoneVerificationModel();
    }

    /**
     * 發送 OTP 驗證碼（透過 Twilio Verify），並寫入 phone_verifications 紀錄
     *
     * @param string $to      收件電話（E.164 格式，例如 +886912345678）
     * @param string $channel 發送管道：sms（預設）或 call
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $to, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Twilio 設定不完整，請確認 .env 中的 TWILIO_* 環境變數'];
        }

        $to = $this->normalizeE164($to);

        $url = sprintf(
            'https://verify.twilio.com/v2/Services/%s/Verifications',
            $this->verifyServiceSid
        );

        $result = $this->curlPost($url, ['To' => $to, 'Channel' => $options['channel'] ?? 'sms']);

        log_message('info', '[TwilioService] sendOtp To=' . $to . ' HTTP=' . $result['http_code']);

        if ($result['http_code'] !== 201) {
            $errorMsg = $result['body']['message'] ?? ('簡訊發送失敗（HTTP ' . $result['http_code'] . '）');
            log_message('error', '[TwilioService] sendOtp failed: ' . json_encode($result['body']));
            return ['success' => false, 'message' => $errorMsg];
        }

        // 將此號碼舊的未使用紀錄標記為已失效
        $this->model
            ->where('phone', $to)
            ->where('is_used', 0)
            ->set(['is_used' => 1])
            ->update();

        // 建立新的發送紀錄（code 欄位以固定識別字串替代，實際碼由 Twilio 管理）
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $this->model->insert([
            'phone'      => $to,
            'code'       => 'TWILIO_VERIFY',
            'provider'   => 'twilio',
            'attempts'   => 0,
            'is_used'    => 0,
            'expires_at' => $expiresAt,
        ]);

        return ['success' => true, 'message' => '驗證碼已發送'];
    }

    /**
     * 驗證使用者輸入的 OTP（透過 Twilio Verify），並更新 phone_verifications 紀錄
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

        $to = $this->normalizeE164($to);

        // 更新嘗試次數（從資料庫找到此號碼最新的有效紀錄）
        $record = $this->model->findLatestValid($to);
        if ($record) {
            $this->model->update($record['id'], ['attempts' => (int) $record['attempts'] + 1]);
        }

        $url = sprintf(
            'https://verify.twilio.com/v2/Services/%s/VerificationCheck',
            $this->verifyServiceSid
        );

        $result = $this->curlPost($url, ['To' => $to, 'Code' => $code]);

        log_message('info', '[TwilioService] verifyOtp To=' . $to . ' HTTP=' . $result['http_code']);

        if ($result['http_code'] === 200 && ($result['body']['status'] ?? '') === 'approved') {
            // 驗證成功，標記紀錄並記錄時間
            if ($record) {
                $this->model->update($record['id'], [
                    'is_used'     => 1,
                    'verified_at' => date('Y-m-d H:i:s'),
                ]);
            }
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

    /**
     * 將電話號碼正規化為 E.164 格式
     * 例：+8860912345678 → +886912345678
     */
    private function normalizeE164(string $phone): string
    {
        return preg_replace('/^(\+\d{1,4})0(\d{6,11})$/', '$1$2', $phone);
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
