<?php

namespace App\Services;

use App\Models\AppConfigModel;
use App\Models\SmsRateLimitModel;
use DateTime;

/**
 * SmsRateLimiter — 簡訊發送 IP 速率限制
 *
 * 設定來源：app_configs 資料表（key-value）
 *   sms_rate_limit_enabled — "1" 啟用 / "0" 停用（預設 "1"）
 *   sms_rate_limit_window  — 時間窗口（分鐘），預設 10
 *   sms_rate_limit_max     — 窗口內最大發送次數，預設 3
 *   sms_rate_limit_block   — 超出後封鎖時間（分鐘），預設 30
 */
class SmsRateLimiter
{
    private array $config;

    public function __construct()
    {
        $model = model(AppConfigModel::class);

        $this->config = [
            'enabled' => ($model->getByKey('sms_rate_limit_enabled')['value'] ?? '1') !== '0',
            'window'  => (int) ($model->getByKey('sms_rate_limit_window')['value'] ?? 10),
            'max'     => (int) ($model->getByKey('sms_rate_limit_max')['value']    ?? 3),
            'block'   => (int) ($model->getByKey('sms_rate_limit_block')['value']  ?? 30),
        ];

        // 最低安全值，防止 sadmin 設定 0 造成邏輯錯誤
        if ($this->config['window'] < 1)  $this->config['window'] = 1;
        if ($this->config['max'] < 1)     $this->config['max']    = 1;
        if ($this->config['block'] < 1)   $this->config['block']  = 1;
    }

    /**
     * 檢查並記錄 IP 發送嘗試（原子化）
     *
     * @return array ['allowed' => bool, 'message' => string, 'retry_after' => int|null（秒）]
     */
    public function checkAndRecord(string $ip): array
    {
        if (!$this->config['enabled']) {
            return ['allowed' => true, 'message' => '', 'retry_after' => null];
        }

        $model  = new SmsRateLimitModel();
        $now    = new DateTime();
        $record = $model->findByIp($ip);

        if ($record) {
            // 1. 仍在封鎖期
            if (!empty($record['blocked_until'])) {
                $blockedUntil = new DateTime($record['blocked_until']);
                if ($blockedUntil > $now) {
                    $retryAfter = $blockedUntil->getTimestamp() - $now->getTimestamp();
                    $minutes    = (int) ceil($retryAfter / 60);
                    return [
                        'allowed'     => false,
                        'message'     => "發送次數過多，請等候約 {$minutes} 分鐘後再試",
                        'retry_after' => $retryAfter,
                    ];
                }
            }

            $windowStart = new DateTime($record['window_start']);
            $windowEnd   = (clone $windowStart)->modify("+{$this->config['window']} minutes");

            if ($now >= $windowEnd) {
                // 2. 窗口已過期，重置計數
                $model->update($record['id'], [
                    'window_start'  => $now->format('Y-m-d H:i:s'),
                    'send_count'    => 1,
                    'blocked_until' => null,
                ]);
            } elseif ((int) $record['send_count'] >= $this->config['max']) {
                // 3. 窗口內已超出上限，立即封鎖
                $blockedUntil = (clone $now)->modify("+{$this->config['block']} minutes");
                $model->update($record['id'], [
                    'blocked_until' => $blockedUntil->format('Y-m-d H:i:s'),
                ]);
                $block = $this->config['block'];
                return [
                    'allowed'     => false,
                    'message'     => "您已超過 {$this->config['window']} 分鐘內 {$this->config['max']} 次的發送上限，請等候 {$block} 分鐘後再試",
                    'retry_after' => $block * 60,
                ];
            } else {
                // 4. 窗口內尚未超出，累加計數
                $model->update($record['id'], [
                    'send_count' => (int) $record['send_count'] + 1,
                ]);
            }
        } else {
            // 5. 首次記錄
            $model->insert([
                'ip'           => $ip,
                'window_start' => $now->format('Y-m-d H:i:s'),
                'send_count'   => 1,
                'blocked_until'=> null,
            ]);
        }

        return ['allowed' => true, 'message' => '', 'retry_after' => null];
    }

    /**
     * 強制解封指定 IP
     */
    public static function unblockIp(string $ip): bool
    {
        $model  = new SmsRateLimitModel();
        $record = $model->findByIp($ip);
        if (!$record) return false;

        return (bool) $model->update($record['id'], [
            'send_count'    => 0,
            'blocked_until' => null,
        ]);
    }

    /**
     * 清除所有速率限制紀錄（sadmin 一鍵重置）
     */
    public static function clearAll(): int
    {
        $model = new SmsRateLimitModel();
        $count = $model->countAll();
        $model->truncate();
        return $count;
    }

    /**
     * 取得目前所有封鎖中的 IP 清單（供 sadmin 顯示）
     */
    public static function blockedIps(): array
    {
        $model = new SmsRateLimitModel();
        $now   = date('Y-m-d H:i:s');
        return $model
            ->where('blocked_until >', $now)
            ->orderBy('blocked_until', 'DESC')
            ->findAll();
    }

    /**
     * 回傳目前設定（供 sadmin 顯示）
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
