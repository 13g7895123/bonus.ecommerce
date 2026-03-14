<?php

namespace App\Services;

use App\Repositories\AnnouncementRepository;

class AnnouncementService
{
    public function __construct(
        private readonly AnnouncementRepository $repo = new AnnouncementRepository(),
    ) {}

    public function getList(int $page = 1, int $limit = 20): array
    {
        $result = $this->repo->getPublished($page, $limit);
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
        $row = $this->repo->find($id);
        if (!$row || !$row['is_published']) {
            return null;
        }
        return $row;
    }

    /**
     * Batch create announcements — single INSERT (no loop queries).
     */
    public function createBatch(array $announcements): array
    {
        if (empty($announcements)) {
            return ['success' => true, 'created' => 0];
        }
        $this->repo->createBatch($announcements);
        return ['success' => true, 'created' => count($announcements)];
    }
}
