<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, AuditLog, CategoryRepository, Csrf, Database, ImageUpload, ProductRepository, StockService, View};

$admin = Auth::requireAdmin();
$id = (int) input('id') ?: null;
$product = $id ? ProductRepository::find($id) : null;
if ($id && !$product) {
    redirect('admin/products.php');
}
$errors = [];

if (is_post()) {
    Csrf::check();

    // Remove one photo.
    if (input('delete_image') !== '' && $id) {
        ProductRepository::deleteImage((int) input('delete_image'), $id);
        flash('Photo removed.');
        redirect('admin/product_edit.php?id=' . $id);
    }

    $d = [
        'name' => input('name'), 'description' => input('description'), 'price' => input('price'),
        'colour' => input('colour'), 'status' => input('status'), 'category_id' => (int) input('category_id'),
    ];
    if ($d['name'] === '') { $errors[] = 'Please enter a product name.'; }
    if (!is_numeric($d['price']) || (float) $d['price'] < 0) { $errors[] = 'Please enter a valid price.'; }
    if (!in_array($d['status'], ['draft', 'published', 'archived'], true)) { $errors[] = 'Unknown status.'; }
    if (!Database::value('SELECT id FROM categories WHERE id = ?', [$d['category_id']])) { $errors[] = 'Please choose a category.'; }
    $upload = ImageUpload::save($_FILES['image'] ?? []);
    if ($upload['error']) { $errors[] = $upload['error']; }

    if (!$errors) {
        $newProduct = $id === null;
        $id = ProductRepository::save($id, $d);

        $notice = $newProduct ? 'Product created. Add sizes and stock below.' : 'Product saved.';

        // Stock for this product's sizes only, so a posted variant id from another product is ignored.
        $ownVariantIds = array_column(ProductRepository::variants($id), 'id');
        foreach ((array) ($_POST['stock'] ?? []) as $variantId => $qty) {
            if (in_array((int) $variantId, $ownVariantIds, false) && is_numeric($qty) && (int) $qty >= 0) {
                StockService::set((int) $variantId, (int) $qty, (int) $admin['id']);
            }
        }
        // One new size.
        $size = input('new_size');
        if ($size !== '') {
            try {
                ProductRepository::addVariant($id, $size, max(0, (int) input('new_stock')), sprintf('OHL-%d-%s', $id, strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size))));
            } catch (mysqli_sql_exception $e) {
                // Keep the notice that the product saved, and say why the size was not added.
                $notice .= " Size $size already exists, so it was not added.";
            }
        }
        if ($upload['path']) {
            ProductRepository::addImage($id, $upload['path'], $d['name']);
        }
        AuditLog::record($newProduct ? 'create' : 'update', 'product', $id, $d['name'] . ' (' . $d['status'] . ', ' . money($d['price']) . ')');
        flash($notice);
        redirect('admin/product_edit.php?id=' . $id);
    }
    // The form has errors and will not be saved, so drop the photo that was uploaded with it.
    if ($upload['path']) {
        unlink(ROOT . '/public/' . $upload['path']);
    }
    $product = array_merge($product ?? ['id' => null], $d);
}

View::render('admin/product_edit', [
    'title' => $product ? 'Edit product' : 'Add product', 'active' => 'products.php',
    'p' => $product ?? ['id' => null, 'name' => '', 'description' => '', 'price' => '', 'colour' => '', 'status' => 'draft', 'category_id' => 0],
    'categories' => CategoryRepository::all(),
    'variants' => $id ? ProductRepository::variants($id) : [],
    'images' => $id ? ProductRepository::images($id) : [],
    'errors' => $errors,
], 'admin');
