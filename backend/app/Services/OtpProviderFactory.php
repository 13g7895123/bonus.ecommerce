<?php

namespace App\Services;

use App\Models\AppConfigModel;

/**
 * OtpProviderFactory — 根據後台設定動態選擇 OTP 服務提供者
 *
 * 讀取 app_configs 表中 key='sms_provider' 的值（'twilio' 或 'firebase'），
 * 預設為 'twilio'。
 */
class OtpProviderFactory
{
    public static function make(): OtpProviderInterface
    {
        $config   = model(AppConfigModel::class)->getByKey('sms_provider');
        $provider = $config['value'] ?? 'twilio';

        return match ($provider) {
            'firebase' => new FirebaseOtpService(),
            default    => new TwilioService(),
        };
    }

    /**
     * 取得目前設定的提供者名稱
     */
    public static function currentProvider(): string
    {
        $config = model(AppConfigModel::class)->getByKey('sms_provider');
        return $config['value'] ?? 'twilio';
    }
}
