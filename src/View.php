<?php
declare(strict_types=1);

namespace Holy;

/** Renders src/views/<name>.php inside a layout. Pages stay thin: they fetch data, then call View. */
final class View
{
    /** @param string $layout 'shop' or 'admin' */
    public static function render(string $name, array $data = [], string $layout = 'shop'): void
    {
        $data['flash'] = flash();
        ob_start();
        extract($data, EXTR_SKIP);
        require ROOT . "/src/views/$name.php";
        $content = ob_get_clean();
        $title = $data['title'] ?? 'oneholylab';
        require ROOT . "/src/views/layout/$layout.php";
    }

    /** Include a small reusable piece, e.g. View::partial('product_card', ['p' => $row]). */
    public static function partial(string $name, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require ROOT . "/src/views/partials/$name.php";
    }
}
