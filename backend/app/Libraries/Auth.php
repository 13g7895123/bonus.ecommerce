<?php

namespace App\Libraries;

/**
 * Static holder for the currently authenticated user.
 * Populated by JwtFilter before each request.
 */
class Auth
{
    private static ?array $user = null;

    public static function setUser(array $user): void
    {
        self::$user = $user;
    }

    public static function getUser(): ?array
    {
        return self::$user;
    }

    public static function id(): ?int
    {
        return self::$user['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::$user['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return (self::$user['role'] ?? '') === 'admin';
    }
}
