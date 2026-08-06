<?php
/**
 * XPOSED — Security helpers: CSRF + simple rate limiting.
 */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted CSRF token. Exits with 403 when invalid. */
function csrf_verify(): void
{
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('CSRF validation failed.');
    }
}

/** Require a POST request (early exit otherwise). */
function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

/**
 * Naive per-IP rate limit backed by the chat_messages table.
 * Returns remaining budget; exits 429 when exhausted.
 *
 * @param string $key   e.g. 'chat', 'login'
 * @param int    $limit per hour
 * @param int    $window_sec
 */
function rate_limit(string $key, int $limit, int $window_sec = 3600): int
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $db = db();

    $st = $db->prepare(
        'SELECT COUNT(*) FROM rate_limits
         WHERE rkey = ? AND ip = ? AND created_at > (NOW() - INTERVAL ? SECOND)'
    );
    $st->execute([$key, $ip, (int)$window_sec]);
    $used = (int)$st->fetchColumn();

    $st = $db->prepare('INSERT INTO rate_limits (rkey, ip, created_at) VALUES (?, ?, NOW())');
    $st->execute([$key, $ip]);

    if ($used >= $limit) {
        http_response_code(429);
        header('Content-Type: application/json');
        exit(json_encode(['ok' => false, 'error' => 'Slow down — try again in a bit.']));
    }

    return $limit - $used - 1;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
