<?php

namespace App\Repositories\Interfaces;

interface CustomerServiceMessageRepositoryInterface
{
    public function getByTicket(string $ticketId, int $page, int $limit): array;
    public function getOrCreateTicket(int $userId): string;
    public function create(array $data): int;
    public function createBatch(array $rows): bool;
}
