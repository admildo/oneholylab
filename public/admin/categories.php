<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, AuditLog, CategoryRepository, Csrf, View};

Auth::requireAdmin();
if (is_post()) {
    Csrf::check();
    $name = input('name');
    $id = (int) input('id');
    // "Hoodies" and "hoodies!" would share a slug, which the database refuses.
    if ($name !== '' && in_array(input('action'), ['create', 'rename'], true) && CategoryRepository::nameTaken($name, $id)) {
        flash('A category with that name already exists.');
        redirect('admin/categories.php');
    }
    switch (input('action')) {
        case 'create':
            if ($name !== '') { CategoryRepository::create($name); AuditLog::record('create', 'category', null, $name); flash('Category added.'); }
            break;
        case 'rename':
            if ($name !== '') { CategoryRepository::rename($id, $name); AuditLog::record('update', 'category', $id, $name); flash('Category renamed.'); }
            break;
        case 'delete':
            if (CategoryRepository::delete($id)) { AuditLog::record('delete', 'category', $id); flash('Category deleted.'); }
            else { flash('That category still has products. Move them first.'); }
            break;
    }
    redirect('admin/categories.php');
}
View::render('admin/categories', ['title' => 'Categories', 'active' => 'categories.php', 'categories' => CategoryRepository::all()], 'admin');
