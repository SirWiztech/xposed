<?php
/**
 * Shared page <head> + fixed header.
 * Expects (optional): $pageTitle, $metaDescription, $ogImage, $active
 */
$pageTitle       = $pageTitle       ?? 'Xposed — 9 Years Live. No Filter. All In.';
$metaDescription = $metaDescription ?? 'Xposed (Cody) — Twitch & Kick partner, 541K+ on YouTube. Watch live, catch the latest uploads, and go behind the reel.';
$ogImage         = $ogImage         ?? '';
$active          = $active          ?? '';

$isLive = setting('is_live', '0') === '1';

$navLinks = [
    'home'    => ['label' => 'Home',    'href' => 'index.php',      'icon' => 'home'],
    'videos'  => ['label' => 'Videos',  'href' => 'videos.php',     'icon' => 'film'],
    'live'    => ['label' => 'Live',    'href' => 'live.php',       'icon' => 'radio'],
    'store'   => ['label' => 'Store',   'href' => 'store.php',      'icon' => 'bag'],
    'tools'   => ['label' => 'Tools',   'href' => 'tools.php',      'icon' => 'chip'],
    'blog'    => ['label' => 'Blog',    'href' => 'blog.php',       'icon' => 'note'],
    'connect' => ['label' => 'Connect', 'href' => 'index.php#connect', 'icon' => 'link'],
];

$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">

<!-- Open Graph / Twitter -->
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Xposed">
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(url('assets/css/main.css') . '?v=' . @filemtime(__DIR__ . '/../../../assets/css/main.css')) ?>">
</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>

<canvas id="bg-particles" aria-hidden="true"></canvas>
<div class="bg-orbs" aria-hidden="true">
  <div class="orb-wrap" id="orbWrap1"><span class="orb orb-1"></span></div>
  <div class="orb-wrap" id="orbWrap2"><span class="orb orb-2"></span></div>
  <div class="orb-wrap" id="orbWrap3"><span class="orb orb-3"></span></div>
</div>

<header id="siteHeader">
  <?php if ($active && $active !== 'home'): ?>
  <button class="back-btn" id="backBtn" data-home="<?= e(url('index.php')) ?>" aria-label="Go back to the previous page">
    <?php icon('arrow-left') ?><span>Back</span>
  </button>
  <?php endif; ?>

  <a href="<?= e(url('index.php')) ?>" class="logo"><span class="logo-mark"><img src="<?= e(url('assets/' . rawurlencode('image-removebg-preview (7).png'))) ?>" alt="Xposed"></span>POSED</a>

  <nav class="links" aria-label="Primary">
    <?php foreach ($navLinks as $key => $link): ?>
    <a href="<?= e(url($link['href'])) ?>" class="<?= $active === $key ? 'active' : '' ?>">
      <span class="nav-icon"><?php icon($link['icon'] ?? 'dot-live') ?></span>
      <?= e($link['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="nav-actions">
    <a href="<?= e(url('cart.php')) ?>" class="live-pill" aria-label="Cart">
      <span class="nav-icon" style="width:15px;height:15px;"><?php icon('bag') ?></span>
      <span class="pill-label">Cart</span><?php if ($cartCount > 0): ?><span class="cart-count tab-nums"><?= (int)$cartCount ?></span><?php endif; ?>
    </a>
    <div class="live-pill <?= $isLive ? 'is-live' : '' ?>">
      <span class="dot"></span>
      <span class="pill-status-text"><?= $isLive ? 'Live on Kick' : 'Offline — Watch Latest VOD' ?></span>
    </div>
    <button class="menu-btn" aria-label="Open menu" aria-expanded="false">☰</button>
  </div>
</header>
