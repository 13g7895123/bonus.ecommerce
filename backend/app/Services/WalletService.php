<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\UserWalletRepository;

class WalletService
{
    public function __construct(
        private readonly UserWalletRepository  $walletRepo = new UserWalletRepository(),
        private readonly TransactionRepository $txRepo     = new TransactionRepository(),
    ) {}

    public function getInfo(int $userId): ?array
    {
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return null;
        }
        $wallet['has_bank_account'] = !empty($wallet['bank_account']);
        $wallet['has_withdrawal_pw'] = !empty($wallet['withdrawal_password_hash']);
        if (!empty($wallet['bank_account'])) {
            $acc = $wallet['bank_account'];
            $wallet['bank_account_masked'] = str_repeat('*', max(0, strlen($acc) - 4)) . substr($acc, -4);
        }
        unset($wallet['bank_account'], $wallet['withdrawal_password_hash']);
        $wallet['balance']       = (float) $wallet['balance'];
        $wallet['miles_balance'] = (int) $wallet['miles_balance'];
        return $wallet;
    }

    public function setWithdrawalPassword(int $userId, string $password, ?string $oldPassword = null): array
    {
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'Wallet not found'];
        }
        if (!empty($wallet['withdrawal_password_hash']) && $oldPassword !== null) {
            if (!password_verify($oldPassword, $wallet['withdrawal_password_hash'])) {
                return ['success' => false, 'message' => 'Old password incorrect'];
            }
        }
        $this->walletRepo->updateByUserId($userId, [
            'withdrawal_password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        return ['success' => true, 'message' => 'Withdrawal password updated'];
    }

    public function bindBankAccount(int $userId, array $data, string $withdrawalPassword): array
    {
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet || empty($wallet['withdrawal_password_hash'])) {
            return ['success' => false, 'message' => 'Please set a withdrawal password first'];
        }
        if (!password_verify($withdrawalPassword, $wallet['withdrawal_password_hash'])) {
            return ['success' => false, 'message' => 'Withdrawal password incorrect'];
        }
        $this->walletRepo->updateByUserId($userId, [
            'bank_name'              => $data['bank_name'],
            'bank_branch'            => $data['bank_branch'] ?? null,
            'bank_account'           => $data['bank_account'],
            'bank_account_name'      => $data['bank_account_name'],
            'bank_passbook_file_id'  => $data['bank_passbook_file_id'] ?? null,
        ]);
        return ['success' => true, 'message' => 'Bank account bound'];
    }

    public function withdraw(int $userId, float $amount, string $withdrawalPassword): array
    {
        $wallet = $this->walletRepo->findByUserId($userId);
        if (!$wallet) {
            return ['success' => false, 'message' => 'Wallet not found'];
        }
        if (empty($wallet['withdrawal_password_hash'])) {
            return ['success' => false, 'message' => 'Please set a withdrawal password first'];
        }
        if (!password_verify($withdrawalPassword, $wallet['withdrawal_password_hash'])) {
            return ['success' => false, 'message' => 'Withdrawal password incorrect'];
        }
        if (empty($wallet['bank_account'])) {
            return ['success' => false, 'message' => 'Please bind a bank account first'];
        }
        if ((float) $wallet['balance'] < $amount) {
            return ['success' => false, 'message' => 'Insufficient balance'];
        }

        $newBalance = (float) $wallet['balance'] - $amount;
        $this->walletRepo->updateByUserId($userId, ['balance' => $newBalance]);
        $this->txRepo->create([
            'user_id'      => $userId,
            'type'         => 'withdrawal',
            'amount'       => $amount,
            'status'       => 'completed',
            'description'  => 'Withdrawal to bank account',
            'reference_id' => 'WD_' . time() . '_' . $userId,
        ]);

        return ['success' => true, 'data' => ['balance' => $newBalance]];
    }

    public function getTransactions(int $userId, ?string $type, int $page, int $limit): array
    {
        return $this->txRepo->getByUserId($userId, $type, $page, $limit);
    }
}
