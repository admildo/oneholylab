<?php
use Holy\{Auth, CartService, CategoryRepository, Csrf};

$cartCount = CartService::count();
$categories = CategoryRepository::all();
$user = Auth::user();
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <meta name="description" content="oneholylab: faith-inspired streetwear for Gen Z.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
  <script src="<?= asset('assets/js/app.js') ?>" defer></script>
</head>
<body class="bg-white font-sans text-[14px] leading-normal text-black antialiased">
  <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

  <div class="cap bg-black px-4 py-2.5 text-center text-white">
    Free delivery on orders over <?= money(config('free_delivery_over')) ?>
    <span class="hidden sm:inline"><span class="mx-2 text-neutral-500">/</span> 14-day returns</span>
  </div>

  <header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="mx-auto grid h-16 max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center gap-4 px-4 md:h-[72px] md:px-6">

      <div class="flex items-center gap-2">
        <button type="button" class="-ml-2 inline-flex min-h-[44px] min-w-[44px] items-center justify-center lg:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="Menu">
          <svg width="22" height="22" viewBox="0 0 22 22" aria-hidden="true"><path d="M3 7h16M3 15h16" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
        </button>
        <nav class="cap hidden items-center gap-7 lg:flex" aria-label="Main">
          <a href="<?= url('shop.php') ?>" class="nav-link">New in</a>
          <?php foreach ($categories as $category): ?>
            <a href="<?= url('shop.php?category=' . rawurlencode($category['slug'])) ?>" class="nav-link"><?= e($category['name']) ?></a>
          <?php endforeach; ?>
        </nav>
      </div>

      <a href="<?= url() ?>" class="font-logo text-[24px] font-medium leading-none tracking-tight md:text-[30px]">ONEHOLYLAB</a>

      <div class="cap flex items-center justify-end gap-1 md:gap-5">
        <a class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-2" href="<?= url('shop.php#search') ?>" aria-label="Search">
          <svg width="19" height="19" viewBox="0 0 20 20" aria-hidden="true"><circle cx="8.5" cy="8.5" r="6" stroke="currentColor" stroke-width="1.3" fill="none"/><path d="M13 13l5 5" stroke="currentColor" stroke-width="1.3"/></svg>
          <span class="hidden xl:inline">Search</span>
        </a>
        <?php if ($user): ?>
          <a class="nav-link hidden md:inline-flex" href="<?= url('account.php') ?>">Account</a>
        <?php else: ?>
          <a class="nav-link hidden md:inline-flex" href="<?= url('login.php') ?>">Log in</a>
        <?php endif; ?>
        <a class="relative inline-flex min-h-[44px] min-w-[44px] items-center justify-center" href="<?= url('cart.php') ?>" aria-label="Shopping bag, <?= $cartCount ?> items">
          <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="M4 6h12l-1 12H5L4 6z M7 6V5a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
          <?php if ($cartCount > 0): ?>
            <span class="absolute right-0.5 top-1 inline-flex h-[17px] min-w-[17px] items-center justify-center rounded-full bg-black px-1 text-[10px] font-medium leading-none text-white"><?= $cartCount ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <nav id="mobile-nav" class="cap hidden flex-col border-t border-line px-4 py-2 lg:!hidden" aria-label="Mobile">
      <a class="flex min-h-[48px] items-center border-b border-neutral-100" href="<?= url('shop.php') ?>">New in</a>
      <?php foreach ($categories as $category): ?>
        <a class="flex min-h-[48px] items-center border-b border-neutral-100" href="<?= url('shop.php?category=' . rawurlencode($category['slug'])) ?>"><?= e($category['name']) ?></a>
      <?php endforeach; ?>
      <?php if ($user): ?>
        <a class="flex min-h-[48px] items-center border-b border-neutral-100" href="<?= url('account.php') ?>">My account</a>
        <form method="post" action="<?= url('logout.php') ?>">
          <?= Csrf::field() ?>
          <button class="cap flex min-h-[48px] items-center">Log out</button>
        </form>
      <?php else: ?>
        <a class="flex min-h-[48px] items-center" href="<?= url('login.php') ?>">Log in or register</a>
      <?php endif; ?>
    </nav>
  </header>

  <?php if (!empty($flash)): ?>
    <div class="mx-4 mt-4 flex items-center gap-3 border border-black bg-soft px-4 py-3 md:mx-6" role="status">
      <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <main id="main"><?= $content ?></main>

  <footer class="mt-20 bg-ink text-neutral-300">
    <div class="mx-auto grid max-w-[1600px] gap-10 px-4 pb-10 pt-14 md:grid-cols-2 md:px-6 lg:grid-cols-[2fr_1fr_1fr_1fr] lg:pt-20">
      <div class="flex flex-col gap-4">
        <span class="font-logo text-[44px] leading-[.9] text-white md:text-[64px]">ONEHOLYLAB</span>
        <p class="max-w-xs">Faith-inspired streetwear for Gen Z. Heavyweight basics, printed with the words we live by.</p>
      </div>

      <div class="flex flex-col gap-2.5">
        <span class="cap mb-1 text-white">Shop</span>
        <a class="hover:text-white" href="<?= url('shop.php') ?>">New in</a>
        <?php foreach ($categories as $category): ?>
          <a class="hover:text-white" href="<?= url('shop.php?category=' . rawurlencode($category['slug'])) ?>"><?= e($category['name']) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="flex flex-col gap-2.5">
        <span class="cap mb-1 text-white">Help</span>
        <a class="hover:text-white" href="#">Shipping</a>
        <a class="hover:text-white" href="#">Returns</a>
        <a class="hover:text-white" href="#">Contact us</a>
      </div>

      <div class="flex flex-col gap-2.5">
        <span class="cap mb-1 text-white">Company</span>
        <a class="hover:text-white" href="#">About us</a>
        <a class="hover:text-white" href="#">Instagram</a>
        <a class="hover:text-white" href="<?= url('account.php') ?>">My account</a>
        <a class="hover:text-white" href="<?= url('admin/') ?>">Staff login</a>
      </div>
    </div>

    <div class="mx-auto flex max-w-[1600px] flex-wrap justify-between gap-2 border-t border-neutral-800 px-4 py-5 text-[11px] text-neutral-500 md:px-6">
      <span>&copy; <?= date('Y') ?> oneholylab</span>
      <span>Walk by faith, not by sight. 2 Corinthians 5:7</span>
    </div>
  </footer>
</body>
</html>
