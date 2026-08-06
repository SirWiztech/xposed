<?php
/**
 * XPOSED — Product detail + add-to-cart.
 */

require __DIR__ . '/app/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$product = Product::find($id);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Not found — Xposed';
    include __DIR__ . '/app/views/partials/header.php';
    echo '<main><section class="page-hero wrap"><h1>Not <span class="accent">Found</span></h1>'
       . '<p class="lead">That product doesn’t exist or is no longer for sale. <a href="' . e(url('store.php')) . '" style="text-decoration:underline;">Back to the store →</a></p></section></main>';
    include __DIR__ . '/app/views/partials/footer.php';
    exit;
}

$variants = Product::variants($id);

// Handle add-to-cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    csrf_verify();
    $variantId = (int)($_POST['variant_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    $variantName = '';
    foreach ($variants as $v) {
        if ((int)$v['id'] === $variantId) {
            $variantName = $v['name'];
            break;
        }
    }

    cart_add([
        'product_id'   => $id,
        'variant_id'   => $variantId,
        'product_name' => $product['name'],
        'variant_name' => $variantName,
        'unit_price'   => (int)$product['price_cents'],
    ]);
    redirect('cart.php');
}

$pageTitle       = $product['name'] . ' — Xposed Store';
$metaDescription = mb_substr(strip_tags($product['description'] ?? ''), 0, 150);
$active = 'store';
include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="section-pad wrap">
    <div class="pd-grid">
      <div class="pd-media">
        <?php if ($product['image']): ?><img src="<?= e(upload_url($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy"><?php endif; ?>
      </div>

      <div class="pd">
        <span class="tag-sm"><?= e($product['category']) ?> · <?= e(ucfirst($product['type'])) ?></span>
        <h1><?= e($product['name']) ?></h1>
        <div class="price-lg tab-nums"><?= e(money((int)$product['price_cents'])) ?> <?= e($product['currency']) ?></div>
        <p class="desc"><?= nl2br(e($product['description'])) ?></p>

        <form method="post" action="<?= e(url('product.php?id=' . $id)) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int)$id ?>">

          <?php if ($variants): ?>
          <div class="field">
            <label for="variant_id">Size / Variant</label>
            <select name="variant_id" id="variant_id" required>
              <?php foreach ($variants as $v): ?>
              <option value="<?= (int)$v['id'] ?>"><?= e($v['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div class="field">
            <label for="qty">Quantity</label>
            <div class="qty-row">
              <input type="number" name="qty" id="qty" value="1" min="1" max="9">
            </div>
          </div>

          <div class="add-cart-row">
            <button type="submit" class="btn btn-primary">Add to Cart</button>
            <a href="<?= e(url('store.php')) ?>" class="btn btn-ghost">Back to Store</a>
          </div>
        </form>

        <p class="notice-sm">
          <?= $product['type'] === 'digital'
              ? 'Digital download — delivered to your email right after checkout.'
              : 'Apparel ships from Canada. International shipping where carriers allow it.' ?>
        </p>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
