<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * php spark ws:start
 *
 * Swoole WebSocket server for real-time customer service chat.
 *
 * Architecture:
 *   Browser  ──WS──►  nginx /ws  ──►  this server (port WS_PORT, default 9501)
 *   php-fpm  ──HTTP POST /notify ──►  this server  ──►  push to connected clients
 */
class WsServer extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'ws:start';
    protected $description = 'Start Swoole WebSocket server for real-time customer service';

    public function run(array $params): void
    {
        $port = (int) (getenv('WS_PORT') ?: 9501);

        // -------------------------------------------------------------------
        // Shared state (single worker process, no cross-process needed)
        // fd  => ['user_id' => int, 'ticket_id' => string]
        $clients = [];
        // ticket_id => [fd => true, ...]
        $tickets = [];
        // -------------------------------------------------------------------

        $server = new Server('0.0.0.0', $port);
        $server->set([
            'worker_num'       => 1,
            'enable_coroutine' => false,
        ]);

        // ── WebSocket: new connection ──────────────────────────────────────
        $server->on('open', function (Server $server, Request $request) use (&$clients, &$tickets) {
            $token  = $request->get['token'] ?? '';
            $userId = $this->verifyJwt($token);

            if (!$userId) {
                $server->disconnect($request->fd, 1008, 'Unauthorized');
                CLI::write("[WS] REJECT fd={$request->fd} – invalid token");
                return;
            }

            try {
                $ticketId = $this->getOrCreateTicket($userId);
            } catch (\Throwable $e) {
                $server->disconnect($request->fd, 1011, 'Server error');
                CLI::error("[WS] DB error on open: " . $e->getMessage());
                return;
            }

            $clients[$request->fd] = ['user_id' => $userId, 'ticket_id' => $ticketId];
            $tickets[$ticketId][$request->fd] = true;

            CLI::write("[WS] CONNECT  fd={$request->fd}  userId={$userId}  ticket={$ticketId}");
        });

        // ── WebSocket: client message (no-op – sends go through REST) ─────
        $server->on('message', function (Server $server, Frame $frame) {
            // Reserved for future use (e.g. typing indicators)
        });

        // ── WebSocket: disconnect ──────────────────────────────────────────
        $server->on('close', function (Server $server, int $fd) use (&$clients, &$tickets) {
            if (isset($clients[$fd])) {
                $ticketId = $clients[$fd]['ticket_id'];
                unset($tickets[$ticketId][$fd]);
                if (empty($tickets[$ticketId])) {
                    unset($tickets[$ticketId]);
                }
                unset($clients[$fd]);
                CLI::write("[WS] CLOSE    fd={$fd}");
            }
        });

        // ── HTTP endpoint: POST /notify  (called by php-fpm after DB write) –
        $server->on('request', function (Request $request, Response $response) use ($server, &$clients, &$tickets) {
            $uri    = $request->server['request_uri']    ?? '/';
            $method = $request->server['request_method'] ?? 'GET';

            // ── POST /notify ───────────────────────────────────────────────
            if ($uri === '/notify' && $method === 'POST') {
                $body     = json_decode($request->rawContent(), true) ?? [];
                $ticketId = (string) ($body['ticket_id'] ?? '');
                $message  = $body['message'] ?? null;

                if ($ticketId !== '' && $message !== null) {
                    $pushed = 0;
                    $payload = json_encode(['type' => 'message', 'data' => $message]);
                    foreach (array_keys($tickets[$ticketId] ?? []) as $fd) {
                        if ($server->isEstablished($fd)) {
                            $server->push($fd, $payload);
                            $pushed++;
                        } else {
                            // stale fd – clean up
                            unset($clients[$fd], $tickets[$ticketId][$fd]);
                        }
                    }
                    CLI::write("[WS] NOTIFY   ticket={$ticketId}  pushed={$pushed}");
                }

                $response->header('Content-Type', 'application/json');
                $response->end(json_encode(['ok' => true]));
                return;
            }

            // ── GET /health ────────────────────────────────────────────────
            if ($uri === '/health') {
                $response->header('Content-Type', 'application/json');
                $response->end(json_encode([
                    'status'      => 'ok',
                    'connections' => count($clients),
                    'tickets'     => count($tickets),
                ]));
                return;
            }

            $response->status(404);
            $response->end('Not Found');
        });

        CLI::write("[WS] Starting WebSocket server on ws://0.0.0.0:{$port}");
        $server->start();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers (standalone, no CI4 framework dependency inside Swoole callbacks)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Verify HS256 JWT, return user_id on success or null.
     */
    private function verifyJwt(string $token): ?int
    {
        if (empty($token)) {
            return null;
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $body, $sig] = $parts;

        $secret   = getenv('JWT_SECRET') ?: 'default_secret_change_me_in_production';
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$header}.{$body}", $secret, true)), '+/', '-_'), '=');

        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $payload = json_decode(self::base64urlDecode($body), true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return ($payload['user_id'] ?? 0) ? (int) $payload['user_id'] : null;
    }

    /**
     * Find existing ticket for a user, or generate a deterministic new one.
     * Uses a fresh PDO connection (safe for Swoole synchronous mode).
     */
    private function getOrCreateTicket(int $userId): string
    {
        $stmt = $this->getPdo()->prepare(
            "SELECT ticket_id FROM customer_service_messages
             WHERE sender_id = ? AND sender_type = 'user'
             LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? (string) $row['ticket_id'] : 'ticket_' . $userId . '_' . time();
    }

    /**
     * Lazily create a PDO connection. Reused within the single worker process.
     */
    private function getPdo(): \PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            $host   = getenv('DB_HOSTNAME') ?: 'db';
            $dbname = getenv('DB_DATABASE') ?: '';
            $user   = getenv('DB_USERNAME') ?: '';
            $pass   = getenv('DB_PASSWORD') ?: '';
            $pdo    = new \PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }
        return $pdo;
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
