<?php

namespace App\Controllers\Api;

use App\Services\AdminService;

class AdminController extends BaseApiController
{
    public function users()
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $result = (new AdminService())->listUsers($page, $limit);
        return $this->success($result);
    }

    public function adjustBalance(int $userId)
    {
        $data   = $this->getJson();
        $amount = $data['amount'] ?? null;

        if ($amount === null) {
            return $this->error('amount is required');
        }

        $result = (new AdminService())->adjustBalance(
            $userId,
            (float) $amount,
            $data['description'] ?? ''
        );

        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success($result['data'], 'Balance adjusted');
    }

    public function reviewVerification(int $userId)
    {
        $data   = $this->getJson();
        $action = $data['action'] ?? '';
        $reason = $data['reason'] ?? null;

        if (!$action) {
            return $this->error('action is required (approve or reject)');
        }

        $result = (new AdminService())->reviewVerification($userId, $action, $reason);
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        return $this->success(null, $result['message']);
    }
}
