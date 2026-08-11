<?php
/**
 * XPOSED — Blog post model.
 */

class Post
{
    public static function latest(int $limit = 3): array
    {
        $st = db()->prepare(
            'SELECT * FROM posts WHERE status = \'published\' AND published_at <= NOW()
             ORDER BY published_at DESC LIMIT ?'
        );
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public static function paginate(int $page = 1, int $perPage = 9): array
    {
        $offset = max(0, $page - 1) * $perPage;
        $total = (int)db()->query(
            'SELECT COUNT(*) FROM posts WHERE status = \'published\' AND published_at <= NOW()'
        )->fetchColumn();

        $st = db()->prepare(
            'SELECT * FROM posts WHERE status = \'published\' AND published_at <= NOW()
             ORDER BY published_at DESC LIMIT ? OFFSET ?'
        );
        $st->execute([$perPage, $offset]);

        return [
            'items'    => $st->fetchAll(),
            'total'    => $total,
            'pages'    => max(1, (int)ceil($total / $perPage)),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    public static function findBySlug(string $slug): ?array
    {
        $st = db()->prepare(
            'SELECT * FROM posts WHERE slug = ? AND status = \'published\' AND published_at <= NOW()'
        );
        $st->execute([$slug]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM posts WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function create(array $p): int
    {
        $db = db();
        $st = $db->prepare(
            'INSERT INTO posts (title, slug, excerpt, cover_image, body, status, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $p['title'],
            $p['slug'],
            $p['excerpt'] ?? '',
            $p['cover_image'] ?? '',
            $p['body'] ?? '',
            $p['status'] ?? 'published',
            $p['published_at'] ?? now(),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $p): void
    {
        $st = db()->prepare(
            'UPDATE posts SET title = ?, slug = ?, excerpt = ?, cover_image = ?,
                    body = ?, status = ?, published_at = ?
             WHERE id = ?'
        );
        $st->execute([
            $p['title'],
            $p['slug'],
            $p['excerpt'] ?? '',
            $p['cover_image'] ?? '',
            $p['body'] ?? '',
            $p['status'] ?? 'published',
            $p['published_at'] ?? now(),
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $st = db()->prepare('DELETE FROM posts WHERE id = ?');
        $st->execute([$id]);
    }

    public static function count(): int
    {
        return (int)db()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    /** Lightweight Markdown-ish renderer for admin-authored bodies. */
    public static function renderBody(string $body): string
    {
        $body = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $body);
        $body = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $body);
        $body = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $body);
        $body = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $body);
        $body = preg_replace('/`(.+?)`/', '<code>$1</code>', $body);
        $body = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $body);
        $body = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $body);
        $body = preg_replace('/(?<!\n)\n(?!\n)/', "</p>\n<p>", $body);
        $body = '<p>' . $body . '</p>';
        $body = preg_replace('/<p><\/p>/', '', $body);
        $body = preg_replace('/<\/ul>\n<p>(?=<)/', '</ul>', $body);
        $body = preg_replace('/<h([23])><\/p>/', '<h$1>', $body);
        return $body;
    }
}
