<?php
declare(strict_types=1);

namespace Holy;

final class CategoryRepository
{
    public static function all(): array
    {
        return Database::all('SELECT * FROM categories ORDER BY name');
    }

    /** True when another category already uses this name's slug. Slugs must be unique. */
    public static function nameTaken(string $name, int $ignoreId = 0): bool
    {
        return (bool) Database::value('SELECT id FROM categories WHERE slug = ? AND id <> ?', [slugify($name), $ignoreId]);
    }

    public static function create(string $name): int
    {
        return Database::insert('INSERT INTO categories (name, slug) VALUES (?, ?)', [$name, slugify($name)]);
    }

    public static function rename(int $id, string $name): void
    {
        Database::run('UPDATE categories SET name = ?, slug = ? WHERE id = ?', [$name, slugify($name), $id]);
    }

    public static function delete(int $id): bool
    {
        if ((int) Database::value('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id]) > 0) {
            return false; // still in use
        }
        Database::run('DELETE FROM categories WHERE id = ?', [$id]);
        return true;
    }
}
