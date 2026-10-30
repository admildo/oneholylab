<?php
declare(strict_types=1);

namespace Holy;

/** Users (customers and admins) and their addresses. */
final class UserRepository
{
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::one('SELECT * FROM users WHERE email = ?', [strtolower($email)]);
    }

    public static function create(array $d, string $role = 'customer'): int
    {
        return Database::insert(
            'INSERT INTO users (first_name, last_name, email, phone, password_hash, role, marketing_opt_in)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$d['first_name'], $d['last_name'], strtolower($d['email']), $d['phone'] ?? null,
             password_hash($d['password'], PASSWORD_DEFAULT), $role, (int) ($d['marketing'] ?? 0)]
        );
    }

    public static function updateProfile(int $id, array $d): void
    {
        Database::run('UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?',
            [$d['first_name'], $d['last_name'], $d['phone'] ?: null, $id]);
    }

    public static function customersWithTotals(string $search = ''): array
    {
        $like = '%' . $search . '%';
        return Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.created_at,
                    COUNT(o.id) AS orders, COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN o.total END), 0) AS spent
             FROM users u LEFT JOIN orders o ON o.user_id = u.id
             WHERE u.role = 'customer' AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)
             GROUP BY u.id ORDER BY u.created_at DESC",
            [$like, $like]
        );
    }

    public static function addresses(int $userId): array
    {
        return Database::all('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC', [$userId]);
    }

    public static function defaultAddress(int $userId): ?array
    {
        return Database::one('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 1', [$userId]);
    }

    /** Replace the user's single saved address (kept simple: one default address). */
    public static function saveAddress(int $userId, array $d): void
    {
        Database::run('DELETE FROM addresses WHERE user_id = ?', [$userId]);
        Database::run(
            'INSERT INTO addresses (user_id, line1, city, province, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, 1)',
            [$userId, $d['line1'], $d['city'], $d['province'], $d['postal_code'], $d['country'] ?: 'South Africa']
        );
    }
}
