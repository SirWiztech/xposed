<?php
/**
 * XPOSED — Admin auth guard.
 * Include after bootstrap.php. Redirects to login when not authed.
 */

require_once __DIR__ . '/../app/bootstrap.php';

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        redirect('admin/index.php');
    }
}

function admin_user(): ?array
{
    if (!admin_logged_in()) {
        return null;
    }
    static $user = false;
    if ($user === false) {
        $st = db()->prepare('SELECT * FROM admins WHERE id = ?');
        $st->execute([(int)$_SESSION['admin_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user ?: null;
}
