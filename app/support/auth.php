<?php

declare(strict_types=1);

class Auth
{
    public static function id(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function login(int $usuarioId): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuarioId;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }
}