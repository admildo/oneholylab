<?php
// Fills an empty database with demo data and creates the first admin.
// Usage:  php sql/seed.php [admin-email] [admin-password]
// Run it once, after importing sql/schema.sql. Delete the demo data before going live.
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

use Holy\{CategoryRepository, Database, ProductRepository, UserRepository};

if (PHP_SAPI !== 'cli') {
    exit('Run this from the command line.');
}
if (Database::value('SELECT COUNT(*) FROM users') > 0) {
    exit("Database is not empty. Re-import sql/schema.sql first if you want to start over.\n");
}

$adminEmail = $argv[1] ?? 'admin@oneholylab.test';
$adminPass  = $argv[2] ?? bin2hex(random_bytes(5));

UserRepository::create(['first_name' => 'Store', 'last_name' => 'Admin', 'email' => $adminEmail, 'password' => $adminPass], 'admin');
$customer = UserRepository::create(['first_name' => 'Thandi', 'last_name' => 'Mokoena', 'email' => 'thandi@example.com', 'password' => 'password123']);
UserRepository::saveAddress($customer, ['line1' => '12 Grace Street', 'city' => 'Johannesburg', 'province' => 'Gauteng', 'postal_code' => '2001', 'country' => 'South Africa']);

$cats = [];
foreach (['Tees', 'Hoodies and sweats', 'Accessories'] as $name) {
    $cats[$name] = CategoryRepository::create($name);
}

// name, category, price, colour, image, sizes (size => stock), description
$products = [
    ['John 3:16 Oversized Hoodie', 'Hoodies and sweats', 890, 'Black', 'john-316-hoodie', ['S' => 6, 'M' => 3, 'L' => 12, 'XL' => 0], 'Heavyweight 400gsm brushed cotton hoodie with a dropped shoulder, kangaroo pocket and a tonal John 3:16 print across the chest. For God so loved the world.'],
    ['Saved by Grace Heavyweight Tee', 'Tees', 450, 'White', 'saved-by-grace-tee', ['S' => 10, 'M' => 12, 'L' => 8, 'XL' => 4], 'Boxy heavyweight cotton tee with Ephesians 2:8 printed on the chest.'],
    ['Faith Over Fear Crewneck', 'Hoodies and sweats', 750, 'Grey', 'faith-over-fear-crewneck', ['S' => 5, 'M' => 6, 'L' => 6, 'XL' => 0], 'Soft brushed crewneck sweatshirt printed with Isaiah 41:10.'],
    ['Chosen Zip Hoodie', 'Hoodies and sweats', 950, 'Stone', 'chosen-zip-hoodie', ['S' => 1, 'M' => 2, 'L' => 1, 'XL' => 0], 'Full-zip hoodie in stone with a small Chosen print. Limited run.'],
    ['Psalm 23 Boxy Tee', 'Tees', 450, 'Black', 'psalm-23-boxy-tee', ['S' => 8, 'M' => 10, 'L' => 10, 'XL' => 0], 'Black boxy tee with the opening of Psalm 23 across the chest.'],
    ['Light of the World Long Sleeve', 'Tees', 550, 'White', 'light-of-the-world-long-sleeve', ['S' => 4, 'M' => 6, 'L' => 5, 'XL' => 2], 'Long sleeve tee printed with Matthew 5:14.'],
    ['Holy Dad Cap', 'Accessories', 350, 'Black', 'holy-dad-cap', ['One size' => 2], 'Unstructured cotton cap with embroidered HOLY wordmark.'],
    ['Armour of God Tote', 'Accessories', 280, 'Natural', 'armour-of-god-tote', ['One size' => 40], 'Heavy canvas tote printed with Ephesians 6:11.'],
];
$n = 0;
foreach ($products as [$name, $cat, $price, $colour, $img, $sizes, $desc]) {
    $id = ProductRepository::save(null, ['category_id' => $cats[$cat], 'name' => $name, 'description' => $desc, 'price' => $price, 'colour' => $colour, 'status' => 'published']);
    // Photos are public/assets/img/products/<image>-1.jpg, -2.jpg ...; the SVG is the fallback.
    $photos = glob(ROOT . "/public/assets/img/products/$img-*.jpg") ?: [];
    sort($photos);
    foreach ($photos ?: ["$img.svg"] as $photo) {
        ProductRepository::addImage($id, 'assets/img/products/' . basename($photo), $name);
    }
    foreach ($sizes as $size => $stock) {
        ProductRepository::addVariant($id, $size, $stock, sprintf('OHL-%03d-%s', ++$n, strtoupper(str_replace(' ', '', $size))));
    }
}
Database::run('UPDATE products SET status = "draft" WHERE slug = "light-of-the-world-long-sleeve"');

echo "Seeded.\nAdmin login: $adminEmail / $adminPass\nDemo customer: thandi@example.com / password123\n";
