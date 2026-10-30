<?php
declare(strict_types=1);

namespace Holy;

/** The only place stock numbers change. Every change is written to stock_movements. */
final class StockService
{
    /** Change a variant's stock by $delta (negative to remove). Never goes below zero. */
    public static function change(int $variantId, int $delta, string $reason, ?int $userId = null): bool
    {
        $rows = Database::run(
            'UPDATE product_variants SET stock = stock + ? WHERE id = ? AND stock + ? >= 0',
            [$delta, $variantId, $delta]
        );
        if ($rows === 0) {
            return false;
        }
        Database::run(
            'INSERT INTO stock_movements (variant_id, user_id, change_qty, reason) VALUES (?, ?, ?, ?)',
            [$variantId, $userId, $delta, $reason]
        );
        return true;
    }

    /** Set stock to an exact number (admin adjustment). */
    public static function set(int $variantId, int $newStock, int $userId): void
    {
        $current = (int) Database::value('SELECT stock FROM product_variants WHERE id = ?', [$variantId]);
        $delta = $newStock - $current;
        if ($delta !== 0) {
            self::change($variantId, $delta, $delta > 0 ? 'restock' : 'adjustment', $userId);
        }
    }

    public static function lowStock(): array
    {
        return Database::all(
            'SELECT v.id, v.size, v.stock, p.id AS product_id, p.name
             FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE p.status = "published" AND v.stock <= ? ORDER BY v.stock, p.name',
            [config('low_stock_threshold')]
        );
    }

    public static function recentMovements(int $limit = 30): array
    {
        return Database::all(
            'SELECT m.*, v.size, p.name AS product_name, CONCAT(u.first_name, " ", u.last_name) AS user_name
             FROM stock_movements m
             JOIN product_variants v ON v.id = m.variant_id
             JOIN products p ON p.id = v.product_id
             LEFT JOIN users u ON u.id = m.user_id
             ORDER BY m.id DESC LIMIT ' . (int) $limit
        );
    }
}
