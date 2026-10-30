<?php use Holy\View; /** @var int $days @var array $kpis @var array $daily @var array $top @var array $low @var array $recent */
$max = max(1, ...array_column($daily, 'total'));

// The sales card says how it compares with the period before. $change is null when there was no earlier sale.
$change = $kpis['change'];
$salesNote = $change === null
    ? 'No earlier sales to compare'
    : ($change >= 0 ? '+' : '') . $change . '% vs previous period';
?>
<div class="flex flex-wrap items-center justify-between gap-3">
  <h1 class="cap text-[15px] font-medium">Dashboard</h1>

  <form method="get" class="flex items-center gap-2">
    <label for="days" class="cap text-muted">Period</label>
    <select id="days" name="days" class="box-input" data-autosubmit>
      <?php foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $option => $label): ?>
        <option value="<?= $option ?>" <?= $option === $days ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<!-- Key figures -->
<section class="grid grid-cols-2 gap-3 md:grid-cols-4" aria-label="Key figures">
  <div class="flex flex-col gap-1.5 border border-line bg-white p-4">
    <span class="cap text-muted">Sales</span>
    <b class="text-2xl font-medium"><?= money($kpis['sales']) ?></b>
    <span class="text-xs text-muted"><?= e($salesNote) ?></span>
  </div>

  <div class="flex flex-col gap-1.5 border border-line bg-white p-4">
    <span class="cap text-muted">Orders</span>
    <b class="text-2xl font-medium"><?= $kpis['orders'] ?></b>
    <a class="text-xs underline" href="<?= url('admin/orders.php?status=paid') ?>"><?= $kpis['to_fulfil'] ?> to fulfil</a>
  </div>

  <div class="flex flex-col gap-1.5 border border-line bg-white p-4">
    <span class="cap text-muted">New customers</span>
    <b class="text-2xl font-medium"><?= $kpis['customers'] ?></b>
  </div>

  <div class="flex flex-col gap-1.5 border border-line bg-white p-4">
    <span class="cap text-muted">Low stock items</span>
    <b class="text-2xl font-medium"><?= $kpis['low_stock'] ?></b>
    <a class="text-xs underline" href="<?= url('admin/products.php?low=1') ?>">Review stock</a>
  </div>
</section>

<!-- Sales chart and top sellers -->
<div class="grid gap-6 lg:grid-cols-[2fr_1fr]">

  <section class="panel">
    <div class="panel-h">
      <h2 class="cap text-xs font-medium">Sales per day</h2>
      <span class="text-xs text-muted">Last <?= $days ?> days</span>
    </div>
    <div class="p-4">
      <div class="flex h-40 items-end gap-1" role="img" aria-label="Bar chart of sales per day">
        <?php foreach ($daily as $day): ?>
          <div class="min-w-[3px] flex-1 <?= $day['total'] == $max ? 'bg-black' : 'bg-neutral-300' ?>"
               style="height:<?= max(1, round($day['total'] / $max * 100)) ?>%"
               title="<?= e($day['date']) ?>: <?= money($day['total']) ?>"></div>
        <?php endforeach; ?>
      </div>
      <div class="mt-2 flex justify-between text-[11px] text-muted">
        <span><?= e($daily[0]['date']) ?></span>
        <span><?= e(end($daily)['date']) ?></span>
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-h"><h2 class="cap text-xs font-medium">Top sellers</h2></div>
    <div class="flex flex-col gap-3.5 p-4">
      <?php if (!$top): ?>
        <span class="text-muted">No sales in this period yet.</span>
      <?php endif; ?>

      <?php foreach ($top as $seller): ?>
        <div class="flex items-center gap-3">
          <img class="h-[58px] w-11 bg-soft object-cover" src="<?= url($seller['image'] ?: 'assets/img/placeholder.svg') ?>" alt="">
          <span class="min-w-0 flex-1"><?= e($seller['name']) ?></span>
          <span class="text-muted"><?= (int) $seller['units'] ?> sold</span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<!-- Recent orders and low stock -->
<div class="grid gap-6 lg:grid-cols-[2fr_1fr]">

  <section class="panel">
    <div class="panel-h">
      <h2 class="cap text-xs font-medium">Recent orders</h2>
      <a class="cap text-[11px]" href="<?= url('admin/orders.php') ?>">View all</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[560px] border-collapse">
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
          <?php foreach ($recent as $order): ?>
            <tr>
              <td class="td"><a class="underline" href="<?= url('admin/orders.php?id=' . $order['id']) ?>"><?= e($order['order_no']) ?></a></td>
              <td class="td"><?= e($order['customer']) ?></td>
              <td class="td"><?= date('d M', strtotime($order['created_at'])) ?></td>
              <td class="td"><?php View::partial('status_pill', ['status' => $order['status']]); ?></td>
              <td class="td"><?= money($order['total']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recent): ?>
            <tr><td class="td text-muted" colspan="5">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel">
    <div class="panel-h"><h2 class="cap text-xs font-medium">Low stock</h2></div>
    <div class="flex flex-col gap-3 p-4">
      <?php if (!$low): ?>
        <span class="text-muted">Everything is well stocked.</span>
      <?php endif; ?>

      <?php foreach ($low as $item): ?>
        <div class="flex items-center justify-between gap-3">
          <a class="underline" href="<?= url('admin/product_edit.php?id=' . $item['product_id']) ?>"><?= e($item['name']) ?> (<?= e($item['size']) ?>)</a>
          <span class="pill"><?= (int) $item['stock'] ?> left</span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
