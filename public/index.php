<?php
require __DIR__ . '/../src/bootstrap.php';

use Holy\{Database, ProductRepository, View};

$new = array_slice(ProductRepository::search(['sort' => 'newest']), 0, 4);
$tiles = Database::all(
    "SELECT c.name, c.slug, (SELECT pi.path FROM products p JOIN product_images pi ON pi.product_id = p.id
                             WHERE p.category_id = c.id AND p.status = 'published' ORDER BY p.created_at DESC, pi.sort_order LIMIT 1) AS image
     FROM categories c ORDER BY c.id"
);
$hero = ProductRepository::search(['category' => 'hoodies-and-sweats', 'sort' => 'newest'])[0] ?? $new[0] ?? [];
// Story block: the Psalm 23 tee if it is on sale, otherwise any product with a photo.
$all = ProductRepository::search();
$feature = current(array_filter($all, fn ($p) => $p['slug'] === 'psalm-23-boxy-tee' && $p['image']))
    ?: (current(array_filter($all, fn ($p) => (bool) $p['image'])) ?: null);
View::render('shop/home', ['title' => 'oneholylab', 'new' => $new, 'hero' => $hero, 'tiles' => $tiles, 'feature' => $feature]);
