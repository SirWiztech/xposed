<?php
/**
 * XPOSED — Friendly fatal-error rendering.
 *
 * Converts uncaught run-time failures into a styled, actionable page instead
 * of a bare 500. Connection details are only shown when the app is in debug
 * mode; everyone else gets safe, step-by-step fix guidance.
 */

function render_fatal_error(Throwable $e): void
{
    // Discard anything already sent so the error page is clean.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (http_response_code() < 400) {
        http_response_code(500);
    }

    $msg  = $e->getMessage();
    $isDb = stripos($msg, 'database connection failed') === 0
        || $e instanceof PDOException;

    $title = $isDb ? 'Database unavailable' : 'Something went wrong';
    $lead  = $isDb
        ? 'The site could not connect to its database, so this page can’t load yet.'
        : 'An unexpected error stopped this page from loading.';

    // Only leak internal details in debug mode.
    $det = '';
    if (is_debug()) {
        $det = '<pre>' . htmlspecialchars($msg . "\n\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    }

    $tips = '';
    if ($isDb) {
        $tips = '<ol>'
            . '<li>Open the server’s <code>.env</code> at the docroot (e.g. <code>/app/.env</code>).</li>'
            . '<li>Set <code>XPOSED_DB_HOST</code> (or leave it unset so the injected '
            . '<code>DB_HOST</code> on Wasmer is used) to the MySQL host from your hosting '
            . 'dashboard — <strong>not</strong> 127.0.0.1.</li>'
            . '<li>Check <code>XPOSED_DB_PORT</code> / <code>DB_PORT</code>, and that the '
            . '<code>DB_NAME</code> / <code>DB_USER</code> pair matches the database.</li>'
            . '<li>Confirm the database has been imported once from <code>database.sql</code> (utf8mb4).</li>'
            . '</ol>';
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Xposed — ' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><style>'
        . 'body{margin:0;background:#0A0A0B;color:#F5F5F5;font-family:Inter,system-ui,-apple-system,sans-serif;display:grid;place-items:center;min-height:100vh}'
        . '.card{max-width:680px;margin:24px;padding:40px;background:#141416;border:1px solid rgba(225,6,0,.35);border-radius:8px}'
        . 'h1{font-family:Anton,Arial,sans-serif;letter-spacing:.06em;text-transform:uppercase;color:#E10600;margin:0 0 10px}'
        . 'p{line-height:1.6;color:#c8c8cc}'
        . 'code{background:#1F1F22;border:1px solid rgba(255,255,255,.12);padding:2px 6px;border-radius:4px;color:#fff}'
        . 'ol{color:#c8c8cc;line-height:1.9}'
        . 'pre{background:#000;border:1px solid rgba(255,255,255,.1);padding:14px;overflow:auto;border-radius:6px;color:#9fe;font-size:.8rem}'
        . 'a{color:#E10600}</style></head><body><div class="card">'
        . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
        . '<p>' . htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') . '</p>'
        . ($tips !== '' ? $tips : '')
        . $det
        . '<p>Still stuck? Email <a href="mailto:businessxposed@gmail.com">businessxposed@gmail.com</a>.</p>'
        . '</div></body></html>';
}