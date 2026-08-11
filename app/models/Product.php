<?php
/**
 * XPOSED — Product model.
 */

class Product
{
    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** Admin-only lookup that includes hidden/inactive products. */
    public static function findAny(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM products WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $st = db()->prepare('SELECT * FROM products WHERE slug = ? AND active = 1');
        $st->execute([$slug]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function featured(int $limit = 3): array
    {
        $st = db()->prepare(
            'SELECT * FROM products WHERE active = 1 ORDER BY featured DESC, id DESC LIMIT ?'
        );
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public static function all(array $filter = []): array
    {
        $sql = 'SELECT * FROM products WHERE active = 1';
        $params = [];
        if (!empty($filter['category'])) {
            $sql .= ' AND category = ?';
            $params[] = $filter['category'];
        }
        $sql .= ' ORDER BY category ASC, id DESC';
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function categories(): array
    {
        return db()->query('SELECT DISTINCT category FROM products WHERE active = 1 AND category <> \'\' ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function variants(int $productId): array
    {
        $st = db()->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort ASC');
        $st->execute([$productId]);
        return $st->fetchAll();
    }

    public static function create(array $p): int
    {
        $db = db();
        $st = $db->prepare(
            'INSERT INTO products (name, slug, description, price_cents, currency, image,
                                   category, type, featured, active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $p['name'],
            $p['slug'],
            $p['description'] ?? '',
            (int)$p['price_cents'],
            $p['currency'] ?? 'CAD',
            $p['image'] ?? '',
            $p['category'] ?? '',
            $p['type'] ?? 'apparel',
            (int)($p['featured'] ?? 0),
            (int)($p['active'] ?? 1),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $p): void
    {
        $st = db()->prepare(
            'UPDATE products SET name = ?, slug = ?, description = ?, price_cents = ?,
                    currency = ?, image = ?, category = ?, type = ?, featured = ?, active = ?
             WHERE id = ?'
        );
        $st->execute([
            $p['name'],
            $p['slug'],
            $p['description'] ?? '',
            (int)$p['price_cents'],
            $p['currency'] ?? 'CAD',
            $p['image'] ?? '',
            $p['category'] ?? '',
            $p['type'] ?? 'apparel',
            (int)($p['featured'] ?? 0),
            (int)($p['active'] ?? 1),
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        db()->prepare('DELETE FROM product_variants WHERE product_id = ?')->execute([$id]);
    }

    public static function saveVariants(int $productId, array $names): void
    {
        db()->prepare('DELETE FROM product_variants WHERE product_id = ?')->execute([$productId]);
        $st = db()->prepare('INSERT INTO product_variants (product_id, name) VALUES (?, ?)');
        $sort = 0;
        foreach ($names as $n) {
            if (trim((string)$n) === '') continue;
            $st->execute([$productId, trim((string)$n)]);
            $sort++;
        }
    }

    public static function count(): int
    {
        return (int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }
}