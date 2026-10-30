<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, OrderService, ReportService, StockService, View};

Auth::requireAdmin();
$days = in_array((int) input('days', '7'), [7, 30, 90], true) ? (int) input('days', '7') : 7;
View::render('admin/dashboard', [
    'title'  => 'Dashboard', 'active' => 'index.php', 'days' => $days,
    'kpis'   => ReportService::kpis($days),
    'daily'  => ReportService::dailySales($days),
    'top'    => ReportService::topProducts($days),
    'low'    => array_slice(StockService::lowStock(), 0, 6),
    'recent' => array_slice(OrderService::all(), 0, 6),
], 'admin');
