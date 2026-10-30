<?php
require __DIR__ . '/../../src/bootstrap.php';

use Holy\{Auth, Csrf};

if (is_post()) {
    Csrf::check();
    Auth::logout();
}
redirect('admin/login.php');
