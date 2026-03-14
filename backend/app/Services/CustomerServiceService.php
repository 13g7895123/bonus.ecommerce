<?php

namespace App\Services;

use App\Models\CustomerServiceMessageModel;

class CustomerServiceService
{
    public function getMessages(int $userId, int $page = 1, int $limit = 50): array
    {
        $model    = model(CustomerServiceMessageModel::class);
        $ticketId = $model->getOrCreateTicket($userId);
        $result   = $model->getByTicket($ticketId, $page, $limit);
        $result['ticket_id'] = $ticketId;
        return $result;
    }

    public function sendMessage(int $userId, ?string $content, $imageFile = null): array
    {
        $model    = model(CustomerServiceMessageModel::class);
        $ticketId = $model->getOrCreateTicket($userId);

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

        $model->insert([
            'ticket_id'   => $ticketId,
            'sender_type' => 'user',
            'sender_id'   => $userId,
            'content'     => $content,
            'image_path'  => $imagePath,
        ]);

        return ['success' => true, 'data' => ['ticket_id' => $ticketId]];
    }
}
