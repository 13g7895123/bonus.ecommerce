<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\UserModel;
use App\Models\UserWalletModel;

class AdminService
{
    public function listUsers(int $page = 1, int $limit = 20): array
    {
        $model  = model(UserModel::class);
        $total  = $model->countAll();
        $users  = $model->orderBy('created_at', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->findAll();

        $walletModel = model(UserWalletModel::class);
        foreach ($users as &$user) {
            unset($user['password_hash']);
            $wallet = $walletModel->findByUserId($user['id']);
            $user['balance']       = $wallet ? (float) $wallet['balance'] : 0;
            $user['miles_balance'] = $wallet ? (int) $wallet['miles_balance'] : 0;
        }
        unset($user);

        return ['items' => $users, 'total' => $total];
    }

    public function adjustBalance(int $userId, float $amount, string $description): array
    {
        $wallet = model(UserWalletModel::class)->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'User wallet not found'];
        }
        $newBalance = (float) $wallet['balance'] + $amount;
        if ($newBalance < 0) {
            return ['success' => false, 'message' => 'Cannot deduct more than available balance'];
        }
        model(UserWalletModel::class)->updateByUserId($userId, ['balance' => $newBalance]);
        model(TransactionModel::class)->insert([
            'user_id'     => $userId,
            'type'        => 'adjustment',
            'amount'      => $amount,
            'status'      => 'completed',
            'description' => $description ?: ($amount >= 0 ? 'Admin top-up' : 'Admin deduction'),
        ]);
        return ['success' => true, 'data' => ['balance' => $newBalance]];
    }

    public function reviewVerification(int $userId, string $action, ?string $reason = null): array
    {
        $user = model(UserModel::class)->find($userId);
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
        model(UserModel::class)->update($userId, $updates);

        return ['success' => true, 'message' => 'Verification ' . ($action === 'approve' ? 'approved' : 'rejected')];
    }
}
