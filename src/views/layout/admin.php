<?php
use Holy\{Auth, Csrf};

/** @var string $active the page file name, e.g. 'orders.php', to highlight in the menu */
$nav = [
    'index.php' => 'Dashboard',
    'products.php' => 'Products',
    'orders.php' => 'Orders',
    'customers.php' => 'Customers',
    'categories.php' => 'Categories',
    'activity.php' => 'History',
];
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= e($title) ?> | oneholylab admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
  <script src="<?= asset('assets/js/app.js') ?>" defer></script>
</head>
<body class="bg-neutral-50 font-sans text-[13px] leading-normal text-black antialiased">
  <div class="flex min-h-screen flex-wrap">

    <aside class="flex flex-[1_1_100%] flex-wrap items-center gap-x-4 gap-y-1 border-b border-line bg-white px-4 py-3 lg:flex-[0_0_240px] lg:flex-col lg:items-stretch lg:gap-6 lg:border-b-0 lg:border-r lg:px-5 lg:py-6">
      <a href="<?= url('admin/') ?>" class="mr-auto font-logo text-2xl font-medium leading-[.9] lg:mr-0">
        ONEHOLYLAB <span class="cap font-sans text-muted">Admin</span>
      </a>

      <nav class="cap flex w-full flex-wrap gap-x-4 lg:flex-col" aria-label="Admin">
        <?php foreach ($nav as $file => $label): ?>
          <a class="flex min-h-[44px] items-center <?= ($active ?? '') === $file ? 'font-semibold' : '' ?>" href="<?= url('admin/' . $file) ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <a class="flex min-h-[44px] items-center text-muted" href="<?= url() ?>" target="_blank" rel="noopener">View shop</a>
      </nav>

      <form method="post" action="<?= url('admin/logout.php') ?>">
        <?= Csrf::field() ?>
        <button class="cap flex min-h-[44px] items-center text-muted">Log out (<?= e(Auth::user()['first_name'] ?? '') ?>)</button>
      </form>
    </aside>

    <main class="flex min-w-0 flex-[999_1_560px] flex-col gap-6 px-4 py-6 md:px-6 md:py-8">
      <?php if (!empty($flash)): ?>
        <div class="border border-black bg-white px-4 py-3" role="status"><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <?= $content ?>
    </main>
  </div>
</body>
</html>
