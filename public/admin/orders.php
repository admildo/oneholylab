<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, Csrf, OrderService, View};

Auth::requireAdmin();
$id = (int) input('id');

if (is_post()) {
    Csrf::check();
    $error = OrderService::updateStatus((int) input('order_id'), input('status'), input('tracking_no'));
    flash($error ?? 'Order updated and the customer was notified.');
    redirect('admin/orders.php?id=' . (int) input('order_id'));
}

$status = in_array(input('status'), OrderService::STATUSES, true) ? input('status') : '';
$orders = OrderService::all($status);
$selected = OrderService::find($id ?: (int) ($orders[0]['id'] ?? 0));
View::render('admin/orders', [
    'title' => 'Orders', 'active' => 'orders.php', 'orders' => $orders, 'status' => $status,
    'selected' => $selected, 'items' => $selected ? OrderService::items((int) $selected['id']) : [],
], 'admin');
