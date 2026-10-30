<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, AuditLog, StockService, View};

Auth::requireAdmin();
View::render('admin/activity', ['title' => 'History', 'active' => 'activity.php', 'log' => AuditLog::recent(), 'moves' => StockService::recentMovements()], 'admin');
