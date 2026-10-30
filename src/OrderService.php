<?php
declare(strict_types=1);

namespace Holy;

use Throwable;

/** Turns a cart into an order, and moves orders through their statuses. */
final class OrderService
{
    public const STATUSES = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];

    public static function gateway(): PaymentGateway
    {
        return match (config('payment_provider')) {
            default => new DemoGateway(), // add real providers here
        };
    }

    /**
     * Place an order from the user's cart. Everything happens in one transaction:
     * if stock runs out or the payment fails, nothing is saved.
     * @return array{ok: bool, message: string, order_no?: string}
     */
    public static function place(array $user, array $address, string $method, array $card): array
    {
        $cart = CartService::summary($method);
        if (!$cart['lines']) {
            return ['ok' => false, 'message' => 'Your bag is empty.'];
        }
        // A product can be archived or set to draft after it went into the bag.
        foreach ($cart['lines'] as $l) {
            if ($l['status'] !== 'published') {
                return ['ok' => false, 'message' => "Sorry, {$l['name']} is no longer for sale. Remove it from your bag and try again."];
            }
        }
        Database::begin();
        try {
            // Lock the variants so two buyers cannot take the last item together.
            foreach ($cart['lines'] as $l) {
                $stock = (int) Database::value('SELECT stock FROM product_variants WHERE id = ? FOR UPDATE', [$l['variant_id']]);
                if ($stock < $l['quantity']) {
                    Database::rollback();
                    return ['ok' => false, 'message' => "Sorry, {$l['name']} (size {$l['size']}) is no longer available in that quantity. Update your bag and try again."];
                }
            }
            $orderNo = 'OHL-' . (10000 + (int) Database::value('SELECT COALESCE(MAX(id), 0) + 1 FROM orders'));
            $orderId = Database::insert(
                'INSERT INTO orders (order_no, user_id, status, subtotal, delivery_fee, total, delivery_method,
                                     ship_name, ship_line1, ship_city, ship_province, ship_postal, ship_country)
                 VALUES (?, ?, "pending", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$orderNo, $user['id'], $cart['subtotal'], $cart['delivery'], $cart['total'], $method,
                 $address['name'], $address['line1'], $address['city'], $address['province'], $address['postal_code'], $address['country']]
            );
            foreach ($cart['lines'] as $l) {
                Database::run(
                    'INSERT INTO order_items (order_id, variant_id, product_name, size, unit_price, quantity) VALUES (?, ?, ?, ?, ?, ?)',
                    [$orderId, $l['variant_id'], $l['name'], $l['size'], $l['price'], $l['quantity']]
                );
                StockService::change((int) $l['variant_id'], -(int) $l['quantity'], 'order', (int) $user['id']);
            }
            $result = self::gateway()->charge((float) $cart['total'], $orderNo, $card);
            if (!$result['ok']) {
                Database::rollback();
                return ['ok' => false, 'message' => $result['message']];
            }
            Database::run(
                'INSERT INTO payments (order_id, provider, provider_ref, amount, status) VALUES (?, ?, ?, ?, "succeeded")',
                [$orderId, config('payment_provider'), $result['reference'], $cart['total']]
            );
            Database::run('UPDATE orders SET status = "paid" WHERE id = ?', [$orderId]);
            CartService::clear();
            Database::commit();
        } catch (Throwable $e) {
            if (Database::inTransaction()) {
                Database::rollback();
            }
            error_log('Order failed: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Something went wrong while placing your order. You have not been charged.'];
        }
        Mailer::orderConfirmation($user, self::byNumber($orderNo, (int) $user['id']));
        return ['ok' => true, 'message' => 'Order placed', 'order_no' => $orderNo];
    }

    public static function byNumber(string $orderNo, ?int $userId = null): ?array
    {
        return Database::one(
            'SELECT * FROM orders WHERE order_no = ?' . ($userId ? ' AND user_id = ?' : ''),
            $userId ? [$orderNo, $userId] : [$orderNo]
        );
    }

    public static function items(int $orderId): array
    {
        return Database::all('SELECT * FROM order_items WHERE order_id = ?', [$orderId]);
    }

    public static function forUser(int $userId): array
    {
        return Database::all(
            'SELECT o.*, (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count
             FROM orders o WHERE o.user_id = ? ORDER BY o.id DESC',
            [$userId]
        );
    }

    /** Admin list. $status '' means all. */
    public static function all(string $status = ''): array
    {
        return Database::all(
            'SELECT o.*, CONCAT(u.first_name, " ", u.last_name) AS customer, u.email,
                    (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count
             FROM orders o JOIN users u ON u.id = o.user_id
             ' . ($status !== '' ? 'WHERE o.status = ?' : '') . ' ORDER BY o.id DESC',
            $status !== '' ? [$status] : []
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT o.*, CONCAT(u.first_name, " ", u.last_name) AS customer, u.email, u.phone
             FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?',
            [$id]
        );
    }

    /** Admin changes status and tracking. Cancelling puts the stock back. */
    public static function updateStatus(int $orderId, string $status, ?string $tracking): ?string
    {
        if (!in_array($status, self::STATUSES, true)) {
            return 'Unknown status.';
        }
        $order = self::find($orderId);
        if (!$order) {
            return 'Order not found.';
        }
        if ($order['status'] === 'cancelled' && $status !== 'cancelled') {
            return 'A cancelled order cannot be reopened.';
        }
        Database::begin();
        try {
            if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
                foreach (self::items($orderId) as $i) {
                    if ($i['variant_id']) {
                        StockService::change((int) $i['variant_id'], (int) $i['quantity'], 'cancellation', Auth::user()['id'] ?? null);
                    }
                }
            }
            Database::run('UPDATE orders SET status = ?, tracking_no = ? WHERE id = ?', [$status, $tracking ?: null, $orderId]);
            AuditLog::record('status', 'order', $orderId, "{$order['order_no']}: {$order['status']} to $status");
            Database::commit();
        } catch (Throwable $e) {
            Database::rollback();
            error_log($e->getMessage());
            return 'Could not update the order.';
        }
        if ($status !== $order['status'] || ($tracking && $tracking !== $order['tracking_no'])) {
            Mailer::statusChanged(UserRepository::find((int) $order['user_id']), self::find($orderId));
        }
        return null;
    }
}
