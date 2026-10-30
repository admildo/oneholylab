<?php use Holy\{Csrf, View}; /** @var array $user @var array $orders @var array $address @var string $tab @var array $errors */
$tabs = ['orders' => 'My orders', 'details' => 'Personal details', 'address' => 'Address'];
?>
<section class="mx-auto flex max-w-[1600px] flex-col gap-8 px-4 pb-20 pt-8 md:px-6 md:pt-12">

  <div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-logo text-[40px] leading-none md:text-[56px]">Hi, <?= e($user['first_name']) ?></h1>
    <form method="post" action="<?= url('logout.php') ?>">
      <?= Csrf::field() ?>
      <button class="cap link-u">Log out</button>
    </form>
  </div>

  <nav class="flex flex-wrap gap-2 border-b border-line pb-6" aria-label="Account sections">
    <?php foreach ($tabs as $key => $label): ?>
      <a class="chip <?= $tab === $key ? 'chip-on' : 'hover:border-black' ?>" href="<?= url('account.php?tab=' . $key) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>

  <?php if ($tab === 'orders'): ?>

    <?php if (!$orders): ?>
      <p class="text-muted">You have not placed an order yet. <a class="underline" href="<?= url('shop.php') ?>">Start shopping</a></p>
    <?php endif; ?>

    <?php foreach ($orders as $order): ?>
      <article class="grid grid-cols-2 items-center gap-4 border-b border-line py-5 md:grid-cols-4">
        <div class="flex flex-col gap-1">
          <span class="font-semibold"><?= e($order['order_no']) ?></span>
          <span class="text-muted"><?= date('d M Y', strtotime($order['created_at'])) ?> · <?= (int) $order['item_count'] ?> items</span>
        </div>
        <?php View::partial('status_pill', ['status' => $order['status']]); ?>
        <span><?= money($order['total']) ?></span>
        <a class="cap link-u justify-self-end" href="<?= url('order.php?no=' . rawurlencode($order['order_no'])) ?>">View order</a>
      </article>
    <?php endforeach; ?>

  <?php elseif ($tab === 'details'): ?>

    <form method="post" action="<?= url('account.php?tab=details') ?>" class="flex max-w-[640px] flex-col gap-5">
      <?= Csrf::field() ?>
      <input type="hidden" name="form" value="details">
      <?php View::partial('form_errors', ['errors' => $errors]); ?>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="field">
          <label for="first_name">First name</label>
          <input id="first_name" name="first_name" class="input" value="<?= e($user['first_name']) ?>" required>
        </div>
        <div class="field">
          <label for="last_name">Last name</label>
          <input id="last_name" name="last_name" class="input" value="<?= e($user['last_name']) ?>" required>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input id="email" class="input" value="<?= e($user['email']) ?>" disabled>
        </div>
        <div class="field">
          <label for="phone">Phone</label>
          <input id="phone" name="phone" class="input" value="<?= e($user['phone']) ?>">
        </div>
      </div>

      <button class="btn self-start" type="submit">Save changes</button>
    </form>

  <?php else: ?>

    <form method="post" action="<?= url('account.php?tab=address') ?>" class="flex max-w-[640px] flex-col gap-5">
      <?= Csrf::field() ?>
      <input type="hidden" name="form" value="address">
      <?php View::partial('form_errors', ['errors' => $errors]); ?>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="field md:col-span-2">
          <label for="line1">Street address</label>
          <input id="line1" name="line1" class="input" value="<?= e($address['line1'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="city">City</label>
          <input id="city" name="city" class="input" value="<?= e($address['city'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="province">Province</label>
          <input id="province" name="province" class="input" value="<?= e($address['province'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="postal_code">Postal code</label>
          <input id="postal_code" name="postal_code" class="input" value="<?= e($address['postal_code'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="country">Country</label>
          <input id="country" name="country" class="input" value="<?= e($address['country'] ?? 'South Africa') ?>" required>
        </div>
      </div>

      <button class="btn self-start" type="submit">Save address</button>
    </form>

  <?php endif; ?>
</section>
