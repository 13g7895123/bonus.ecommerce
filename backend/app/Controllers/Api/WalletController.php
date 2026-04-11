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
            return $this->error('請輸入密碼');
        }
        if (strlen($password) < 4) {
            return $this->error('密碼長度至少需要 4 個字元');
        }

        $result = (new WalletService())->setWithdrawalPassword(Auth::id(), $password, $oldPassword);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success(null, $result['message']);
    }

    public function bindBank()
    {
        $data = $this->getJson();

        if (empty($data['bank_name']) || empty($data['bank_account']) || empty($data['bank_account_name'])) {
            return $this->error('請填寫銀行名稱、帳號及戶名');
        }

        $result = (new WalletService())->bindBankAccount(Auth::id(), $data);
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
            return $this->error('金額無效');
        }
        if (!$password) {
            return $this->error('請輸入提款密碼');
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
