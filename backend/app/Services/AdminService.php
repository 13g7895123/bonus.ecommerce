<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;

class AdminService
{
    public function __construct(
        private readonly UserRepository       $userRepo        = new UserRepository(),
        private readonly UserWalletRepository $walletRepo      = new UserWalletRepository(),
        private readonly TransactionRepository $txRepo         = new TransactionRepository(),
    ) {}

    public function listUsers(int $page = 1, int $limit = 20): array
    {
        $result = $this->userRepo->paginate($page, $limit);

        // Batch load wallets — single query, no loop queries
        $userIds = array_column($result['items'], 'id');
        $wallets = [];
        if (!empty($userIds)) {
            $rawWallets = model(\App\Models\UserWalletModel::class)
                ->whereIn('user_id', $userIds)
                ->findAll();
            foreach ($rawWallets as $w) {
                $wallets[(int) $w['user_id']] = $w;
            }
        }

        $result['items'] = array_map(static function (array $user) use ($wallets): array {
            unset($user['password_hash']);
            $wallet = $wallets[$user['id']] ?? null;
            $user['balance']       = $wallet ? (float) $wallet['balance'] : 0;
            $user['miles_balance'] = $wallet ? (int) $wallet['miles_balance'] : 0;
            return $user;
        }, $result['items']);

        return $result;
    }

    public function adjustBalance(int $userId, float $amount, string $description): array
    {
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'User wallet not found'];
        }
        $newBalance = (float) $wallet['balance'] + $amount;
        if ($newBalance < 0) {
            return ['success' => false, 'message' => 'Cannot deduct more than available balance'];
        }
        $this->walletRepo->updateByUserId($userId, ['balance' => $newBalance]);
        $this->txRepo->create([
            'user_id'     => $userId,
            'type'        => 'adjustment',
            'amount'      => $amount,
            'status'      => 'completed',
            'description' => $description ?: ($amount >= 0 ? 'Admin top-up' : 'Admin deduction'),
        ]);
        return ['success' => true, 'data' => ['balance' => $newBalance]];
    }

    /**
     * Batch adjust balances — avoid N+1 by loading all wallets in one query,
     * then issuing a single updateBatch and a single insertBatch.
     *
     * @param array $adjustments  [['user_id'=>1,'amount'=>100,'description'=>'...'], ...]
     */
    public function adjustBalanceBatch(array $adjustments): array
    {
        if (empty($adjustments)) {
            return ['success' => true, 'message' => 'Nothing to adjust'];
        }

        $userIds = array_unique(array_column($adjustments, 'user_id'));

        // Single query — all wallets at once
        $rawWallets = model(\App\Models\UserWalletModel::class)
            ->whereIn('user_id', $userIds)
            ->findAll();
        $walletMap = [];
        foreach ($rawWallets as $w) {
            $walletMap[(int) $w['user_id']] = $w;
        }

        $walletUpdates = [];
        $txRows        = [];
        $errors        = [];

        foreach ($adjustments as $adj) {
            $uid    = (int) $adj['user_id'];
            $amount = (float) $adj['amount'];
            $wallet = $walletMap[$uid] ?? null;

            if (!$wallet) {
                $errors[] = "User {$uid}: wallet not found";
                continue;
            }

            $newBalance = (float) $wallet['balance'] + $amount;
            if ($newBalance < 0) {
                $errors[] = "User {$uid}: insufficient balance";
                continue;
            }

            $walletUpdates[] = ['user_id' => $uid, 'balance' => $newBalance];
            $txRows[]        = [
                'user_id'     => $uid,
                'type'        => 'adjustment',
                'amount'      => $amount,
                'status'      => 'completed',
                'description' => $adj['description'] ?? ($amount >= 0 ? 'Admin top-up' : 'Admin deduction'),
            ];
        }

        // Single updateBatch — no per-row SQL
        if (!empty($walletUpdates)) {
            $this->walletRepo->updateBatchByUserIds($walletUpdates);
        }
        // Single insertBatch
        if (!empty($txRows)) {
            $this->txRepo->createBatch($txRows);
        }

        return ['success' => true, 'adjusted' => count($walletUpdates), 'errors' => $errors];
    }

    public function reviewVerification(int $userId, string $action, ?string $reason = null): array
    {
        $user = $this->userRepo->find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        if (!in_array($action, ['approve', 'reject'], true)) {
            return ['success' => false, 'message' => 'Invalid action'];
        }

        $updates = [
            'verify_status' => $action === 'approve' ? 'verified' : 'rejected',
            'is_verified'   => $action === 'approve' ? 1 : 0,
        ];
        if ($action === 'reject' && $reason) {
            $verData = json_decode($user['verification_data'] ?? '{}', true);
            $verData['reject_reason'] = $reason;
            $updates['verification_data'] = json_encode($verData);
        }
        $this->userRepo->update($userId, $updates);

        return ['success' => true, 'message' => 'Verification ' . ($action === 'approve' ? 'approved' : 'rejected')];
    }
}
