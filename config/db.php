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
        '1049' => 'unknown database — check XPOSED_DB_NAME (InfinityFree prefixes it, e.g. if0_<user>_<db>).',
        '2002' => 'MySQL host unreachable — check XPOSED_DB_HOST (InfinityFree uses a remote host such as sqlXXX.infinityfree.com, NOT 127.0.0.1) and XPOSED_DB_PORT.',
        '2003' => 'connection refused — check XPOSED_DB_HOST and XPOSED_DB_PORT.',
        'HY000' => 'host check failed — the db* settings in .env may be wrong (host, port).',
    ];

    $hint = $map[$num] ?? null;
    return ($hint !== null ? $hint . ' ' : '') . '(' . $msg . ')';
}