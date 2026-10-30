<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{CategoryRepository, ProductRepository, View};

$f = [
    'q'        => input('q'),
    'category' => input('category'),
    'size'     => input('size'),
    'sort'     => input('sort', 'newest'),
];
View::render('shop/list', [
    'title'      => 'Shop | oneholylab',
    'f'          => $f,
    'products'   => ProductRepository::search($f),
    'categories' => CategoryRepository::all(),
]);
