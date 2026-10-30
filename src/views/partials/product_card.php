<?php /** @var array $p a product row from ProductRepository::search() or related() */
// Only search results include stock, so the stock badges appear only when it is known.
$hasStock = isset($p['stock']);
$soldOut = $hasStock && (int) $p['stock'] === 0;
$lowStock = $hasStock && $p['stock'] > 0 && $p['stock'] <= config('low_stock_threshold');
$hasSecondPhoto = !empty($p['image2']);
?>
<a class="group flex flex-col gap-3" href="<?= url('product.php?slug=' . rawurlencode($p['slug'])) ?>">
  <div class="relative aspect-[3/4] overflow-hidden bg-soft">
    <img class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.03]<?= $hasSecondPhoto ? ' group-hover:opacity-0' : '' ?><?= $soldOut ? ' opacity-60' : '' ?>"
         loading="lazy" src="<?= url($p['image'] ?: 'assets/img/placeholder.svg') ?>" alt="<?= e($p['name']) ?>">

    <?php if ($hasSecondPhoto): ?>
      <img class="absolute inset-0 h-full w-full object-cover opacity-0 transition duration-700 ease-out group-hover:scale-[1.03] group-hover:opacity-100"
           loading="lazy" src="<?= url($p['image2']) ?>" alt="" aria-hidden="true">
    <?php endif; ?>

    <?php if ($soldOut): ?>
      <span class="badge badge-dark absolute left-2 top-2">Sold out</span>
    <?php elseif ($lowStock): ?>
      <span class="badge absolute left-2 top-2">Low stock</span>
    <?php endif; ?>

    <?php if (!$soldOut): ?>
      <span class="cap absolute inset-x-2 bottom-2 hidden translate-y-2 bg-white/95 py-3 text-center opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 md:block">View product</span>
    <?php endif; ?>
  </div>

  <div class="flex flex-col gap-1 px-1 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
    <div class="flex min-w-0 flex-col gap-0.5">
      <span class="font-medium leading-snug"><?= e($p['name']) ?></span>
      <?php if (!empty($p['colour'])): ?>
        <span class="text-muted"><?= e($p['colour']) ?></span>
      <?php endif; ?>
    </div>
    <span class="whitespace-nowrap"><?= money($p['price']) ?></span>
  </div>
</a>
