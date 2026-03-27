<?php

namespace App\Services;

use App\Models\AppConfigModel;

/**
 * OtpProviderFactory — 根據設定動態選擇 OTP 服務提供者
 *
 * 優先順序：
 *   1. app_configs 表中 key='sms_provider' 的 DB override（由 sadmin 設定）
 *   2. .env 中 SMS_PROVIDER 的值
 *   3. 硬寫預設值 'twilio'
 */
class OtpProviderFactory
{
    public static function make(): OtpProviderInterface
    {
        return match (self::activeProvider()) {
            'firebase'    => new FirebaseOtpService(),
            'topmessage'  => new TopMessageService(),
            default       => new TwilioService(),
        };
    }

    /**
     * 取得目前實際使用的提供者名稱
     */
    public static function activeProvider(): string
    {
        $db = model(AppConfigModel::class)->getByKey('sms_provider');
        if (!empty($db['value'])) {
            return $db['value'];
        }
        return env('SMS_PROVIDER', 'twilio');
    }

    /**
     * 取得 .env 中設定的預設提供者
     */
    public static function envProvider(): string
    {
        return env('SMS_PROVIDER', 'twilio');
    }

    /**
     * 取得 DB override 值（null 表示未設定）
     */
    public static function dbOverride(): ?string
    {
        $db = model(AppConfigModel::class)->getByKey('sms_provider');
        return !empty($db['value']) ? $db['value'] : null;
    }

    /**
     * 清除 DB override，讓系統回落到 .env 設定
     */
    public static function clearDbOverride(): void
    {
        model(AppConfigModel::class)->setByKey('sms_provider', '');
    }
}
