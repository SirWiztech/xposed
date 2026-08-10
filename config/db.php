<?php
/**
 * XPOSED — PDO connection (singleton)
 * Prepared statements only. Connection failures are re-raised as a clear
 * RuntimeException and rendered by app/helpers/errors.php, never a bare 500.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = config('db');

    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException(
            'Database connection failed: the PHP pdo_mysql extension is not loaded. Enable it on the host.'
        );
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['host'],
        (int)$cfg['port'],
        $cfg['name'],
        $cfg['charset']
    );

    try {
        $pdo = new PDO($dsn, (string)$cfg['user'], (string)$cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        throw new RuntimeException('Database connection failed: ' . pdo_error_hint($e), 0, $e);
    }

    return $pdo;
}

/** Translate a PDO/MySQL error into a short human hint. */
function pdo_error_hint(PDOException $e): string
{
    $msg = (string)$e->getMessage();

    // Prefer the driver error number (errorInfo[1]); fall back to parsing the message.
    $num = (is_array($e->errorInfo) && isset($e->errorInfo[1])) ? (string)$e->errorInfo[1] : '';
    if ($num === '') {
        if (preg_match('~\[(\d+)\]~', $msg, $m)) {
            $num = $m[1];
        } elseif (preg_match('~SQLSTATE\[(\w+)\]~', $msg, $m)) {
            $num = $m[1];
        }
    }

    $map = [
        '1045' => 'access denied — check XPOSED_DB_USER / XPOSED_DB_PASS.',
        '1049' => 'unknown database — check the DB name (managed DBs are prefixed, e.g. db_<id> on Wasmer).',
        '2002' => 'host unreachable — DB host/port come from the hosting dashboard (Wasmer injects DB_HOST/DB_PORT automatically; do NOT use 127.0.0.1 on the live site).',
        '2003' => 'connection refused — check the DB host/port from the hosting dashboard.',
        'HY000' => 'host-check failed — the DB settings (host, port, user) may be wrong.',
    ];

    $hint = $map[$num] ?? null;
    return ($hint !== null ? $hint . ' ' : '') . '(' . $msg . ')';
}