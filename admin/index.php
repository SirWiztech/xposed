<?php
/**
 * XPOSED Admin — Login
 */

require __DIR__ . '/_guard.php';

if (admin_logged_in()) {
    redirect('admin/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    csrf_verify();
    rate_limit('admin_login', 5);

    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $st = db()->prepare('SELECT * FROM admins WHERE email = ?');
    $st->execute([$email]);
    $admin = $st->fetch();

    // Accepted password: XPOSED_ADMIN_PASSWORD (env) wins when set;
    // otherwise fall back to the row's bcrypt hash.
    $envPass = (string)config('admin.password');
    $passwordOk = $envPass !== ''
        ? password_verify($password, password_hash($envPass, PASSWORD_DEFAULT))
        : ($admin !== false && password_verify($password, $admin['password_hash']));

    if ($admin && $passwordOk) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        redirect('admin/dashboard.php');
    }

    // Fake verification work to keep timing uniform.
    password_verify($password, '$2y$12$95Ar8B0KcBqFsFhOJUHbjO8rcg0mcY308h3rkim1J/k.9jATCVXoS');
    $error = 'Email or password is wrong.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login — Xposed</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(url('admin/admin.css')) ?>">
</head>
<body>
<div class="bg-orbs" aria-hidden="true">
  <div class="orb-wrap"><span class="orb orb-1"></span></div>
  <div class="orb-wrap"><span class="orb orb-2"></span></div>
  <div class="orb-wrap"><span class="orb orb-3"></span></div>
</div>
<div class="login-wrap">
  <form class="login-box" method="post" action="<?= e(url('admin/index.php')) ?>">
    <?= csrf_field() ?>
    <h1><span class="logo-mark"><img src="<?= e(url('assets/' . rawurlencode('image-removebg-preview (7).png'))) ?>" alt="Xposed"></span> POSED <span style="color:var(--signal-red);">HQ</span></h1>
    <p class="sub">Admin access — keep this door locked.</p>

    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>

    <div class="field" style="margin-bottom:14px;">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autocomplete="username">
    </div>
    <div class="field" style="margin-bottom:22px;">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn red">Sign in</button>
  </form>
</div>
</body>
</html>
