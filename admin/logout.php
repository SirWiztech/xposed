<?php
/**
 * XPOSED Admin — Logout
 */

require __DIR__ . '/../app/bootstrap.php';
unset($_SESSION['admin_id']);
redirect('admin/index.php');
