<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerServiceMessageModel extends Model
{
    protected $table         = 'customer_service_messages';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'ticket_id', 'sender_type', 'sender_id',
        'content', 'image_path', 'read_at', 'created_at',
    ];

    public function getByTicket(string $ticketId, int $page = 1, int $limit = 50): array
    {
        $total = $this->where('ticket_id', $ticketId)->countAllResults(false);
        $items = $this->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'ASC')
            ->limit($limit, ($page - 1) * $limit)
            ->findAll();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Get existing ticket_id for a user, or generate a new one.
     */
    public function getOrCreateTicket(int $userId): string
    {
        $row = $this->where('sender_id', $userId)->where('sender_type', 'user')->first();
        if ($row) {
            return $row['ticket_id'];
        }
        return 'ticket_' . $userId . '_' . time();
    }

    public function insert($data = null, bool $returnID = true): bool|int|string
    {
        if (is_array($data) && empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return parent::insert($data, $returnID);
    }
}
