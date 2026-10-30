<?php
declare(strict_types=1);

namespace Holy;

/** Login state, registration and role checks. */
final class Auth
{
    public static function user(): ?array
    {
        static $cache = [];
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }
        return $cache[$id] ??= UserRepository::find((int) $id);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    /** Returns the user on success, or null on a wrong email or password. */
    public static function attempt(string $email, string $password, ?string $requireRole = null): ?array
    {
        $user = UserRepository::findByEmail($email);
        // Always verify against a hash so timing does not reveal which emails exist.
        $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsaltandhashtimingXXXXXXXXXXXXXXXXXXXXXX';
        if (!password_verify($password, $hash) || !$user) {
            return null;
        }
        if ($requireRole !== null && $user['role'] !== $requireRole) {
            return null;
        }
        self::login((int) $user['id']);
        return $user;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        CartService::mergeGuestCartInto($userId);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /** Customer pages: send guests to the login page and come back afterwards. */
    public static function requireLogin(): array
    {
        if (!self::check()) {
            // redirect() adds the site folder itself, so store the path without it.
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $base = config('base_url');
            $_SESSION['return_to'] = $base !== '' && str_starts_with($uri, $base . '/') ? substr($uri, strlen($base)) : $uri;
            redirect('login.php');
        }
        return self::user();
    }

    /** Admin pages: only admins get through. */
    public static function requireAdmin(): array
    {
        if (!self::check() || !self::isAdmin()) {
            redirect('admin/login.php');
        }
        return self::user();
    }
}
