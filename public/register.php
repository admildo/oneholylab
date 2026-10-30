<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{Auth, Csrf, UserRepository, View};

$errors = [];
if (is_post()) {
    Csrf::check();
    $d = [
        'first_name' => input('first_name'), 'last_name' => input('last_name'),
        'email' => input('email'), 'password' => $_POST['password'] ?? '', 'marketing' => input('marketing'),
    ];
    if ($d['first_name'] === '' || $d['last_name'] === '') {
        $errors[] = 'Please enter your first and last name.';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (UserRepository::findByEmail($d['email'])) {
        $errors[] = 'An account with that email already exists. Try logging in.';
    }
    if (strlen($d['password']) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }
    if (!$errors) {
        Auth::login(UserRepository::create($d));
        flash('Welcome to oneholylab.');
        $to = $_SESSION['return_to'] ?? 'account.php';
        unset($_SESSION['return_to']);
        redirect($to);
    }
}
View::render('shop/auth', ['title' => 'Create account | oneholylab', 'tab' => 'register', 'errors' => $errors, 'old' => $_POST]);
