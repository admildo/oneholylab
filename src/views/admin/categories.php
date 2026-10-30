<?php use Holy\Csrf; /** @var array $categories */ ?>
<h1 class="cap text-[15px] font-medium">Categories</h1>

<section class="panel max-w-xl">
  <div class="flex flex-col gap-3 p-4">

    <?php foreach ($categories as $category): ?>
      <div class="flex flex-wrap items-center gap-2">
        <form method="post" class="flex flex-1 items-center gap-2">
          <?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= $category['id'] ?>">
          <input type="hidden" name="action" value="rename">
          <input name="name" class="box-input min-w-0 flex-1" value="<?= e($category['name']) ?>" aria-label="Category name">
          <button class="chip">Rename</button>
        </form>

        <form method="post" data-confirm="Delete this category?">
          <?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= $category['id'] ?>">
          <input type="hidden" name="action" value="delete">
          <button class="chip">Delete</button>
        </form>
      </div>
    <?php endforeach; ?>

    <form method="post" class="mt-2 flex items-center gap-2 border-t border-line pt-4">
      <?= Csrf::field() ?>
      <input type="hidden" name="action" value="create">
      <input name="name" class="box-input min-w-0 flex-1" placeholder="New category name" aria-label="New category name" required>
      <button class="btn !min-h-[40px]">Add</button>
    </form>
  </div>
</section>
