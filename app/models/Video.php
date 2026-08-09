<?php
/**
 * XPOSED — Video model (cached YouTube uploads).
 */

class Video
{
    /** Latest N videos for the homepage rail (main uploads only). */
    public static function latest(int $limit = 5): array
    {
        $st = db()->prepare(
            'SELECT * FROM videos WHERE is_short = 0 ORDER BY published_at DESC, position ASC LIMIT ?'
        );
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    /** Paginated list for /videos archive (main uploads only). */
    public static function paginate(int $page = 1, int $perPage = 12): array
    {
        $offset = max(0, $page - 1) * $perPage;
        $total = (int)db()->query('SELECT COUNT(*) FROM videos WHERE is_short = 0')->fetchColumn();

        $st = db()->prepare(
            'SELECT * FROM videos WHERE is_short = 0 ORDER BY published_at DESC, position ASC LIMIT ? OFFSET ?'
        );
        $st->execute([$perPage, $offset]);
        $items = $st->fetchAll();

        return [
            'items'    => $items,
            'total'    => $total,
            'pages'    => max(1, (int)ceil($total / $perPage)),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM videos WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** Upsert one video from the YouTube sync. Returns id. */
    public static function upsertFromYoutube(array $v): int
    {
        $db = db();
        $st = $db->prepare(
            'INSERT INTO videos (youtube_id, title, description, thumb, duration,
                                 view_count, published_at, position, is_short)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE title = VALUES(title),
                                     description = VALUES(description),
                                     thumb = VALUES(thumb),
                                     duration = VALUES(duration),
                                     view_count = VALUES(view_count),
                                     published_at = VALUES(published_at),
                                     position = VALUES(position),
                                     is_short = VALUES(is_short)'
        );
        $st->execute([
            $v['youtube_id'] ?? '',
            $v['title'] ?? '',
            $v['description'] ?? '',
            $v['thumb'] ?? '',
            $v['duration'] ?? '',
            (int)($v['view_count'] ?? 0),
            $v['published_at'] ?? now(),
            (int)($v['position'] ?? 0),
            (int)($v['is_short'] ?? 0),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $st = db()->prepare('DELETE FROM videos WHERE id = ?');
        $st->execute([$id]);
    }

    public static function count(): int
    {
        return (int)db()->query('SELECT COUNT(*) FROM videos')->fetchColumn();
    }
}
