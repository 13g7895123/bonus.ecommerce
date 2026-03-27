<?php

namespace App\Filters;

use App\Libraries\Auth;
use App\Repositories\ApiLogRepository;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiLogFilter implements FilterInterface
{
    private float $startTime;

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $this->startTime = microtime(true);
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        // Skip logging for admin panel and sadmin to avoid infinite loops
        $uri = (string) $request->getUri()->getPath();
        if (str_starts_with($uri, '/admin-panel') || str_starts_with($uri, '/sadmin')) {
            return null;
        }

        $durationMs = (int) round((microtime(true) - $this->startTime) * 1000);

        // Sanitize request headers — remove Authorization value for security
        $headers = [];
        foreach ($request->headers() as $name => $header) {
            $value = $header->getValue();
            if (strtolower($name) === 'authorization') {
                $value = '[REDACTED]';
            }
            $headers[$name] = $value;
        }

        // Capture request body (JSON or form)
        $body = $request->getBody();
        // Mask sensitive fields
        $bodyDecoded = json_decode($body, true);
        if (is_array($bodyDecoded)) {
            foreach (['password', 'password_confirmation', 'withdrawal_password', 'old_password'] as $key) {
                if (array_key_exists($key, $bodyDecoded)) {
                    $bodyDecoded[$key] = '[REDACTED]';
                }
            }
            $body = json_encode($bodyDecoded, JSON_UNESCAPED_UNICODE);
        }

        $responseBody = $response->getBody();
        // Truncate very large response bodies
        if (strlen((string) $responseBody) > 65535) {
            $responseBody = substr((string) $responseBody, 0, 65535) . '...[TRUNCATED]';
        }

        try {
            (new ApiLogRepository())->create([
                'method'          => $request->getMethod(),
                'uri'             => $uri,
                'ip_address'      => $request->getIPAddress(),
                'user_id'         => Auth::id(),
                'request_headers' => json_encode($headers, JSON_UNESCAPED_UNICODE),
                'request_body'    => $body,
                'response_code'   => $response->getStatusCode(),
                'response_body'   => $responseBody,
                'duration_ms'     => $durationMs,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Never let logging break the response
        }

        return null;
    }
}
