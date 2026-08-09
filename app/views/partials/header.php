<?php
/**
 * Shared page <head> + fixed header.
 * Expects (optional): $pageTitle, $metaDescription, $ogImage, $ogType, $canonical, $robots, $active, $schemaJson
 */
$pageTitle       = $pageTitle       ?? config('seo.default_title');
$metaDescription = $metaDescription ?? config('seo.default_description');
$ogImage         = $ogImage         ?? '';
$ogType          = $ogType          ?? 'website';
$canonical       = $canonical       ?? canonical_url();
$robots          = $robots          ?? 'index,follow';
$active          = $active          ?? '';
$schemaJson      = $schemaJson      ?? null;
$prevUrl         = $prevUrl         ?? '';
$nextUrl         = $nextUrl         ?? '';

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
$siteName = config('seo.site_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<meta name="robots" content="<?= e($robots) ?>">
<meta name="theme-color" content="#0A0A0B">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if ($prevUrl): ?><link rel="prev" href="<?= e($prevUrl) ?>"><?php endif; ?>
<?php if ($nextUrl): ?><link rel="next" href="<?= e($nextUrl) ?>"><?php endif; ?>
<link rel="icon" href="<?= e(url('assets/favicon.ico')) ?>" sizes="any">
<link rel="icon" type="image/png" sizes="192x192" href="<?= e(url('assets/android-chrome-192x192.png')) ?>">
<link rel="icon" type="image/png" sizes="512x512" href="<?= e(url('assets/android-chrome-512x512.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(url('assets/apple-touch-icon.png')) ?>">

<!-- Open Graph / Twitter -->
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="<?= e(config('seo.locale')) ?>">
<?php $shareImage = $ogImage !== '' ? absolute_url($ogImage) : default_og_image(); ?>
<meta property="og:image" content="<?= e($shareImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="<?= e(config('seo.twitter_handle')) ?>">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($metaDescription) ?>">
<meta name="twitter:image" content="<?= e($shareImage) ?>">
<?php if ($ogType === 'article'): ?>
<meta property="article:published_time" content="<?= e($publishedTime ?? '') ?>">
<meta property="article:modified_time" content="<?= e($publishedTime ?? '') ?>">
<?php endif; ?>
<?php if ($ogType === 'product'): ?>
<meta property="product:price:amount" content="<?= e($productPrice ?? '') ?>">
<meta property="product:price:currency" content="<?= e($productPriceCurrency ?? '') ?>">
<?php endif; ?>

<?php if ($schemaJson): ?>
<script type="application/ld+json"><?= json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>

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
    <a href="#" id="aiNavLink" aria-label="Open the AI assistant" aria-expanded="false">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path d="M12 3l1.9 5.3 5.3 1.9-5.3 1.9L12 17.4l-1.9-5.3L4.8 10.2l5.3-1.9z" stroke-linejoin="round"/>
        </svg>
      </span>
      AI
    </a>
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
