<?php
/**
 * XPOSED — FAQ model (rules-based chat matching).
 */

class Faq
{
    public static function all(): array
    {
        return db()->query('SELECT * FROM faqs ORDER BY sort ASC, id ASC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM faqs WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function create(array $f): int
    {
        $db = db();
        $st = $db->prepare(
            'INSERT INTO faqs (question, keywords, answer, category, sort) VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([
            $f['question'],
            $f['keywords'] ?? '',
            $f['answer'],
            $f['category'] ?? 'General',
            (int)($f['sort'] ?? 0),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $f): void
    {
        $st = db()->prepare(
            'UPDATE faqs SET question = ?, keywords = ?, answer = ?, category = ?, sort = ? WHERE id = ?'
        );
        $st->execute([
            $f['question'],
            $f['keywords'] ?? '',
            $f['answer'],
            $f['category'] ?? 'General',
            (int)($f['sort'] ?? 0),
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $st = db()->prepare('DELETE FROM faqs WHERE id = ?');
        $st->execute([$id]);
    }

    public static function count(): int
    {
        return (int)db()->query('SELECT COUNT(*) FROM faqs')->fetchColumn();
    }

    /**
     * Score a free-text question against FAQ keywords.
     * Returns best [faq, score] or null when nothing clears the threshold.
     */
    public static function match(string $question): ?array
    {
        $q = mb_strtolower(trim($question));
        if ($q === '') {
            return null;
        }

        $best = null;
        $bestScore = 0.0;

        foreach (self::all() as $faq) {
            $tokens = array_values(array_filter(array_map('trim', explode(',', mb_strtolower((string)$faq['keywords'])))));
            $score = 0.0;
            foreach ($tokens as $t) {
                if ($t === '') continue;
                if ($q === $t) {
                    $score += 3.0;
                } elseif (mb_strpos($q, $t) !== false) {
                    $score += 1.5;
                } elseif (mb_strpos($t, $q) !== false) {
                    $score += 1.0;
                }
            }
            $score += mb_strpos($q, mb_strtolower($faq['question'])) !== false ? 2.0 : 0.0;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        if ($best && $bestScore >= 1.5) {
            return [$best, $bestScore];
        }
        return null;
    }
}
