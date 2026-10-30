<?php use Holy\View; /** @var array $new @var array $hero @var array $tiles @var ?array $feature */ ?>

<section class="relative h-[calc(100svh-104px)] min-h-[520px] max-h-[860px] overflow-hidden bg-ink text-white" aria-label="New collection">
  <picture>
    <source media="(min-width: 768px)" srcset="<?= url('assets/img/hero/hero.jpg') ?>">
    <img class="absolute inset-0 h-full w-full object-cover object-[70%_center] md:object-center" src="<?= url('assets/img/hero/hero-mobile.jpg') ?>" alt="Model wearing the John 3:16 oversized hoodie">
  </picture>
  <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent md:bg-gradient-to-r md:from-black/65 md:via-black/25"></div>

  <div class="relative mx-auto flex h-full max-w-[1600px] flex-col justify-end gap-5 px-4 pb-10 md:justify-center md:px-6 md:pb-0">
    <span class="cap">New collection / Autumn <?= date('Y') ?></span>
    <h1 class="font-logo text-[64px] font-medium leading-[.88] tracking-tight md:text-[112px] lg:text-[140px]">WALK<br>BY FAITH</h1>
    <p class="max-w-sm text-[15px] text-neutral-200">Heavyweight hoodies and boxy tees, printed with the verses we carry every day.</p>
    <div class="flex flex-wrap gap-2 pt-1">
      <a class="btn btn-light" href="<?= url('shop.php') ?>">Shop the collection</a>
      <?php if ($hero): ?>
        <a class="btn btn-outline-light" href="<?= url('product.php?slug=' . rawurlencode($hero['slug'])) ?>">Shop the hoodie</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="border-b border-line" aria-label="Why shop with us">
  <ul class="mx-auto grid max-w-[1600px] grid-cols-1 divide-y divide-line px-4 sm:grid-cols-3 sm:divide-x sm:divide-y-0 md:px-6">
    <li class="flex items-center gap-3 py-4 sm:justify-center">
      <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 6h12v10H2zM14 9h4l4 4v3h-8M6 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm12 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
      <span><span class="font-medium">Free delivery</span> <span class="text-muted">over <?= money(config('free_delivery_over')) ?></span></span>
    </li>
    <li class="flex items-center gap-3 py-4 sm:justify-center">
      <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.6M4 4v4h4" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
      <span><span class="font-medium">14-day returns</span> <span class="text-muted">no questions asked</span></span>
    </li>
    <li class="flex items-center gap-3 py-4 sm:justify-center">
      <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
      <span><span class="font-medium">Secure checkout</span> <span class="text-muted">card details never stored</span></span>
    </li>
  </ul>
</section>

<section class="mx-auto max-w-[1600px] px-4 pb-6 pt-14 md:px-6 md:pt-20">
  <div class="mb-6 flex items-end justify-between gap-4">
    <div>
      <span class="cap text-muted">Just dropped</span>
      <h2 class="mt-1 font-logo text-[34px] leading-none md:text-[48px]">New in</h2>
    </div>
    <a class="cap link-u" href="<?= url('shop.php') ?>">View all</a>
  </div>
  <div class="grid grid-cols-2 gap-x-2 gap-y-10 md:grid-cols-3 md:gap-x-4 lg:grid-cols-4">
    <?php foreach ($new as $p): View::partial('product_card', ['p' => $p]); endforeach; ?>
  </div>
</section>

<section class="mx-auto max-w-[1600px] px-4 py-14 md:px-6 md:py-20">
  <div class="mb-6">
    <span class="cap text-muted">Find your fit</span>
    <h2 class="mt-1 font-logo text-[34px] leading-none md:text-[48px]">Shop by category</h2>
  </div>
  <div class="grid gap-2 md:grid-cols-3 md:gap-4">
    <?php foreach ($tiles as $tile): ?>
      <a class="group relative block aspect-[4/5] overflow-hidden bg-soft" href="<?= url('shop.php?category=' . rawurlencode($tile['slug'])) ?>">
        <img class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105" loading="lazy" src="<?= url($tile['image'] ?: 'assets/img/placeholder.svg') ?>" alt="">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-transparent"></div>
        <div class="absolute inset-x-5 bottom-5 flex items-end justify-between text-white">
          <span class="font-logo text-[28px] leading-none md:text-[34px]"><?= e($tile['name']) ?></span>
          <span class="cap inline-flex items-center gap-2">
            Shop
            <svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" class="transition-transform group-hover:translate-x-1"><path d="M1 7h11M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
          </span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($feature): ?>
  <section class="mx-auto grid max-w-[1600px] items-stretch gap-0 px-4 md:grid-cols-2 md:px-6" aria-label="Featured">
    <a class="block aspect-[4/5] overflow-hidden bg-soft md:aspect-auto" href="<?= url('product.php?slug=' . rawurlencode($feature['slug'])) ?>">
      <img class="h-full w-full object-cover" loading="lazy" src="<?= url($feature['image']) ?>" alt="<?= e($feature['name']) ?>">
    </a>
    <div class="flex flex-col justify-center gap-5 bg-soft px-6 py-14 md:px-14 lg:px-20">
      <span class="cap text-muted">The story</span>
      <p class="font-logo text-[32px] leading-[1.05] md:text-[44px] lg:text-[56px]">Clothing for a generation that wears what it believes.</p>
      <p class="max-w-md text-muted">Every piece starts with a verse. We print it on heavyweight cotton cut for a relaxed, boxy fit, so it is something you reach for every day, not just on Sunday.</p>
      <div>
        <a class="btn" href="<?= url('product.php?slug=' . rawurlencode($feature['slug'])) ?>">Shop <?= e($feature['name']) ?></a>
      </div>
    </div>
  </section>
<?php endif; ?>
