<?php /** @var array $products @var array $categories @var array $f */
$thr = config('low_stock_threshold');
?>
<div class="flex flex-wrap items-center justify-between gap-3">
  <h1 class="cap text-[15px] font-medium">Products</h1>
  <a class="btn" href="<?= url('admin/product_edit.php') ?>">Add product</a>
</div>

<!-- Filters -->
<form method="get" class="flex flex-wrap items-center gap-2">
  <label for="q" class="cap text-muted">Search</label>
  <input id="q" name="q" type="search" class="box-input min-w-0 flex-[1_1_200px]" placeholder="Name or SKU" value="<?= e($f['q']) ?>">

  <label for="c" class="cap text-muted">Category</label>
  <select id="c" name="category" class="box-input" data-autosubmit>
    <option value="">All</option>
    <?php foreach ($categories as $category): ?>
      <option value="<?= e($category['slug']) ?>" <?= $f['category'] === $category['slug'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label class="chip cursor-pointer <?= $f['lowStock'] ? 'chip-on' : '' ?>">
    <input class="sr-only" type="checkbox" name="low" value="1" <?= $f['lowStock'] ? 'checked' : '' ?> data-autosubmit>
    Low stock only
  </label>

  <button class="btn btn-ghost !min-h-[40px]" type="submit">Search</button>
</form>

<section class="panel">
  <div class="overflow-x-auto">
    <table class="w-full min-w-[720px] border-collapse">
      <thead>
        <tr>
          <th class="th"></th>
          <th class="th">Product</th>
          <th class="th">Category</th>
          <th class="th">Price</th>
          <th class="th">Stock</th>
          <th class="th">Status</th>
          <th class="th"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
          <?php
          $stock = (int) $product['stock'];
          $flagged = $stock <= $thr; // low or sold out: the dot turns black
          $note = '';
          if ($stock === 0) {
              $note = ' · out of stock';
          } elseif ($flagged) {
              $note = ' · low';
          }
          ?>
          <tr>
            <td class="td">
              <img class="h-[58px] w-11 bg-soft object-cover" src="<?= url($product['image'] ?: 'assets/img/placeholder.svg') ?>" alt="">
            </td>
            <td class="td">
              <a class="underline" href="<?= url('admin/product_edit.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a>
            </td>
            <td class="td"><?= e($product['category_name']) ?></td>
            <td class="td"><?= money($product['price']) ?></td>
            <td class="td">
              <span class="pill">
                <span class="inline-block h-[7px] w-[7px] rounded-full" style="background:<?= $flagged ? '#000' : '#bdbdbd' ?>"></span>
                <?= $stock ?> units<?= $note ?>
              </span>
            </td>
            <td class="td"><?= ucfirst(e($product['status'])) ?></td>
            <td class="td">
              <a class="cap text-[11px]" href="<?= url('admin/product_edit.php?id=' . $product['id']) ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?>
          <tr><td class="td text-muted" colspan="7">No products found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<span class="text-xs text-muted">Showing <?= count($products) ?> products</span>
