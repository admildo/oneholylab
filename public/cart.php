<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{CartService, Csrf, View};

if (is_post()) {
    Csrf::check();
    $variant = (int) input('variant_id');
    $error = match (input('action')) {
        'add' => $variant ? CartService::add($variant) : 'Please choose a size.',
        'set' => CartService::setQuantity($variant, (int) input('quantity')),
        default => null,
    };
    if ($error) {
        flash($error);
        redirect(input('action') === 'add' ? ($_SERVER['HTTP_REFERER'] ?? 'shop.php') : 'cart.php');
    }
    if (input('action') === 'add') {
        flash('Added to your bag.');
    }
    redirect('cart.php');
}
View::render('shop/cart', ['title' => 'Shopping bag | oneholylab', 'cart' => CartService::summary()]);
