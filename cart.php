<?php
/**
 * XPOSED — Cart.
 */

require __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        cart_update((string)($_POST['key'] ?? ''), max(0, (int)($_POST['qty'] ?? 0)));
    } elseif ($action === 'remove') {
        cart_remove((string)($_POST['key'] ?? ''));
    }
    redirect('cart.php');
}

$cart = cart();
$total = cart_total_cents();

$pageTitle       = 'Cart — Xposed Store';
$metaDescription = 'Your Xposed cart.';
$robots = 'noindex, nofollow';
$active = 'store';
include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">The Store</p>
    <h1>Your <span class="accent">Cart</span></h1>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <?php if (empty($cart)): ?>
      <div class="empty">
        <p>Your cart is empty.</p>
        <p style="margin-top:14px;"><a href="<?= e(url('store.php')) ?>" class="btn btn-primary">Back to Store</a></p>
      </div>
    <?php else: ?>
    <div class="cart-layout">
      <div>
        <?php foreach ($cart as $key => $item): ?>
        <form method="post" action="<?= e(url('cart.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="key" value="<?= e($key) ?>">
          <div class="cart-item">
            <div class="swatch"></div>
            <div class="ci-info">
              <h3><?= e($item['product_name']) ?></h3>
              <?php if (!empty($item['variant_name'])): ?><div class="ci-variant"><?= e($item['variant_name']) ?></div><?php endif; ?>
              <div class="ci-qty">
                <input type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="0" max="9" aria-label="Quantity">
                <span class="ci-price tab-nums"><?= e(money((int)$item['unit_price'] * (int)$item['qty'])) ?></span>
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-direction:column;">
              <button type="submit" name="action" value="update" class="btn btn-ghost" style="padding:8px 14px;font-size:.72rem;">Update</button>
              <button type="submit" name="action" value="remove" class="btn btn-ghost" style="padding:8px 14px;font-size:.72rem;border-color:rgba(225,6,0,.4);color:var(--signal-red);">Remove</button>
            </div>
          </div>
        </form>
        <?php endforeach; ?>
      </div>

      <aside class="cart-summary">
        <h3>Summary</h3>
        <div class="sum-row"><span>Items</span><span class="tab-nums"><?= (int)cart_count() ?></span></div>
        <div class="sum-row total"><span>Total</span><span class="tab-nums"><?= e(money($total)) ?></span></div>
        <a href="<?= e(url('checkout.php')) ?>" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:18px;">Checkout</a>
        <a href="<?= e(url('store.php')) ?>" class="btn btn-ghost" style="width:100%; justify-content:center; margin-top:10px;">Keep Shopping</a>
      </aside>
    </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
