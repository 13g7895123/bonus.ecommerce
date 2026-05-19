<?php

namespace App\Services;

use App\Models\AnnouncementReadModel;
use App\Repositories\AnnouncementRepository;

class AnnouncementService
{
    public function __construct(
        private readonly AnnouncementRepository $repo = new AnnouncementRepository(),
    ) {}

    public function getList(int $page = 1, int $limit = 20): array
    {
        $result = $this->repo->getPublished($page, $limit);
        $result['items'] = array_map(fn($a) => [
            'id'           => $a['id'],
            'title'        => $a['title'],
            'content'      => $a['content'],
            'content_text' => $this->toPlainText($a['content'] ?? ''),
            'is_pinned'    => (int) ($a['is_pinned'] ?? 0),
            'published_at' => $a['published_at'],
        ], $result['items']);
        $result['top_announcement'] = $result['items'][0] ?? null;
        return $result;
    }

    public function getById(int $id, ?int $userId = null): ?array
    {
        $row = $this->repo->find($id);
        if (!$row || !$row['is_published']) {
            return null;
        }
        if ($userId) {
            $read = model(AnnouncementReadModel::class)
                ->where('user_id', $userId)
                ->where('announcement_id', $id)
                ->first();
            $row['is_read'] = $read ? 1 : 0;
        }
        $row['content_text'] = $this->toPlainText($row['content'] ?? '');
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

    public function markRead(int $userId, int $announcementId): bool
    {
        $announcement = $this->repo->find($announcementId);
        if (!$announcement || !(int) $announcement['is_published']) {
            return false;
        }

        $model = model(AnnouncementReadModel::class);
        $row = $model->where('user_id', $userId)->where('announcement_id', $announcementId)->first();

        if ($row) {
            $model->update($row['id'], ['read_at' => date('Y-m-d H:i:s')]);
            return true;
        }

        $model->insert([
            'user_id'         => $userId,
            'announcement_id' => $announcementId,
            'read_at'         => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    private function toPlainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text ?? '');
        return trim($text ?? '');
    }
}
