<?php
/**
 * XPOSED — Global helpers
 */

function config(?string $key = null, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../../config/config.php';
    }
    if ($key === null) {
        return $cfg;
    }
    $parts = explode('.', $key);
    $cur = $cfg;
    foreach ($parts as $p) {
        if (!is_array($cur) || !array_key_exists($p, $cur)) {
            return $default;
        }
        $cur = $cur[$p];
    }
    return $cur;
}

/** HTML-escape output. */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Build a site-relative URL from the configured base. */
function url(string $path = ''): string
{
    $base = rtrim(config('app.base_url'), '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function fmt_number(int $n): string
{
    if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 2, '.', ''), '0'), '.') . 'M';
    if ($n >= 1000) return rtrim(rtrim(number_format($n / 1000, 1, '.', ''), '0'), '.') . 'K';
    return (string)$n;
}

function money(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/** Return true when the environment renders errors. */
function is_debug(): bool
{
    return (bool)config('app.debug');
}

/**
 * Read a settings row.
 * @param string $key
 * @param mixed  $default
 */
function setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        try {
            $cache = [];
            foreach (db()->query('SELECT skey, svalue FROM settings') as $row) {
                $cache[$row['skey']] = $row['svalue'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function setting_set(string $key, string $value): void
{
    $st = db()->prepare(
        'INSERT INTO settings (skey, svalue) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
    );
    $st->execute([$key, $value]);
}

/**
 * Inline single-color line icon set (currentColor-driven).
 * Shared across all pages (navbar, footer, buttons) so the family stays consistent.
 */
function icon(string $name, string $class = 'icon'): void
{
    $icons = [
        'home'       => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h6v-6h4v6h6V10"/>',
        'play'       => '<polygon points="7,4 20,12 7,20"/>',
        'film'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><polygon points="10,9 15,12 10,15" fill="currentColor" stroke="none"/>',
        'arrow-right'=> '<line x1="4" y1="12" x2="19" y2="12"/><polyline points="13,6 19,12 13,18"/>',
        'arrow-left'=> '<line x1="20" y1="12" x2="5" y2="12"/><polyline points="11,18 5,12 11,6"/>',
        'external'   => '<path d="M7 17L17 7"/><polyline points="8,7 17,7 17,16"/>',
        'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3,6 12,13 21,6"/>',
        'users'      => '<path d="M8 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/><path d="M2.5 20c.7-3.4 3-5.4 5.5-5.4s4.8 2 5.5 5.4"/><path d="M15.5 8a3 3 0 1 1 0-6"/><path d="M14.5 14.2c2.2.4 4 2.2 4.6 5.3"/>',
        'eye'        => '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="3" x2="8" y2="7"/><line x1="16" y1="3" x2="16" y2="7"/>',
        'dot-live'   => '<circle cx="12" cy="12" r="5" fill="currentColor" stroke="none"/>',
        'trophy'     => '<path d="M8 4h8v4a4 4 0 0 1-8 0V4z"/><path d="M8 5H4a3 3 0 0 0 4 4"/><path d="M16 5h4a3 3 0 0 1-4 4"/><line x1="12" y1="12" x2="12" y2="17"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
        'chip'       => '<rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="4" x2="12" y2="7"/><line x1="12" y1="17" x2="12" y2="20"/><line x1="4" y1="12" x2="7" y2="12"/><line x1="17" y1="12" x2="20" y2="12"/>',
        'bag'        => '<path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8a3 3 0 0 1 6 0"/>',
        'note'       => '<path d="M5 3h14v18l-3-2-3 2-3-2-3 2V3z"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/>',
        'link'       => '<path d="M10 14a4 4 0 0 0 6 0l3-3a4 4 0 0 0-6-6l-1 1"/><path d="M14 10a4 4 0 0 0-6 0l-3 3a4 4 0 0 0 6 6l1-1"/>',
        'tv'         => '<rect x="3" y="6" width="18" height="12" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="18" x2="12" y2="21"/>',
        'check'      => '<polyline points="4,12 9,17 20,5"/>',
        'shield'     => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        'chart'      => '<line x1="4" y1="20" x2="20" y2="20"/><rect x="6" y="12" width="3" height="6"/><rect x="11" y="7" width="3" height="11"/><rect x="16" y="10" width="3" height="8"/>',
        'radio'      => '<rect x="3" y="6" width="18" height="14" rx="2"/><circle cx="12" cy="13" r="2.5"/><line x1="7" y1="9" x2="9" y2="9"/><line x1="17" y1="9" x2="17" y2="9"/>',
        'kick'       => '<path d="M4 4v16"/><path d="M9 4v6l7-6h4l-8 7 8 9h-4l-7-6v6"/>',
        'twitch'     => '<path d="M5 4h15v10l-4 4h-4l-3 3v-3H5V4z"/><line x1="11" y1="8" x2="11" y2="13"/><line x1="15.5" y1="8" x2="15.5" y2="13"/>',
        'youtube'    => '<rect x="3" y="5.5" width="18" height="13" rx="4"/><polygon points="10,9.5 15,12 10,14.5" fill="currentColor" stroke="none"/>',
        'tiktok'     => '<path d="M14 4v10.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 4c.4 2.4 2 4 4.5 4.3"/>',
        'x'          => '<line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/>',
        'instagram'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/>',
        'chat'       => '<path d="M4 4h16v12H8l-4 4V4z"/>',
        'calc'       => '<rect x="4" y="3" width="16" height="18" rx="2"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="12" y2="15"/>',
        'wheel'      => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/><line x1="12" y1="12" x2="12" y2="3"/><line x1="12" y1="12" x2="12" y2="21"/><line x1="12" y1="12" x2="3" y2="12"/><line x1="12" y1="12" x2="21" y2="12"/><line x1="12" y1="12" x2="5.6" y2="5.6"/><line x1="12" y1="12" x2="18.4" y2="5.6"/><line x1="12" y1="12" x2="5.6" y2="18.4"/><line x1="12" y1="12" x2="18.4" y2="18.4"/>',
        'bank'       => '<path d="M3 10l9-6 9 6"/><line x1="4" y1="10" x2="20" y2="10"/><rect x="6" y1="10" width="12" height="10"/><line x1="9" y1="13" x2="9" y2="17"/><line x1="12" y1="13" x2="12" y2="17"/><line x1="15" y1="13" x2="15" y2="17"/>',
        'grid'       => '<rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/>',
    ];
    if (!isset($icons[$name])) {
        return;
    }
    echo '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
}
