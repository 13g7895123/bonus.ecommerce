<?php

namespace App\Services;

use App\Models\AppConfigModel;
use App\Models\MileageCodeModel;
use App\Repositories\MileageRecordRepository;
use App\Repositories\UserWalletRepository;
use App\Repositories\UserRepository;

class MileageService
{
    public function __construct(
        private readonly MileageRecordRepository $repo       = new MileageRecordRepository(),
        private readonly UserWalletRepository    $walletRepo = new UserWalletRepository(),
        private readonly MileageCodeModel        $codeModel  = new MileageCodeModel(),
        private readonly AppConfigModel          $configModel = new AppConfigModel(),
        private readonly UserRepository          $userRepo   = new UserRepository(),
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
        if ($code === '') {
            return ['success' => false, 'message' => '請輸入里程代碼'];
        }

        $record = $this->codeModel->findValidCode($code);
        if (!$record) {
            return ['success' => false, 'message' => '無效或已失效的里程代碼'];
        }

        $bonus  = (int) $record['miles_amount'];
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'Wallet not found'];
        }

        $newMiles = (int) $wallet['miles_balance'] + $bonus;
        $this->walletRepo->updateByUserId($userId, ['miles_balance' => $newMiles]);
        $this->codeModel->incrementUsed((int) $record['id']);
        $this->repo->createBatch([[
            'user_id'    => $userId,
            'type'       => 'earn',
            'amount'     => $bonus,
            'source'     => 'code_redeem',
            'code_id'    => (int) $record['id'],
            'code'       => (string) $record['code'],
            'created_at' => date('Y-m-d H:i:s'),
        ]]);

        $this->upgradeUserTierIfNeeded($userId, $newMiles);

        return ['success' => true, 'message' => "成功兌換 {$bonus} 哩程數", 'miles_earned' => $bonus, 'miles_balance' => $newMiles];
    }

    private function upgradeUserTierIfNeeded(int $userId, int $totalMiles): void
    {
        $silver   = (int) ($this->configModel->getByKey('tier_silver_miles')['value']   ?? 25000);
        $gold     = (int) ($this->configModel->getByKey('tier_gold_miles')['value']     ?? 50000);
        $platinum = (int) ($this->configModel->getByKey('tier_platinum_miles')['value'] ?? 100000);

        if ($totalMiles >= $platinum) {
            $newTier = 'platinum';
        } elseif ($totalMiles >= $gold) {
            $newTier = 'gold';
        } elseif ($totalMiles >= $silver) {
            $newTier = 'silver';
        } else {
            return;
        }

        $user = $this->userRepo->find($userId);
        if (!$user) return;

        $tierOrder = ['regular' => 0, 'silver' => 1, 'gold' => 2, 'platinum' => 3];
        $currentTier = $user['tier'] ?? 'regular';
        if (($tierOrder[$newTier] ?? 0) > ($tierOrder[$currentTier] ?? 0)) {
            $this->userRepo->update($userId, ['tier' => $newTier]);
        }
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
