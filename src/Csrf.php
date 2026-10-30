<?php
declare(strict_types=1);

namespace Holy;

/** Form protection. Put Csrf::field() in every POST form and call Csrf::check() first thing. */
final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function check(): void
    {
        $sent = $_POST['_csrf'] ?? '';
        if (!is_string($sent) || !hash_equals(self::token(), $sent)) {
            http_response_code(419);
            exit('Your session expired. Go back, refresh the page and try again.');
        }
    }
}
