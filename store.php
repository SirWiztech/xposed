<?php
/**
 * XPOSED — Store (merch + digital tools).
 */

require __DIR__ . '/app/bootstrap.php';

$category = trim((string)($_GET['cat'] ?? ''));
$products = Product::all(['category' => $category]);
$categories = Product::categories();

$pageTitle       = 'Store — Xposed';
$metaDescription = 'Xposed merch and digital tools — apparel, bankroll trackers and more.';
$active = 'store';
include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">The Store</p>
    <h1>Gear &amp; <span class="accent">Tools</span></h1>
    <p class="lead">Apparel for the people who show up daily, plus the digital tools behind the channel.</p>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <?php if ($categories): ?>
    <div class="chip-row" style="padding:0 0 30px;">
      <a class="chip" href="<?= e(url('store.php')) ?>">All</a>
      <?php foreach ($categories as $c): ?>
      <a class="chip" href="<?= e(url('store.php?cat=' . urlencode($c))) ?>" style="<?= $category === $c ? 'border-color:var(--signal-red);color:var(--paper);' : '' ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
      <div class="empty"><p>Nothing here yet — the shop is restocking.</p></div>
    <?php else: ?>
    <div class="store-grid">
      <?php foreach ($products as $item): ?>
      <a class="product" href="<?= e(url('product.php?id=' . (int)$item['id'])) ?>">
        <div class="swatch">
          <?php if ($item['image']): ?><img src="<?= e(upload_url($item['image'])) ?>" alt="<?= e($item['name']) ?>" loading="lazy"><?php endif; ?>
        </div>
        <span class="tag-sm"><?= e($item['category']) ?> · <?= e(ucfirst($item['type'])) ?></span>
        <h3><?= e($item['name']) ?></h3>
        <div class="price tab-nums"><?= e(money((int)$item['price_cents'])) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
