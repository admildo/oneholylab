<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{Auth, CartService, Csrf, OrderService, UserRepository, View};

$user = Auth::requireLogin();
$method = input('method', 'standard');
if (!isset(config('delivery_methods')[$method])) {
    $method = 'standard';
}
$cart = CartService::summary($method);
if (!$cart['lines']) {
    redirect('cart.php');
}

$saved = UserRepository::defaultAddress((int) $user['id']) ?? [];
$v = [
    'first_name'  => input('first_name', $user['first_name']),
    'last_name'   => input('last_name', $user['last_name']),
    'line1'       => input('line1', $saved['line1'] ?? ''),
    'city'        => input('city', $saved['city'] ?? ''),
    'province'    => input('province', $saved['province'] ?? ''),
    'postal_code' => input('postal_code', $saved['postal_code'] ?? ''),
    'country'     => input('country', $saved['country'] ?? 'South Africa'),
];
$errors = [];

if (is_post()) {
    Csrf::check();
    if (input('place') !== '') {
        foreach (['first_name' => 'first name', 'last_name' => 'last name', 'line1' => 'street address', 'city' => 'city', 'province' => 'province', 'postal_code' => 'postal code', 'country' => 'country'] as $k => $label) {
            if ($v[$k] === '') {
                $errors[] = "Please enter your $label.";
            }
        }
        if (!$errors) {
            $address = $v + ['name' => $v['first_name'] . ' ' . $v['last_name']];
            $result = OrderService::place($user, $address, $method, [
                'number' => input('card_number'), 'expiry' => input('card_expiry'), 'cvc' => input('card_cvc'),
            ]);
            if ($result['ok']) {
                if (input('save_address') === '1') {
                    UserRepository::saveAddress((int) $user['id'], $v);
                }
                redirect('order.php?no=' . urlencode($result['order_no']));
            }
            $errors[] = $result['message'];
        }
    }
}
View::render('shop/checkout', ['title' => 'Checkout | oneholylab', 'cart' => $cart, 'v' => $v, 'errors' => $errors, 'method' => $method]);
