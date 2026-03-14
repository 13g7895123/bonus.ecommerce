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
