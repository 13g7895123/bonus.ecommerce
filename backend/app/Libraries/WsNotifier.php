<?php

namespace App\Libraries;

/**
 * WsNotifier
 *
 * Called from php-fpm after writing a message to the database.
 * Sends an internal HTTP POST to the Swoole WebSocket server, which then
 * pushes the message to all connected WebSocket clients for that ticket.
 *
 * Fire-and-forget: a 1-second timeout is used so that if the WS server is
 * unavailable (e.g. restarting) the REST response is not blocked.
 */
class WsNotifier
{
    public static function notify(string $ticketId, array $message): void
    {
        $host    = getenv('WS_INTERNAL_HOST') ?: 'backend-ws';
        $port    = getenv('WS_PORT')          ?: '9501';
        $url     = "http://{$host}:{$port}/notify";
        $payload = json_encode(['ticket_id' => $ticketId, 'message' => $message]);

        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
                'content'       => $payload,
                'timeout'       => 1,
                'ignore_errors' => true,
            ],
        ]);

        @file_get_contents($url, false, $context);
    }
}
