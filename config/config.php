<?php
// Default settings. Override anything in config/config.local.php (not committed)
// or with environment variables of the same name.
$env = static fn(string $k, $default) => getenv($k) !== false ? getenv($k) : $default;

$config = [
    'app_env'   => $env('APP_ENV', 'production'),          // 'development' shows errors
    // The folder the site sits in, e.g. '' at a domain root or for `php -S`, '/oneholylab' in XAMPP.
    // Set it in config/config.local.php (see config.local.example.php).
    'base_url'  => rtrim($env('BASE_URL', ''), '/'),
    'db' => [
        'host'    => $env('DB_HOST', '127.0.0.1'),
        'name'    => $env('DB_NAME', 'oneholylab'),
        'user'    => $env('DB_USER', 'root'),
        'pass'    => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'currency'  => 'R',
    'free_delivery_over' => 1000.00,
    'delivery_methods' => [
        'standard'   => ['label' => 'Standard courier',        'note' => '2 to 5 working days', 'fee' => 100.00],
        'express'    => ['label' => 'Express courier',         'note' => 'Next working day in main cities', 'fee' => 150.00],
        'collection' => ['label' => 'Collect from pick-up point', 'note' => 'Ready in 3 working days', 'fee' => 0.00],
    ],
    'low_stock_threshold' => 5,
    'payment_provider' => $env('PAYMENT_PROVIDER', 'demo'), // see src/PaymentGateway.php
    'mail_from' => $env('MAIL_FROM', 'orders@oneholylab.test'),
    'mail_transport' => $env('MAIL_TRANSPORT', 'log'),      // 'log' writes to storage/mail.log, 'mail' uses PHP mail()
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}
return $config;
