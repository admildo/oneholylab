<?php
declare(strict_types=1);

namespace Holy;

/** Numbers for the admin dashboard. Cancelled orders never count as sales. */
final class ReportService
{
    public static function kpis(int $days): array
    {
        $since = date('Y-m-d 00:00:00', strtotime("-" . ($days - 1) . " days"));
        $prev = date('Y-m-d 00:00:00', strtotime("-" . (2 * $days - 1) . " days"));
        $sales = (float) Database::value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled' AND created_at >= ?", [$since]);
        $before = (float) Database::value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled' AND created_at >= ? AND created_at < ?", [$prev, $since]);
        return [
            'sales'      => $sales,
            'change'     => $before > 0 ? round(($sales - $before) / $before * 100) : null,
            'orders'     => (int) Database::value("SELECT COUNT(*) FROM orders WHERE status <> 'cancelled' AND created_at >= ?", [$since]),
            'to_fulfil'  => (int) Database::value("SELECT COUNT(*) FROM orders WHERE status IN ('paid','processing')"),
            'customers'  => (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= ?", [$since]),
            'low_stock'  => count(StockService::lowStock()),
        ];
    }

    /** Sales per day for the last $days days, oldest first, with zero-filled gaps. */
    public static function dailySales(int $days): array
    {
        $since = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));
        $rows = Database::all(
            "SELECT DATE(created_at) AS d, SUM(total) AS t FROM orders
             WHERE status <> 'cancelled' AND created_at >= ? GROUP BY DATE(created_at)", [$since]
        );
        $by = array_column($rows, 't', 'd');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $out[] = ['date' => $d, 'label' => date('D', strtotime($d)), 'total' => (float) ($by[$d] ?? 0)];
        }
        return $out;
    }

    public static function topProducts(int $days, int $limit = 5): array
    {
        $since = date('Y-m-d 00:00:00', strtotime("-" . ($days - 1) . " days"));
        return Database::all(
            "SELECT oi.product_name AS name, SUM(oi.quantity) AS units,
                    (SELECT pi.path FROM product_variants v JOIN product_images pi ON pi.product_id = v.product_id
                     WHERE v.id = MAX(oi.variant_id) ORDER BY pi.sort_order LIMIT 1) AS image
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE o.status <> 'cancelled' AND o.created_at >= ?
             GROUP BY oi.product_name ORDER BY units DESC LIMIT " . (int) $limit,
            [$since]
        );
    }
}
