<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, CategoryRepository, ProductRepository, View};

Auth::requireAdmin();
$f = ['q' => input('q'), 'category' => input('category'), 'lowStock' => input('low') === '1', 'sort' => 'newest'];
View::render('admin/products', [
    'title' => 'Products', 'active' => 'products.php', 'f' => $f,
    'products' => ProductRepository::search($f, false),
    'categories' => CategoryRepository::all(),
], 'admin');
