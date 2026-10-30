<?php use Holy\{Csrf, OrderService, View}; /** @var array $orders @var string $status @var ?array $selected @var array $items */ ?>
<h1 class="cap text-[15px] font-medium">Orders</h1>

<!-- Status filter -->
<div class="flex flex-wrap gap-2" role="group" aria-label="Filter by status">
  <a class="chip <?= $status === '' ? 'chip-on' : '' ?>" href="<?= url('admin/orders.php') ?>">All</a>
  <?php foreach (OrderService::STATUSES as $statusName): ?>
    <a class="chip <?= $status === $statusName ? 'chip-on' : '' ?>" href="<?= url('admin/orders.php?status=' . $statusName) ?>"><?= ucfirst($statusName) ?></a>
  <?php endforeach; ?>
</div>

<div class="grid items-start gap-6 lg:grid-cols-[2fr_1fr]">

  <!-- Order list -->
  <section class="panel">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[620px] border-collapse">
        <thead>
          <tr>
            <th class="th">Order</th>
            <th class="th">Customer</th>
            <th class="th">Date</th>
            <th class="th">Status</th>
            <th class="th">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <?php
            $isOpen = $selected && $selected['id'] == $order['id'];
            $link = 'admin/orders.php?id=' . $order['id'] . ($status ? '&status=' . $status : '');
            ?>
            <tr class="<?= $isOpen ? 'bg-neutral-50' : '' ?>">
              <td class="td"><a class="underline" href="<?= e(url($link)) ?>"><?= e($order['order_no']) ?></a></td>
              <td class="td"><?= e($order['customer']) ?></td>
              <td class="td"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
              <td class="td"><?php View::partial('status_pill', ['status' => $order['status']]); ?></td>
              <td class="td"><?= money($order['total']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$orders): ?>
            <tr><td class="td text-muted" colspan="5">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Selected order: details and status form -->
  <?php if ($selected): ?>
    <aside class="panel">
      <div class="panel-h">
        <h2 class="cap text-xs font-medium">Order <?= e($selected['order_no']) ?></h2>
        <span class="pill"><?= ucfirst(e($selected['status'])) ?></span>
      </div>

      <div class="flex flex-col gap-4 p-4">
        <div class="flex flex-col gap-0.5">
          <span class="cap text-muted">Customer</span>
          <span><?= e($selected['customer']) ?> · <?= e($selected['email']) ?></span>
          <span class="text-muted">
            <?= e($selected['ship_line1']) ?>, <?= e($selected['ship_city']) ?>, <?= e($selected['ship_province']) ?> <?= e($selected['ship_postal']) ?>
          </span>
        </div>

        <div class="flex flex-col gap-1">
          <span class="cap text-muted">Items</span>
          <?php foreach ($items as $item): ?>
            <div class="flex justify-between gap-3">
              <span><?= e($item['product_name']) ?> (<?= e($item['size']) ?>) x<?= (int) $item['quantity'] ?></span>
              <span><?= money($item['unit_price'] * $item['quantity']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="flex justify-between border-t border-line pt-3">
          <span class="cap">Total</span>
          <b class="font-medium"><?= money($selected['total']) ?></b>
        </div>

        <form method="post" class="flex flex-col gap-4">
          <?= Csrf::field() ?>
          <input type="hidden" name="order_id" value="<?= $selected['id'] ?>">

          <div class="field">
            <label for="st">Update status</label>
            <select id="st" name="status" class="box-input w-full">
              <?php foreach (OrderService::STATUSES as $statusName): ?>
                <option value="<?= $statusName ?>" <?= $selected['status'] === $statusName ? 'selected' : '' ?>><?= ucfirst($statusName) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="tr">Tracking number</label>
            <input id="tr" name="tracking_no" class="input" value="<?= e($selected['tracking_no']) ?>" placeholder="Optional">
          </div>

          <p class="text-xs text-muted">Cancelling an order puts its items back into stock. The customer is emailed when the status changes.</p>
          <button class="btn w-full" type="submit">Save and notify customer</button>
        </form>
      </div>
    </aside>
  <?php endif; ?>
</div>
