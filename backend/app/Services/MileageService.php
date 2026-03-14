<?php

namespace App\Services;

use App\Repositories\MileageRecordRepository;
use App\Repositories\UserWalletRepository;

class MileageService
{
    public function __construct(
        private readonly MileageRecordRepository $repo       = new MileageRecordRepository(),
        private readonly UserWalletRepository    $walletRepo = new UserWalletRepository(),
    ) {}

    public function getHistory(int $userId, int $page = 1, int $limit = 20): array
    {
        $result = $this->repo->getByUserId($userId, $page, $limit);
        $wallet = $this->walletRepo->findByUserId($userId);
        $result['currentMiles'] = $wallet ? (int) $wallet['miles_balance'] : 0;
        return $result;
    }

    public function redeem(int $userId, string $code): array
    {
        // 简單校驗：以 BONUS 開頭的代碼給予 500 哩程
        if (strlen($code) < 4) {
            return ['success' => false, 'message' => '無效的里程代碼'];
        }
        if (strtoupper(substr($code, 0, 5)) !== 'BONUS') {
            return ['success' => false, 'message' => '無效的里程代碼'];
        }

        $bonus  = 500;
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'Wallet not found'];
        }

        $newMiles = (int) $wallet['miles_balance'] + $bonus;
        $this->walletRepo->updateByUserId($userId, ['miles_balance' => $newMiles]);
        $this->repo->createBatch([[
            'user_id'    => $userId,
            'type'       => 'earn',
            'amount'     => $bonus,
            'source'     => 'code_redeem',
            'created_at' => date('Y-m-d H:i:s'),
        ]]);

        return ['success' => true, 'message' => "成功兌換 {$bonus} 哩程數", 'miles_earned' => $bonus, 'miles_balance' => $newMiles];
    }

    /**
     * Batch award mileage — single insertBatch + single updateBatch (no loop queries).
     *
     * @param array $awards [['user_id'=>1,'amount'=>100,'source'=>'reward'], ...]
     */
    public function awardBatch(array $awards): array
    {
        if (empty($awards)) {
            return ['success' => true, 'awarded' => 0];
        }

        $userIds = array_unique(array_column($awards, 'user_id'));

        // Single query to load wallets
        $rawWallets = model(\App\Models\UserWalletModel::class)
            ->whereIn('user_id', $userIds)
            ->findAll();
        $walletMap = [];
        foreach ($rawWallets as $w) {
            $walletMap[(int) $w['user_id']] = (int) $w['miles_balance'];
        }

        // Aggregate awards per user
        $totals = [];
        foreach ($awards as $a) {
            $uid = (int) $a['user_id'];
            $totals[$uid] = ($totals[$uid] ?? ($walletMap[$uid] ?? 0)) + (int) $a['amount'];
        }

        // Build updateBatch rows and mileage record rows
        $walletUpdates = [];
        foreach ($totals as $uid => $newMiles) {
            $walletUpdates[] = ['user_id' => $uid, 'miles_balance' => $newMiles];
        }

        $now = date('Y-m-d H:i:s');
        $recordRows = array_map(static fn($a) => [
            'user_id'    => (int) $a['user_id'],
            'type'       => 'earn',
            'amount'     => (int) $a['amount'],
            'source'     => $a['source'] ?? 'batch_award',
            'created_at' => $now,
        ], $awards);

        // Single updateBatch + single insertBatch
        $this->walletRepo->updateBatchByUserIds($walletUpdates);
        $this->repo->createBatch($recordRows);

        return ['success' => true, 'awarded' => count($awards)];
    }
}
