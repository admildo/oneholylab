<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{Auth, Csrf, OrderService, UserRepository, View};

$user = Auth::requireLogin();
$tab = in_array(input('tab'), ['orders', 'details', 'address'], true) ? input('tab') : 'orders';
$errors = [];

if (is_post()) {
    Csrf::check();
    if (input('form') === 'details') {
        if (input('first_name') === '' || input('last_name') === '') {
            $errors[] = 'Please enter your first and last name.';
        } else {
            UserRepository::updateProfile((int) $user['id'], ['first_name' => input('first_name'), 'last_name' => input('last_name'), 'phone' => input('phone')]);
            flash('Your details were saved.');
            redirect('account.php?tab=details');
        }
    } elseif (input('form') === 'address') {
        $a = ['line1' => input('line1'), 'city' => input('city'), 'province' => input('province'), 'postal_code' => input('postal_code'), 'country' => input('country')];
        if (in_array('', $a, true)) {
            $errors[] = 'Please fill in every address field.';
        } else {
            UserRepository::saveAddress((int) $user['id'], $a);
            flash('Your address was saved.');
            redirect('account.php?tab=address');
        }
    }
}
View::render('shop/account', [
    'title' => 'My account | oneholylab', 'user' => Auth::user(), 'tab' => $tab, 'errors' => $errors,
    'orders' => OrderService::forUser((int) $user['id']), 'address' => UserRepository::defaultAddress((int) $user['id']),
]);
