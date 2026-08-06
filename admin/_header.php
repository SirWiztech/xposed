<?php
/**
 * Admin header partial. Expects: $adminTitle, $adminActive
 */
$adminActive = $adminActive ?? '';
$nav = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'home'],
    'videos'    => ['label' => 'Videos',    'href' => 'videos.php',    'icon' => 'film'],
    'posts'     => ['label' => 'Blog',      'href' => 'posts.php',     'icon' => 'note'],
    'products'  => ['label' => 'Store',     'href' => 'products.php',  'icon' => 'bag'],
    'faqs'      => ['label' => 'FAQ',       'href' => 'faqs.php',      'icon' => 'chat'],
    'orders'    => ['label' => 'Orders',    'href' => 'orders.php',    'icon' => 'check'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($adminTitle) ?> — Xposed Admin</title>
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

<div class="admin-top">
  <div class="brand">
    <span class="logo-mark"><img src="<?= e(url('assets/' . rawurlencode('image-removebg-preview (7).png'))) ?>" alt="Xposed"></span>POSED
    <span class="admin-tag">Admin</span>
  </div>
  <nav>
    <?php foreach ($nav as $key => $l): ?>
    <a href="<?= e(url('admin/' . $l['href'])) ?>" class="<?= $adminActive === $key ? 'active' : '' ?>">
      <?php icon($l['icon'], 'icon') ?><?= e($l['label']) ?>
    </a>
    <?php endforeach; ?>
    <a href="<?= e(url('admin/logout.php')) ?>" style="color:var(--signal-red);"><?php icon('external', 'icon') ?>Log out</a>
  </nav>
</div>
<main class="admin-main">
<?php if (!empty($adminFlash)): ?><div class="flash <?= e($adminFlashType ?? 'ok') ?>"><?= e($adminFlash) ?></div><?php endif; ?>
