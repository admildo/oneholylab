<?php /** @var array $log @var array $moves */ ?>
<h1 class="cap text-[15px] font-medium">History</h1>

<div class="grid items-start gap-6 lg:grid-cols-2">

  <!-- Admin changes -->
  <section class="panel">
    <div class="panel-h"><h2 class="cap text-xs font-medium">Admin changes</h2></div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[420px] border-collapse">
        <thead>
          <tr>
            <th class="th">When</th>
            <th class="th">Who</th>
            <th class="th">What</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($log as $entry): ?>
            <tr>
              <td class="td whitespace-nowrap"><?= date('d M H:i', strtotime($entry['created_at'])) ?></td>
              <td class="td"><?= e($entry['user_name'] ?? 'System') ?></td>
              <td class="td">
                <?= e($entry['action'] . ' ' . $entry['entity']) ?><br>
                <span class="text-muted"><?= e($entry['detail']) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$log): ?>
            <tr><td class="td text-muted" colspan="3">No changes yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Stock movements -->
  <section class="panel">
    <div class="panel-h"><h2 class="cap text-xs font-medium">Stock movements</h2></div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[420px] border-collapse">
        <thead>
          <tr>
            <th class="th">When</th>
            <th class="th">Item</th>
            <th class="th">Change</th>
            <th class="th">Reason</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($moves as $move): ?>
            <tr>
              <td class="td whitespace-nowrap"><?= date('d M H:i', strtotime($move['created_at'])) ?></td>
              <td class="td"><?= e($move['product_name']) ?> (<?= e($move['size']) ?>)</td>
              <td class="td"><?= ($move['change_qty'] > 0 ? '+' : '') . (int) $move['change_qty'] ?></td>
              <td class="td">
                <?= e(ucfirst($move['reason'])) ?><?= $move['user_name'] ? ' · ' . e($move['user_name']) : '' ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$moves): ?>
            <tr><td class="td text-muted" colspan="4">No stock movements yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
