<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\WalletService;

class WalletController extends BaseApiController
{
    public function info()
    {
        $info = (new WalletService())->getInfo(Auth::id());
        if (!$info) {
            return $this->error('Wallet not found', 404);
        }
        return $this->success($info);
    }

    public function setPassword()
    {
        $data        = $this->getJson();
        $password    = $data['password'] ?? '';
        $oldPassword = $data['old_password'] ?? null;

        if (!$password) {
            return $this->error('Password is required');
        }
        if (strlen($password) < 4) {
            return $this->error('Password must be at least 4 characters');
        }

        $result = (new WalletService())->setWithdrawalPassword(Auth::id(), $password, $oldPassword);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success(null, $result['message']);
    }

    public function bindBank()
    {
        $data     = $this->getJson();
        $password = $data['withdrawal_password'] ?? '';

        if (empty($data['bank_name']) || empty($data['bank_account']) || empty($data['bank_account_name'])) {
            return $this->error('bank_name, bank_account, bank_account_name are required');
        }
        if (!$password) {
            return $this->error('withdrawal_password is required');
        }

        $result = (new WalletService())->bindBankAccount(Auth::id(), $data, $password);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success(null, $result['message']);
    }

    public function withdraw()
    {
        $data     = $this->getJson();
        $amount   = (float) ($data['amount'] ?? 0);
        $password = $data['withdrawal_password'] ?? '';

        if ($amount <= 0) {
            return $this->error('Invalid amount');
        }
        if (!$password) {
            return $this->error('withdrawal_password is required');
        }

        $result = (new WalletService())->withdraw(Auth::id(), $amount, $password);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success($result['data'], 'Withdrawal successful');
    }

    public function transactions()
    {
        $type  = $this->request->getGet('type');
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $result = (new WalletService())->getTransactions(Auth::id(), $type ?: null, $page, $limit);
        return $this->success($result);
    }
}
