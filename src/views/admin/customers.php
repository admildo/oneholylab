<?php /** @var array $customers @var string $q */ ?>
<h1 class="cap text-[15px] font-medium">Customers</h1>

<form method="get" class="flex items-center gap-2">
  <label for="q" class="cap text-muted">Search</label>
  <input id="q" name="q" type="search" class="box-input min-w-0 flex-[1_1_200px]" placeholder="Name or email" value="<?= e($q) ?>">
  <button class="btn btn-ghost !min-h-[40px]">Search</button>
</form>

<section class="panel">
  <div class="overflow-x-auto">
    <table class="w-full min-w-[640px] border-collapse">
      <thead>
        <tr>
          <th class="th">Customer</th>
          <th class="th">Email</th>
          <th class="th">Joined</th>
          <th class="th">Orders</th>
          <th class="th">Total spent</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $customer): ?>
          <tr>
            <td class="td"><?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></td>
            <td class="td"><?= e($customer['email']) ?></td>
            <td class="td"><?= date('d M Y', strtotime($customer['created_at'])) ?></td>
            <td class="td"><?= (int) $customer['orders'] ?></td>
            <td class="td"><?= money($customer['spent']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$customers): ?>
          <tr><td class="td text-muted" colspan="5">No customers found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
