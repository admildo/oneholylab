<?php
declare(strict_types=1);

// Loaded first by every page. Sets up autoloading, config, error display, the session and helpers.

define('ROOT', dirname(__DIR__));

// When code uses a class such as Holy\Auth, load its file from src/ (src/Auth.php).
spl_autoload_register(static function (string $class): void {
    $file = ROOT . '/src/' . str_replace('Holy\\', '', $class) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$GLOBALS['config'] = require ROOT . '/config/config.php';

if ($GLOBALS['config']['app_env'] === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// HttpOnly: scripts cannot read the session cookie. SameSite=Lax: other sites cannot send it with their forms.
// Secure: the cookie is sent only over HTTPS.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

require ROOT . '/src/helpers.php';
