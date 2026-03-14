<?php

namespace App\Services;

use App\Models\AnnouncementModel;

class AnnouncementService
{
    public function getList(int $page = 1, int $limit = 20): array
    {
        $result = model(AnnouncementModel::class)->getPublished($page, $limit);
        // Return only title + date for list view
        $result['items'] = array_map(fn($a) => [
            'id'           => $a['id'],
            'title'        => $a['title'],
            'published_at' => $a['published_at'],
        ], $result['items']);
        return $result;
    }

    public function getById(int $id): ?array
    {
        $model = model(AnnouncementModel::class);
        $row   = $model->where('id', $id)->where('is_published', 1)->first();
        return $row ?: null;
    }
}
