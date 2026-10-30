<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, Csrf};

$error = null;
if (Auth::isAdmin()) {
    redirect('admin/');
}
if (is_post()) {
    Csrf::check();
    if (Auth::attempt(input('email'), $_POST['password'] ?? '', 'admin')) {
        redirect('admin/');
    }
    $error = 'Email or password is not correct.';
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>Admin log in | oneholylab</title>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>"></head>
<body class="flex min-h-screen items-center justify-center bg-neutral-50 px-4 font-sans text-[13px]">
<form method="post" class="flex w-full max-w-sm flex-col gap-6 border border-line bg-white p-8">
  <?= Csrf::field() ?>
  <span class="font-logo text-3xl font-medium leading-[.9]">ONEHOLYLAB <span class="cap font-sans text-muted">Admin</span></span>
  <?php if ($error): ?><div class="border border-black p-3" role="alert"><?= e($error) ?></div><?php endif; ?>
  <div class="field"><label for="e">Email</label><input id="e" name="email" type="email" class="input" required autofocus></div>
  <div class="field"><label for="p">Password</label><input id="p" name="password" type="password" class="input" required></div>
  <button class="btn" type="submit">Log in</button>
</form></body></html>
