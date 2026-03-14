<?php

namespace App\Libraries;

/**
 * Lightweight JWT helper using HMAC-SHA256.
 * No external dependencies required.
 */
class JwtHelper
{
    private static function secret(): string
    {
        $secret = getenv('JWT_SECRET');
        return $secret !== false ? $secret : 'default_secret_change_me_in_production';
    }

    private static function ttl(): int
    {
        $ttl = getenv('JWT_EXPIRE');
        return $ttl !== false ? (int) $ttl : 86400;
    }

    public static function generate(array $payload): string
    {
        $header  = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + self::ttl();
        $body    = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac('sha256', "$header.$body", self::secret(), true));
        return "$header.$body.$sig";
    }

    /** @return array|null Returns the decoded payload, or null if invalid/expired. */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $body, $sig] = $parts;
        $expected = self::base64url(hash_hmac('sha256', "$header.$body", self::secret(), true));
        if (!hash_equals($expected, $sig)) {
            return null;
        }
        $payload = json_decode(self::base64urlDecode($body), true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) {
            return null;
        }
        return $payload;
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
