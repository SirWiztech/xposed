<?php
/**
 * XPOSED — Application bootstrap
 * Require this at the top of every public entry file.
 */

declare(strict_types=1);

// Config + helpers
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/helpers/cart.php';
require_once __DIR__ . '/../app/helpers/upload.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/helpers/errors.php';

// Uncaught exceptions render as a styled, actionable page — never a bare 500.
set_exception_handler('render_fatal_error');

// Session (httponly, samesite=lax)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_name('xposed_sid');
    session_start();
}

// Error surface
error_reporting(E_ALL);
ini_set('display_errors', is_debug() ? '1' : '0');

// Header sent to all AJAX chat endpoints
if (str_ends_with($_SERVER['REQUEST_URI'] ?? '', '.php')
    || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('X-Content-Type-Options: nosniff');
}

// Simple PSR-4-ish autoload: app/models/Foo.php → class Foo
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/models/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
