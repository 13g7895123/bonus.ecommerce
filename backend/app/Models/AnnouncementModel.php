<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table         = 'announcements';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['title', 'content', 'is_published', 'published_at'];

    /**
     * @return array{items: array, total: int}
     */
    public function getPublished(int $page = 1, int $limit = 20): array
    {
        $total = $this->where('is_published', 1)->countAllResults(false);
        $items = $this->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->findAll();

        return ['items' => $items, 'total' => $total];
    }
}
