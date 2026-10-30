<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{ProductRepository, View};

$product = ProductRepository::findBySlug(input('slug'));
if (!$product) {
    http_response_code(404);
    View::render('shop/not_found', ['title' => 'Not found | oneholylab']);
    exit;
}
View::render('shop/product', [
    'title'    => $product['name'] . ' | oneholylab',
    'product'  => $product,
    'variants' => ProductRepository::variants((int) $product['id']),
    'images'   => ProductRepository::images((int) $product['id']),
    'related'  => ProductRepository::related((int) $product['id'], (int) $product['category_id']),
]);
