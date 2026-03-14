<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\MileageService;

class MileageController extends BaseApiController
{
    public function history()
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $result = (new MileageService())->getHistory(Auth::id(), $page, $limit);
        return $this->success($result);
    }

    public function redeem()
    {
        $data = $this->getJson();
        $code = trim($data['code'] ?? '');

        if (!$code) {
            return $this->error('請輸入里程代碼', 422);
        }

        $result = (new MileageService())->redeem(Auth::id(), $code);
        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }
        return $this->success([
            'miles_earned'  => $result['miles_earned'],
            'miles_balance' => $result['miles_balance'],
        ], $result['message']);
    }
}
