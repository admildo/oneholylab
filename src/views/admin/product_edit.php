<?php use Holy\{Csrf, View}; /** @var array $p @var array $categories @var array $variants @var array $images @var array $errors */
$thr = config('low_stock_threshold');
?>
<div class="flex flex-wrap items-end justify-between gap-3">
  <div class="flex flex-col">
    <a class="cap text-muted" href="<?= url('admin/products.php') ?>">Back to products</a>
    <h1 class="cap mt-1 text-[15px] font-medium"><?= $p['id'] ? 'Edit product' : 'Add product' ?></h1>
  </div>
  <button class="btn" type="submit" form="product-form">Save product</button>
</div>

<?php View::partial('form_errors', ['errors' => $errors]); ?>

<form id="product-form" method="post" enctype="multipart/form-data" class="grid items-start gap-6 lg:grid-cols-[2fr_1fr]">
  <?= Csrf::field() ?>

  <div class="flex flex-col gap-6">

    <!-- Details -->
    <section class="panel">
      <div class="flex flex-col gap-5 p-4">
        <div class="field">
          <label for="n">Name</label>
          <input id="n" name="name" class="input" required value="<?= e($p['name']) ?>">
        </div>

        <div class="field">
          <label for="d">Description</label>
          <textarea id="d" name="description" rows="4" class="w-full border border-black p-2.5"><?= e($p['description']) ?></textarea>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
          <div class="field">
            <label for="pr">Price (<?= e(config('currency')) ?>)</label>
            <input id="pr" name="price" class="input" inputmode="decimal" required value="<?= e($p['price']) ?>">
          </div>
          <div class="field">
            <label for="co">Colour</label>
            <input id="co" name="colour" class="input" value="<?= e($p['colour']) ?>">
          </div>
          <div class="field">
            <label for="ca">Category</label>
            <select id="ca" name="category_id" class="box-input w-full" required>
              <option value="">Choose...</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= (int) $p['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="st">Status</label>
            <select id="st" name="status" class="box-input w-full">
              <?php foreach (['draft' => 'Draft (hidden)', 'published' => 'Published', 'archived' => 'Archived (hidden)'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $p['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </section>

    <!-- Sizes and stock -->
    <section class="panel">
      <div class="panel-h"><h2 class="cap text-xs font-medium">Sizes and stock</h2></div>
      <div class="overflow-x-auto">
        <table class="w-full min-w-[420px] border-collapse">
          <thead>
            <tr>
              <th class="th">Size</th>
              <th class="th">SKU</th>
              <th class="th">Stock</th>
              <th class="th"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($variants as $variant): ?>
              <tr>
                <td class="td"><?= e($variant['size']) ?></td>
                <td class="td text-muted"><?= e($variant['sku']) ?></td>
                <td class="td">
                  <input type="number" min="0" name="stock[<?= $variant['id'] ?>]" value="<?= (int) $variant['stock'] ?>"
                         class="box-input w-24" aria-label="Stock for size <?= e($variant['size']) ?>">
                </td>
                <td class="td text-xs text-muted">
                  <?php if ($variant['stock'] == 0): ?>
                    Out of stock
                  <?php elseif ($variant['stock'] <= $thr): ?>
                    Low stock
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>

            <!-- A blank row for adding a size -->
            <tr>
              <td class="td"><input name="new_size" class="box-input w-24" placeholder="e.g. M" aria-label="New size"></td>
              <td class="td text-muted">Added on save</td>
              <td class="td"><input type="number" min="0" name="new_stock" class="box-input w-24" placeholder="0" aria-label="Stock for new size"></td>
              <td class="td text-xs text-muted">New size</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <!-- Photos -->
  <aside class="flex flex-col gap-6">
    <section class="panel">
      <div class="panel-h"><h2 class="cap text-xs font-medium">Photos</h2></div>
      <div class="flex flex-col gap-3 p-4">
        <?php foreach ($images as $image): ?>
          <div class="flex items-center gap-3">
            <img class="h-[88px] w-[66px] bg-soft object-cover" src="<?= url($image['path']) ?>" alt="">
            <button class="cap text-[11px] underline" name="delete_image" value="<?= $image['id'] ?>" formnovalidate data-confirm-btn="Remove this photo?">Remove</button>
          </div>
        <?php endforeach; ?>

        <div class="field">
          <label for="im">Add a photo (JPG, PNG or WebP, max 5 MB)</label>
          <input id="im" type="file" name="image" accept="image/jpeg,image/png,image/webp">
        </div>
      </div>
    </section>
  </aside>
</form>
