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
}
