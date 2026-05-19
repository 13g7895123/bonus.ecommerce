<?php

namespace App\Controllers\Api;

use App\Libraries\Auth;
use App\Services\AnnouncementService;

class AnnouncementController extends BaseApiController
{
    public function index()
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);

        $result = (new AnnouncementService())->getList($page, $limit);
        return $this->success($result);
    }

    public function show(int $id)
    {
        $userId = null;
        try {
            if ($this->request->getHeaderLine('Authorization')) {
                $userId = Auth::id();
            }
        } catch (\Throwable) {
            $userId = null;
        }

        $item = (new AnnouncementService())->getById($id, $userId);
        if (!$item) {
            return $this->error('找不到公告', 404);
        }
        return $this->success($item);
    }
}
