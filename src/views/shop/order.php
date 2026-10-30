<?php use Holy\Auth; /** @var array $order @var array $items */
$steps = ['paid' => 'Paid', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
$stepKeys = array_keys($steps);
$currentStep = array_search($order['status'], $stepKeys, true);
?>
<section class="mx-auto flex max-w-[760px] flex-col gap-7 px-4 pb-20 pt-10 md:px-6 md:pt-16">

  <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-black text-white">
    <svg width="20" height="20" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.6" fill="none"/></svg>
  </span>

  <span class="cap text-muted">Order <?= e($order['order_no']) ?></span>
  <h1 class="font-logo text-[44px] leading-none md:text-[56px]">Thank you, <?= e(Auth::user()['first_name']) ?>.</h1>
  <p class="max-w-[520px]">Your payment was received and your order is confirmed. We have emailed a receipt and will email you again when it ships.</p>

  <div class="flex flex-col gap-3 bg-soft p-6">
    <?php foreach ($items as $item): ?>
      <div class="flex justify-between gap-4">
        <span><?= e($item['product_name']) ?> · size <?= e($item['size']) ?> · <?= (int) $item['quantity'] ?></span>
        <span><?= money($item['unit_price'] * $item['quantity']) ?></span>
      </div>
    <?php endforeach; ?>

    <div class="flex justify-between border-t border-neutral-300 pt-3">
      <span class="text-muted">Delivery</span>
      <span><?= $order['delivery_fee'] > 0 ? money($order['delivery_fee']) : 'Free' ?></span>
    </div>
    <div class="flex justify-between text-[16px]">
      <span class="font-semibold">Total paid</span><b class="font-semibold"><?= money($order['total']) ?></b>
    </div>
  </div>

  <p class="text-muted">
    Delivering to <?= e($order['ship_name']) ?>, <?= e($order['ship_line1']) ?>, <?= e($order['ship_city']) ?>, <?= e($order['ship_postal']) ?>.
  </p>

  <!-- Progress bar: every step up to the current one is filled in -->
  <ol class="cap flex gap-2">
    <?php foreach ($stepKeys as $n => $key): ?>
      <?php $done = $order['status'] !== 'cancelled' && $currentStep !== false && $n <= $currentStep; ?>
      <li class="flex flex-1 flex-col gap-2 <?= $done ? 'font-semibold' : 'text-muted' ?>">
        <span class="h-1 w-full <?= $done ? 'bg-black' : 'bg-neutral-200' ?>"></span>
        <?= $steps[$key] ?>
      </li>
    <?php endforeach; ?>
  </ol>

  <?php if ($order['tracking_no']): ?>
    <p>Tracking number: <b><?= e($order['tracking_no']) ?></b></p>
  <?php endif; ?>

  <div class="flex flex-wrap gap-2">
    <a class="btn" href="<?= url('account.php') ?>">My orders</a>
    <a class="btn btn-ghost" href="<?= url('shop.php') ?>">Continue shopping</a>
  </div>
</section>
