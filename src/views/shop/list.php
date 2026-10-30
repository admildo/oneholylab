<?php use Holy\View; /** @var array $products @var array $categories @var array $f */
$sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

$current = null;
foreach ($categories as $category) {
    if ($category['slug'] === $f['category']) {
        $current = $category;
    }
}
$heading = $f['q'] !== '' ? 'Results for "' . $f['q'] . '"' : ($current['name'] ?? 'All products');

// Search and filter settings to keep when a category chip is clicked.
$keep = array_filter(['q' => $f['q'], 'size' => $f['size'], 'sort' => $f['sort'] !== 'newest' ? $f['sort'] : '']);
?>
<section class="mx-auto max-w-[1600px] px-4 pb-2 pt-8 md:px-6 md:pt-12" id="search">
  <nav class="cap mb-3 text-muted" aria-label="Breadcrumb">
    <a class="hover:text-black" href="<?= url() ?>">Home</a> /
    <?php if ($current): ?>
      <a class="hover:text-black" href="<?= url('shop.php') ?>">Shop</a> / <?= e($current['name']) ?>
    <?php else: ?>
      Shop
    <?php endif; ?>
  </nav>

  <div class="flex flex-wrap items-end justify-between gap-6">
    <h1 class="font-logo text-[40px] leading-none md:text-[64px]"><?= e($heading) ?></h1>
    <form method="get" action="<?= url('shop.php') ?>" class="flex w-full max-w-[420px] items-center gap-2 border-b border-black" role="search">
      <svg width="18" height="18" viewBox="0 0 20 20" aria-hidden="true" class="shrink-0"><circle cx="8.5" cy="8.5" r="6" stroke="currentColor" stroke-width="1.3" fill="none"/><path d="M13 13l5 5" stroke="currentColor" stroke-width="1.3"/></svg>
      <label for="q" class="sr-only">Search</label>
      <input id="q" name="q" type="search" class="min-h-[44px] w-full bg-transparent outline-none placeholder:text-neutral-400" value="<?= e($f['q']) ?>" placeholder="Search hoodie, John 3:16, cap...">
      <?php if ($f['category'] !== ''): ?>
        <input type="hidden" name="category" value="<?= e($f['category']) ?>">
      <?php endif; ?>
      <button class="cap shrink-0 px-2 py-2" type="submit">Search</button>
    </form>
  </div>
</section>

<div class="sticky top-16 z-30 border-b border-line bg-white/95 backdrop-blur md:top-[72px]">
  <form method="get" action="<?= url('shop.php') ?>" class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-3 px-4 py-3 md:px-6">
    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 md:mx-0 md:px-0" role="group" aria-label="Category">
      <a class="chip shrink-0 <?= $f['category'] === '' ? 'chip-on' : 'hover:border-black' ?>" href="<?= url('shop.php?' . http_build_query($keep)) ?>">All</a>
      <?php foreach ($categories as $category): ?>
        <a class="chip shrink-0 <?= $f['category'] === $category['slug'] ? 'chip-on' : 'hover:border-black' ?>" href="<?= url('shop.php?' . http_build_query($keep + ['category' => $category['slug']])) ?>"><?= e($category['name']) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="flex w-full items-center gap-2 md:w-auto md:gap-3">
      <input type="hidden" name="q" value="<?= e($f['q']) ?>">
      <input type="hidden" name="category" value="<?= e($f['category']) ?>">
      <span class="cap mr-auto whitespace-nowrap text-muted md:mr-2"><?= count($products) ?> <?= count($products) === 1 ? 'item' : 'items' ?></span>

      <label for="size" class="sr-only">Size in stock</label>
      <select id="size" name="size" class="box-input cap min-w-0" data-autosubmit>
        <option value="">Any size</option>
        <?php foreach ($sizes as $size): ?>
          <option value="<?= $size ?>" <?= $f['size'] === $size ? 'selected' : '' ?>>Size <?= $size ?></option>
        <?php endforeach; ?>
      </select>

      <label for="sort" class="sr-only">Sort</label>
      <select id="sort" name="sort" class="box-input cap min-w-0" data-autosubmit>
        <option value="newest" <?= $f['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
        <option value="price_asc" <?= $f['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
        <option value="price_desc" <?= $f['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
      </select>

      <noscript><button class="btn" type="submit">Apply</button></noscript>
    </div>
  </form>
</div>

<section class="mx-auto max-w-[1600px] px-4 pb-10 pt-8 md:px-6">
  <?php if (!$products): ?>
    <div class="flex flex-col items-center gap-3 py-24 text-center">
      <span class="font-logo text-[32px]">Nothing here yet</span>
      <span class="text-muted">No products match your search. Try another word or clear the filters.</span>
      <a class="btn btn-ghost mt-2" href="<?= url('shop.php') ?>">Clear filters</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-2 gap-x-2 gap-y-10 md:grid-cols-3 md:gap-x-4 lg:grid-cols-4">
      <?php foreach ($products as $p): View::partial('product_card', ['p' => $p]); endforeach; ?>
    </div>
  <?php endif; ?>
</section>
