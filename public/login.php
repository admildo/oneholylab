<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{Auth, Csrf, View};

$errors = [];
if (is_post()) {
    Csrf::check();
    if (Auth::attempt(input('email'), $_POST['password'] ?? '', 'customer')) {
        $to = $_SESSION['return_to'] ?? null;
        unset($_SESSION['return_to']);
        redirect($to && str_starts_with($to, '/') && !str_starts_with($to, '//') ? $to : 'account.php');
    }
    $errors[] = 'Email or password is not correct.';
}
View::render('shop/auth', ['title' => 'Log in | oneholylab', 'tab' => 'login', 'errors' => $errors, 'old' => $_POST]);
