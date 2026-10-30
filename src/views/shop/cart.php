<?php use Holy\Csrf; /** @var array $cart */ ?>
<section class="mx-auto grid max-w-[1600px] gap-8 px-4 pb-16 pt-8 md:px-6 md:pt-12 lg:grid-cols-[2fr_1fr] lg:gap-12">

  <div class="flex flex-col gap-4">
    <h1 class="font-logo mb-2 text-[40px] leading-none md:text-[56px]">
      Your bag <span class="text-neutral-400">(<?= $cart['count'] ?>)</span>
    </h1>

    <?php if (!$cart['lines']): ?>
      <div class="flex flex-col items-start gap-4 border-t border-line py-12">
        <span class="text-[16px]">Your bag is empty.</span>
        <span class="text-muted">Find something you believe in.</span>
        <a class="btn" href="<?= url('shop.php') ?>">Continue shopping</a>
      </div>
    <?php endif; ?>

    <?php foreach ($cart['lines'] as $line): ?>
      <article class="flex items-start gap-4 border-t border-line pt-5 md:gap-6">
        <img class="aspect-[3/4] w-[110px] flex-none bg-soft object-cover md:w-[140px]" src="<?= url($line['image'] ?: 'assets/img/placeholder.svg') ?>" alt="<?= e($line['name']) ?>">

        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
          <div class="flex items-start justify-between gap-3">
            <a class="font-semibold hover:underline" href="<?= url('product.php?slug=' . rawurlencode($line['slug'])) ?>"><?= e($line['name']) ?></a>
            <span><?= money($line['line_total']) ?></span>
          </div>
          <span class="text-muted">Size <?= e($line['size']) ?> · <?= money($line['price']) ?> each</span>

          <?php if ($line['status'] !== 'published'): ?>
            <span class="font-medium">No longer for sale. Please remove it.</span>
          <?php endif; ?>
          <?php if ($line['stock'] < $line['quantity']): ?>
            <span class="font-medium">Only <?= (int) $line['stock'] ?> left. Please reduce the quantity.</span>
          <?php endif; ?>

          <div class="mt-2 flex flex-wrap items-center gap-4">
            <!-- Quantity: − and + share one form -->
            <form method="post" action="<?= url('cart.php') ?>" class="inline-flex items-center border border-neutral-300">
              <?= Csrf::field() ?>
              <input type="hidden" name="variant_id" value="<?= $line['variant_id'] ?>">
              <input type="hidden" name="action" value="set">
              <button class="min-h-[40px] min-w-[40px]" name="quantity" value="<?= $line['quantity'] - 1 ?>" aria-label="Decrease quantity">−</button>
              <span class="min-w-[28px] text-center"><?= (int) $line['quantity'] ?></span>
              <button class="min-h-[40px] min-w-[40px]" name="quantity" value="<?= $line['quantity'] + 1 ?>" aria-label="Increase quantity">+</button>
            </form>

            <form method="post" action="<?= url('cart.php') ?>">
              <?= Csrf::field() ?>
              <input type="hidden" name="variant_id" value="<?= $line['variant_id'] ?>">
              <input type="hidden" name="action" value="set">
              <button class="cap min-h-[40px] text-muted underline" name="quantity" value="0">Remove</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($cart['lines']): ?>
    <aside class="flex flex-col gap-3.5 self-start bg-soft p-6 lg:sticky lg:top-[96px]">
      <h2 class="font-logo text-[26px] leading-none">Summary</h2>
      <div class="flex justify-between"><span class="text-muted">Subtotal</span><span><?= money($cart['subtotal']) ?></span></div>
      <div class="flex justify-between"><span class="text-muted">Delivery (standard)</span><span><?= $cart['delivery'] > 0 ? money($cart['delivery']) : 'Free' ?></span></div>
      <div class="flex justify-between border-t border-neutral-300 pt-3 text-[16px]"><span class="font-semibold">Total</span><b class="font-semibold"><?= money($cart['total']) ?></b></div>
      <span class="text-xs text-muted">Free standard delivery on orders over <?= money(config('free_delivery_over')) ?></span>
      <a class="btn w-full" href="<?= url('checkout.php') ?>">Continue to checkout</a>
      <a class="cap link-u text-center" href="<?= url('shop.php') ?>">Continue shopping</a>
    </aside>
  <?php endif; ?>
</section>
