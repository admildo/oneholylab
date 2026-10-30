<?php use Holy\{Csrf, View}; /** @var array $product @var array $variants @var array $images @var array $related */
$lowAt = config('low_stock_threshold');
$gallery = $images ?: [['path' => 'assets/img/placeholder.svg', 'alt' => $product['name']]];
$lastIndex = count($gallery) - 1;

// Pre-select the first size that is in stock.
$firstAvailable = null;
foreach ($variants as $variant) {
    if ($variant['stock'] > 0) {
        $firstAvailable = $variant['id'];
        break;
    }
}
?>
<nav class="cap mx-auto max-w-[1600px] px-4 pb-4 pt-5 text-muted md:px-6" aria-label="Breadcrumb">
  <a class="hover:text-black" href="<?= url() ?>">Home</a> /
  <a class="hover:text-black" href="<?= url('shop.php?category=' . rawurlencode($product['category_slug'] ?? '')) ?>"><?= e($product['category_name']) ?></a> /
  <span class="text-black"><?= e($product['name']) ?></span>
</nav>

<section class="mx-auto grid max-w-[1600px] gap-8 pb-16 md:px-6 lg:grid-cols-[minmax(0,3fr)_minmax(340px,2fr)] lg:gap-14">

  <!-- Photos: a swipe slider on phones, a grid on desktop -->
  <div>
    <div class="no-scrollbar flex snap-x snap-mandatory overflow-x-auto md:hidden" data-gallery>
      <?php foreach ($gallery as $i => $img): ?>
        <img class="aspect-[3/4] w-full shrink-0 snap-center bg-soft object-cover"
             <?= $i ? 'loading="lazy"' : '' ?> src="<?= url($img['path']) ?>" alt="<?= e($img['alt'] ?: $product['name']) ?>">
      <?php endforeach; ?>
    </div>

    <?php if (count($gallery) > 1): ?>
      <div class="mt-3 flex justify-center gap-1.5 md:hidden" aria-hidden="true">
        <?php foreach ($gallery as $i => $img): ?>
          <span class="h-1 w-6 <?= $i ? 'bg-neutral-300' : 'bg-black' ?>" data-dot></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="hidden gap-2 md:grid md:grid-cols-2">
      <?php foreach ($gallery as $i => $img): ?>
        <?php
        // The first photo is full width. The rest sit in pairs, and a last photo left on its own is full width too.
        $isWide = $i === 0 || ($i === $lastIndex && $i % 2 === 1);
        ?>
        <img class="w-full bg-soft object-cover <?= $isWide ? 'aspect-[4/5] md:col-span-2' : 'aspect-[3/4]' ?>"
             <?= $i ? 'loading="lazy"' : '' ?> src="<?= url($img['path']) ?>" alt="<?= e($img['alt'] ?: $product['name']) ?>">
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Buy box -->
  <div class="flex flex-col gap-7 self-start px-4 md:px-0 lg:sticky lg:top-[96px]">
    <div class="flex flex-col gap-2">
      <span class="cap text-muted"><?= e($product['category_name']) ?></span>
      <h1 class="font-logo text-[34px] leading-[1.02] md:text-[44px]"><?= e($product['name']) ?></h1>
      <div class="flex items-baseline gap-3">
        <span class="text-[18px]"><?= money($product['price']) ?></span>
        <?php if ($product['colour']): ?>
          <span class="text-muted">/ <?= e($product['colour']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <p class="max-w-[480px] text-[14px] leading-relaxed text-neutral-700"><?= nl2br(e($product['description'])) ?></p>

    <form method="post" action="<?= url('cart.php') ?>" class="flex flex-col gap-4">
      <?= Csrf::field() ?>
      <input type="hidden" name="action" value="add">

      <fieldset class="flex flex-col gap-2.5">
        <legend class="cap mb-2">Select size</legend>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($variants as $variant): ?>
            <?php $soldOut = $variant['stock'] <= 0; ?>
            <label class="<?= $soldOut ? 'cursor-not-allowed' : 'cursor-pointer' ?>">
              <input class="peer sr-only" type="radio" name="variant_id" value="<?= $variant['id'] ?>"
                     <?= $soldOut ? 'disabled' : '' ?> <?= $variant['id'] == $firstAvailable ? 'checked' : '' ?>>
              <span class="size peer-checked:size-on peer-focus-visible:outline <?= $soldOut ? 'size-out' : '' ?>"><?= e($variant['size']) ?></span>
              <?php if (!$soldOut && $variant['stock'] <= $lowAt): ?>
                <span class="mt-1 block text-center text-[10px] text-muted"><?= (int) $variant['stock'] ?> left</span>
              <?php endif; ?>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <?php if ($firstAvailable): ?>
        <button class="btn w-full gap-3" type="submit">
          <svg width="16" height="16" viewBox="0 0 20 20" aria-hidden="true"><path d="M4 6h12l-1 12H5L4 6z M7 6V5a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.4" fill="none"/></svg>
          Add to bag
        </button>
      <?php else: ?>
        <button class="btn w-full" type="button" disabled>Sold out</button>
      <?php endif; ?>
    </form>

    <ul class="grid gap-2.5 border-y border-line py-5 text-neutral-700">
      <li class="flex items-center gap-3">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 6h12v10H2zM14 9h4l4 4v3h-8M6 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm12 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
        Free delivery on orders over <?= money(config('free_delivery_over')) ?>
      </li>
      <li class="flex items-center gap-3">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.6M4 4v4h4" stroke="currentColor" stroke-width="1.3" fill="none"/></svg>
        Free returns within 14 days
      </li>
    </ul>

    <div class="-mt-7">
      <?php if (($product['category_slug'] ?? '') !== 'accessories'): ?>
        <details class="group border-b border-line py-4" open>
          <summary class="cap flex min-h-[24px] cursor-pointer list-none items-center justify-between">Details and fit<span class="text-lg leading-none transition-transform group-open:rotate-45">+</span></summary>
          <p class="mt-3 text-muted">Relaxed, boxy fit. If you are between sizes, take your usual size for a roomy look or size down for a closer fit. Machine wash cold, inside out.</p>
        </details>
      <?php endif; ?>

      <details class="group border-b border-line py-4">
        <summary class="cap flex min-h-[24px] cursor-pointer list-none items-center justify-between">Shipping and returns<span class="text-lg leading-none transition-transform group-open:rotate-45">+</span></summary>
        <p class="mt-3 text-muted">Standard delivery in 2 to 5 working days. Free delivery over <?= money(config('free_delivery_over')) ?>. Returns within 14 days.</p>
      </details>
    </div>
  </div>
</section>

<?php if ($related): ?>
  <section class="mx-auto max-w-[1600px] px-4 pb-10 md:px-6">
    <h2 class="mb-6 font-logo text-[30px] leading-none md:text-[40px]">You may also like</h2>
    <div class="grid grid-cols-2 gap-x-2 gap-y-10 md:grid-cols-3 md:gap-x-4 lg:grid-cols-4">
      <?php foreach ($related as $p): View::partial('product_card', ['p' => $p]); endforeach; ?>
    </div>
  </section>
<?php endif; ?>
