<?php
declare(strict_types=1);

namespace Holy;

/** The shopping cart. Guests get a cart tied to their session, customers a cart tied to their account. */
final class CartService
{
    private static function currentId(bool $create): ?int
    {
        $user = Auth::user();
        if ($user) {
            $id = Database::value('SELECT id FROM carts WHERE user_id = ?', [$user['id']]);
            if (!$id && $create) {
                $id = Database::insert('INSERT INTO carts (user_id) VALUES (?)', [$user['id']]);
            }
            return $id ? (int) $id : null;
        }
        $token = $_SESSION['cart_token'] ?? null;
        $id = $token ? Database::value('SELECT id FROM carts WHERE session_token = ?', [$token]) : null;
        if (!$id && $create) {
            $token = $_SESSION['cart_token'] = bin2hex(random_bytes(32));
            $id = Database::insert('INSERT INTO carts (session_token) VALUES (?)', [$token]);
        }
        return $id ? (int) $id : null;
    }

    /** Add one variant. Returns an error message or null. */
    public static function add(int $variantId, int $qty = 1): ?string
    {
        $v = ProductRepository::variant($variantId);
        if (!$v || $v['status'] !== 'published') {
            return 'That product is not available.';
        }
        $cartId = self::currentId(true);
        $have = (int) Database::value('SELECT quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?', [$cartId, $variantId]);
        $want = min($have + max(1, $qty), 10);
        if ($want > (int) $v['stock']) {
            return $v['stock'] > 0 ? "Only {$v['stock']} left in size {$v['size']}." : 'Sorry, that size is sold out.';
        }
        Database::run(
            'INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)',
            [$cartId, $variantId, $want]
        );
        return null;
    }

    public static function setQuantity(int $variantId, int $qty): ?string
    {
        $cartId = self::currentId(false);
        if (!$cartId) {
            return null;
        }
        if ($qty <= 0) {
            Database::run('DELETE FROM cart_items WHERE cart_id = ? AND variant_id = ?', [$cartId, $variantId]);
            return null;
        }
        $stock = (int) Database::value('SELECT stock FROM product_variants WHERE id = ?', [$variantId]);
        if ($qty > $stock) {
            return "Only $stock available.";
        }
        Database::run('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND variant_id = ?', [min($qty, 10), $cartId, $variantId]);
        return null;
    }

    public static function clear(): void
    {
        if ($id = self::currentId(false)) {
            Database::run('DELETE FROM cart_items WHERE cart_id = ?', [$id]);
        }
    }

    /** Lines with product details, plus totals. */
    public static function summary(?string $deliveryMethod = null): array
    {
        $id = self::currentId(false);
        $lines = $id ? Database::all(
            'SELECT ci.variant_id, ci.quantity, v.size, v.stock, p.name, p.slug, p.price, p.status,
                    (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order, id LIMIT 1) AS image
             FROM cart_items ci
             JOIN product_variants v ON v.id = ci.variant_id
             JOIN products p ON p.id = v.product_id
             WHERE ci.cart_id = ? ORDER BY ci.id',
            [$id]
        ) : [];
        $subtotal = 0.0;
        $count = 0;
        foreach ($lines as &$l) {
            $l['line_total'] = $l['price'] * $l['quantity'];
            $subtotal += $l['line_total'];
            $count += $l['quantity'];
        }
        unset($l);
        $delivery = self::deliveryFee($subtotal, $deliveryMethod ?? 'standard');
        return ['lines' => $lines, 'count' => $count, 'subtotal' => $subtotal, 'delivery' => $delivery, 'total' => $subtotal + $delivery];
    }

    public static function deliveryFee(float $subtotal, string $method): float
    {
        $m = config('delivery_methods')[$method] ?? null;
        if ($subtotal <= 0 || !$m) {
            return 0.0;
        }
        if ($method === 'standard' && $subtotal >= config('free_delivery_over')) {
            return 0.0;
        }
        return (float) $m['fee'];
    }

    public static function count(): int
    {
        $id = self::currentId(false);
        return $id ? (int) Database::value('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE cart_id = ?', [$id]) : 0;
    }

    /** Called at login: move what the guest put in the bag into the customer's own cart. */
    public static function mergeGuestCartInto(int $userId): void
    {
        $token = $_SESSION['cart_token'] ?? null;
        if (!$token) {
            return;
        }
        $guest = Database::value('SELECT id FROM carts WHERE session_token = ?', [$token]);
        if ($guest) {
            $mine = Database::value('SELECT id FROM carts WHERE user_id = ?', [$userId])
                ?: Database::insert('INSERT INTO carts (user_id) VALUES (?)', [$userId]);
            // One row at a time: MariaDB 10.4 rejects copying a table into itself with ON DUPLICATE KEY.
            foreach (Database::all('SELECT variant_id, quantity FROM cart_items WHERE cart_id = ?', [$guest]) as $item) {
                Database::run(
                    'INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), 10)',
                    [$mine, $item['variant_id'], $item['quantity']]
                );
            }
            Database::run('DELETE FROM carts WHERE id = ?', [$guest]);
        }
        unset($_SESSION['cart_token']);
    }
}
