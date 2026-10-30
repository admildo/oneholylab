<?php
declare(strict_types=1);

// Small view and request helpers available everywhere.

function config(string $path, $default = null)
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $path) as $key) {
        if (!is_array($value) || !array_key_exists($key, $value)) {
            return $default;
        }
        $value = $value[$key];
    }
    return $value;
}

/** Escape text for HTML. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money($amount): string
{
    return config('currency') . ' ' . number_format((float) $amount, 2, '.', ' ');
}

/** Absolute path on this site, e.g. url('shop.php'). */
function url(string $path = ''): string
{
    return config('base_url') . '/' . ltrim($path, '/');
}

/** URL of a file in public/ with its change time added, so browsers fetch it again after an update. */
function asset(string $path): string
{
    $file = ROOT . '/public/' . ltrim($path, '/');
    return url($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function flash(?string $message = null, string $type = 'info'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/** Trimmed string from POST or GET. */
function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function slugify(string $text): string
{
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return $text !== '' ? $text : 'item';
}
