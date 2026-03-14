<?php

namespace App\Services;

use App\Repositories\CustomerServiceMessageRepository;

class CustomerServiceService
{
    public function __construct(
        private readonly CustomerServiceMessageRepository $repo = new CustomerServiceMessageRepository(),
    ) {}

    public function getMessages(int $userId, int $page = 1, int $limit = 50): array
    {
        $ticketId = $this->repo->getOrCreateTicket($userId);
        $result   = $this->repo->getByTicket($ticketId, $page, $limit);
        $result['ticket_id'] = $ticketId;
        return $result;
    }

    public function sendMessage(int $userId, ?string $content, $imageFile = null): array
    {
        $ticketId  = $this->repo->getOrCreateTicket($userId);
        $imagePath = null;

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $uploadDir = WRITEPATH . 'uploads/cs/' . $userId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $name      = $imageFile->getRandomName();
            $imageFile->move($uploadDir, $name);
            $imagePath = 'cs/' . $userId . '/' . $name;
        }

        $this->repo->create([
            'ticket_id'   => $ticketId,
            'sender_type' => 'user',
            'sender_id'   => $userId,
            'content'     => $content,
            'image_path'  => $imagePath,
        ]);

        return ['success' => true, 'data' => ['ticket_id' => $ticketId]];
    }

    /**
     * Batch send messages (e.g. admin broadcast) — single INSERT.
     */
    public function sendBatch(array $messages): array
    {
        if (empty($messages)) {
            return ['success' => true, 'sent' => 0];
        }
        $this->repo->createBatch($messages);
        return ['success' => true, 'sent' => count($messages)];
    }
}
