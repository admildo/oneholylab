<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, UserRepository, View};

Auth::requireAdmin();
View::render('admin/customers', ['title' => 'Customers', 'active' => 'customers.php', 'q' => input('q'), 'customers' => UserRepository::customersWithTotals(input('q'))], 'admin');
