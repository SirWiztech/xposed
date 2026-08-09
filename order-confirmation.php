<?php
/**
 * XPOSED — Order confirmation / receipt.
 */

require __DIR__ . '/app/bootstrap.php';

$ref = trim((string)($_GET['ref'] ?? ''));
$order = Order::findByRef($ref);

$pageTitle       = 'Order confirmed — Xposed Store';
$metaDescription = 'Your Xposed order confirmation.';
$robots = 'noindex, nofollow';
$active = 'store';
include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">The Store</p>
    <h1>Order <span class="accent">Received</span></h1>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <?php if (!$order): ?>
      <div class="empty"><p>We couldn’t find that order. If you have questions, email <?= e(config('business_email')) ?>.</p></div>
    <?php else:
        $items = Order::items((int)$order['id']);
    ?>
    <div class="cart-layout">
      <div>
        <div class="flash ok">
          Thanks — your order <strong class="tab-nums"><?= e($order['order_ref']) ?></strong> is in.
          A confirmation email is on the way to <strong><?= e($order['email']) ?></strong>.
        </div>

        <div class="form-card" style="max-width:100%;">
          <h2>Receipt</h2>
          <?php foreach ($items as $i): ?>
          <div class="sum-row">
            <span><?= e($i['product_name']) ?><?= $i['variant_name'] ? ' — ' . e($i['variant_name']) : '' ?> × <?= (int)$i['qty'] ?></span>
            <span class="tab-nums"><?= e(money((int)$i['unit_price_cents'] * (int)$i['qty'])) ?></span>
          </div>
          <?php endforeach; ?>
          <div class="sum-row total"><span>Total (<?= e($order['currency']) ?>)</span><span class="tab-nums"><?= e(money((int)$order['total_cents'])) ?></span></div>
          <p class="notice-sm" style="margin-top:18px;">
            Status: <?= e(ucwords(str_replace('-', ' ', $order['status']))) ?> ·
            Payment: <?= e(ucwords($order['payment_method'])) ?>
          </p>
        </div>

        <p style="margin-top:28px;">
          <a href="<?= e(url('store.php')) ?>" class="btn btn-ghost">Continue Shopping</a>
        </p>
      </div>
    </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
