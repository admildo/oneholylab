<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{Auth, OrderService, View};

$user = Auth::requireLogin();
$order = OrderService::byNumber(input('no'), (int) $user['id']); // customers only see their own orders
if (!$order) {
    http_response_code(404);
    View::render('shop/not_found', ['title' => 'Not found | oneholylab']);
    exit;
}
View::render('shop/order', ['title' => 'Order ' . $order['order_no'] . ' | oneholylab', 'order' => $order, 'items' => OrderService::items((int) $order['id'])]);
