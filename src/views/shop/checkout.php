<?php use Holy\{Csrf, CartService, View}; /** @var array $cart @var array $v saved or submitted address values @var array $errors @var string $method */ ?>
<div class="mx-auto flex max-w-[1600px] flex-wrap items-end justify-between gap-4 px-4 pb-8 pt-8 md:px-6 md:pt-12">
  <h1 class="font-logo text-[40px] leading-none md:text-[56px]">Checkout</h1>
  <a href="<?= url('cart.php') ?>" class="cap link-u">Back to bag</a>
</div>

<section class="mx-auto grid max-w-[1600px] gap-10 px-4 pb-16 md:px-6 lg:grid-cols-[2fr_1fr] lg:gap-12">

  <form method="post" action="<?= url('checkout.php') ?>" class="flex flex-col gap-10" id="checkout" novalidate>
    <?= Csrf::field() ?>
    <?php View::partial('form_errors', ['errors' => $errors]); ?>

    <!-- 1. Delivery address -->
    <fieldset class="flex flex-col gap-5">
      <legend class="mb-5 flex items-center gap-3 font-logo text-[24px] leading-none">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-black font-sans text-[12px] font-semibold text-white">1</span>
        Delivery address
      </legend>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="field">
          <label for="first_name">First name</label>
          <input id="first_name" name="first_name" class="input" required value="<?= e($v['first_name']) ?>" autocomplete="given-name">
        </div>
        <div class="field">
          <label for="last_name">Last name</label>
          <input id="last_name" name="last_name" class="input" required value="<?= e($v['last_name']) ?>" autocomplete="family-name">
        </div>
        <div class="field md:col-span-2">
          <label for="line1">Street address</label>
          <input id="line1" name="line1" class="input" required value="<?= e($v['line1']) ?>" autocomplete="address-line1">
        </div>
        <div class="field">
          <label for="city">City</label>
          <input id="city" name="city" class="input" required value="<?= e($v['city']) ?>" autocomplete="address-level2">
        </div>
        <div class="field">
          <label for="province">Province</label>
          <input id="province" name="province" class="input" required value="<?= e($v['province']) ?>" autocomplete="address-level1">
        </div>
        <div class="field">
          <label for="postal_code">Postal code</label>
          <input id="postal_code" name="postal_code" class="input" required value="<?= e($v['postal_code']) ?>" autocomplete="postal-code">
        </div>
        <div class="field">
          <label for="country">Country</label>
          <input id="country" name="country" class="input" required value="<?= e($v['country']) ?>" autocomplete="country-name">
        </div>
      </div>

      <label class="flex min-h-[44px] items-center gap-2.5">
        <input type="checkbox" name="save_address" value="1" checked> Save this address to my account
      </label>
    </fieldset>

    <!-- 2. Delivery method: picking one reloads the page so the total updates -->
    <fieldset class="flex flex-col gap-2.5">
      <legend class="mb-5 flex items-center gap-3 font-logo text-[24px] leading-none">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-black font-sans text-[12px] font-semibold text-white">2</span>
        Delivery method
      </legend>

      <?php foreach (config('delivery_methods') as $key => $option): ?>
        <?php $fee = CartService::deliveryFee($cart['subtotal'], $key); ?>
        <label class="boxed flex min-h-[56px] cursor-pointer items-center gap-3 has-[:checked]:border-black has-[:checked]:shadow-[inset_0_0_0_1px_#000]">
          <input type="radio" name="method" value="<?= e($key) ?>" data-autosubmit <?= $method === $key ? 'checked' : '' ?>>
          <span class="flex-1">
            <?= e($option['label']) ?><br>
            <span class="text-xs text-muted"><?= e($option['note']) ?></span>
          </span>
          <span><?= $fee > 0 ? money($fee) : 'Free' ?></span>
        </label>
      <?php endforeach; ?>
    </fieldset>

    <!-- 3. Payment: card details go to the provider, not to this server -->
    <fieldset class="flex flex-col gap-5">
      <legend class="mb-5 flex items-center gap-3 font-logo text-[24px] leading-none">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-black font-sans text-[12px] font-semibold text-white">3</span>
        Payment
      </legend>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="field md:col-span-2">
          <label for="card_number">Card number</label>
          <input id="card_number" name="card_number" class="input" inputmode="numeric" autocomplete="cc-number" data-card-number placeholder="4242 4242 4242 4242">
        </div>
        <div class="field">
          <label for="card_expiry">Expiry (MM / YY)</label>
          <input id="card_expiry" name="card_expiry" class="input" autocomplete="cc-exp" data-card-expiry placeholder="12 / 28">
        </div>
        <div class="field">
          <label for="card_cvc">CVC</label>
          <input id="card_cvc" name="card_cvc" class="input" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123">
        </div>
      </div>

      <p class="text-xs text-muted">
        Card details go straight to the payment provider and are never stored on our server.
        <?php if (config('payment_provider') === 'demo'): ?>
          Test mode: use card 4242 4242 4242 4242, any future date, any 3 digits. No money is taken.
        <?php endif; ?>
      </p>
    </fieldset>
  </form>

  <!-- Order summary -->
  <aside class="flex flex-col gap-3.5 self-start bg-soft p-6 lg:sticky lg:top-[96px]">
    <h2 class="font-logo text-[26px] leading-none">Your order</h2>

    <?php foreach ($cart['lines'] as $line): ?>
      <div class="flex items-center gap-3">
        <img class="aspect-[3/4] w-14 bg-soft object-cover" src="<?= url($line['image'] ?: 'assets/img/placeholder.svg') ?>" alt="">
        <div class="flex flex-1 flex-col">
          <span><?= e($line['name']) ?></span>
          <span class="text-muted">Size <?= e($line['size']) ?> · <?= (int) $line['quantity'] ?></span>
        </div>
        <span><?= money($line['line_total']) ?></span>
      </div>
    <?php endforeach; ?>

    <div class="flex justify-between border-t border-neutral-300 pt-3">
      <span class="text-muted">Subtotal</span><span><?= money($cart['subtotal']) ?></span>
    </div>
    <div class="flex justify-between">
      <span class="text-muted">Delivery</span><span><?= $cart['delivery'] > 0 ? money($cart['delivery']) : 'Free' ?></span>
    </div>
    <div class="flex justify-between border-t border-neutral-300 pt-3 text-[16px]">
      <span class="font-semibold">Total</span><b class="font-semibold"><?= money($cart['total']) ?></b>
    </div>

    <button class="btn w-full" type="submit" form="checkout" name="place" value="1">Pay <?= money($cart['total']) ?></button>
    <span class="text-center text-xs text-muted">By placing your order you agree to our terms and returns policy.</span>
  </aside>
</section>
