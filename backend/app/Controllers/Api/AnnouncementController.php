<?php

namespace App\Controllers\Api;

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
        $item = (new AnnouncementService())->getById($id);
        if (!$item) {
            return $this->error('Announcement not found', 404);
        }
        return $this->success($item);
    }
}
