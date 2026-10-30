<?php
declare(strict_types=1);

namespace Holy;

/** Products, their variants (sizes with stock) and images. */
final class ProductRepository
{
    /**
     * Shop listing. Filters: q (search text), category (slug), size, sort, onlyPublished, lowStock.
     * Each row has the first image and total stock.
     */
    public static function search(array $f = [], bool $onlyPublished = true): array
    {
        $where = [];
        $p = [];
        if ($onlyPublished) {
            $where[] = "p.status = 'published'";
        }
        if (($f['q'] ?? '') !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR v.sku LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($p, $like, $like, $like);
        }
        if (($f['category'] ?? '') !== '') {
            $where[] = 'c.slug = ?';
            $p[] = $f['category'];
        }
        if (($f['size'] ?? '') !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants sv WHERE sv.product_id = p.id AND sv.size = ? AND sv.stock > 0)';
            $p[] = $f['size'];
        }
        $having = !empty($f['lowStock']) ? 'HAVING stock <= ' . (int) config('low_stock_threshold') : '';
        $order = match ($f['sort'] ?? 'newest') {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            default      => 'p.created_at DESC, p.id DESC',
        };
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                       (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_id = p.id) AS stock,
                       (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order, id LIMIT 1) AS image,
                       (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order, id LIMIT 1 OFFSET 1) AS image2
                FROM products p
                JOIN categories c ON c.id = p.category_id
                LEFT JOIN product_variants v ON v.product_id = p.id
                " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
                GROUP BY p.id $having ORDER BY $order";
        return Database::all($sql, $p);
    }

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?',
            [$id]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = Database::one("SELECT id FROM products WHERE slug = ? AND status = 'published'", [$slug]);
        return $row ? self::find((int) $row['id']) : null;
    }

    public static function variants(int $productId): array
    {
        return Database::all(
            "SELECT * FROM product_variants WHERE product_id = ?
             ORDER BY FIELD(size, 'XS','S','M','L','XL','XXL'), size",
            [$productId]
        );
    }

    public static function images(int $productId): array
    {
        return Database::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$productId]);
    }

    public static function related(int $productId, int $categoryId, int $limit = 4): array
    {
        return Database::all(
            "SELECT p.*, (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order, id LIMIT 1) AS image,
                       (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order, id LIMIT 1 OFFSET 1) AS image2
             FROM products p WHERE p.status = 'published' AND p.id <> ?
             ORDER BY (p.category_id = ?) DESC, p.created_at DESC LIMIT $limit",
            [$productId, $categoryId]
        );
    }

    public static function variant(int $variantId): ?array
    {
        return Database::one(
            'SELECT v.*, p.name AS product_name, p.price, p.slug, p.status,
                    (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order, id LIMIT 1) AS image
             FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = ?',
            [$variantId]
        );
    }

    /** Create or update a product. Returns the product id. */
    public static function save(?int $id, array $d): int
    {
        $slug = slugify($d['name']);
        if ($id === null) {
            return Database::insert(
                'INSERT INTO products (category_id, name, slug, description, price, colour, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$d['category_id'], $d['name'], self::uniqueSlug($slug, null), $d['description'], $d['price'], $d['colour'] ?: null, $d['status']]
            );
        }
        Database::run(
            'UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?, price = ?, colour = ?, status = ? WHERE id = ?',
            [$d['category_id'], $d['name'], self::uniqueSlug($slug, $id), $d['description'], $d['price'], $d['colour'] ?: null, $d['status'], $id]
        );
        return $id;
    }

    private static function uniqueSlug(string $slug, ?int $ignoreId): string
    {
        $candidate = $slug;
        $n = 2;
        while (Database::value('SELECT id FROM products WHERE slug = ? AND id <> ?', [$candidate, $ignoreId ?? 0])) {
            $candidate = $slug . '-' . $n++;
        }
        return $candidate;
    }

    public static function addVariant(int $productId, string $size, int $stock, string $sku): int
    {
        return Database::insert('INSERT INTO product_variants (product_id, sku, size, stock) VALUES (?, ?, ?, ?)', [$productId, $sku, $size, $stock]);
    }

    public static function addImage(int $productId, string $path, string $alt): void
    {
        $next = (int) Database::value('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = ?', [$productId]);
        Database::run('INSERT INTO product_images (product_id, path, alt, sort_order) VALUES (?, ?, ?, ?)', [$productId, $path, $alt, $next]);
    }

    public static function deleteImage(int $imageId, int $productId): void
    {
        Database::run('DELETE FROM product_images WHERE id = ? AND product_id = ?', [$imageId, $productId]);
    }
}
