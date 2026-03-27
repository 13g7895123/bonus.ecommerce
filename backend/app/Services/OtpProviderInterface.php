<?php

namespace App\Services;

/**
 * OTP 提供者介面
 *
 * 所有 SMS / OTP 提供者皆須實作此介面，確保 AuthController 得以透過
 * OtpProviderFactory 取得任一提供者並以統一方式呼叫。
 */
interface OtpProviderInterface
{
    /**
     * 發送 OTP
     *
     * @param string $phone   收件電話（E.164 格式，例如 +886912345678）
     * @param array  $options 額外參數（各提供者視需要使用）
     *                        - Twilio：不需額外參數
     *                        - Firebase：需傳入 ['session_info' => '...']
     *                          （由前端 Firebase SDK 呼叫 signInWithPhoneNumber 後取得）
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $phone, array $options = []): array;

    /**
     * 驗證 OTP
     *
     * @param string $phone 收件電話（E.164 格式）
     * @param string $code  使用者輸入的 6 位數驗證碼
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyOtp(string $phone, string $code): array;
}
