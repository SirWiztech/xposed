<?php
/**
 * XPOSED Admin — Orders (list + status updates)
 */

require __DIR__ . '/_guard.php';
require_admin();

$flash = '';
$flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? 'pending'));
        Order::setStatus($id, $status);
        $flash = 'Order status updated.';
    }
}

$orders = Order::recent(100);

$adminTitle  = 'Orders';
$adminActive = 'orders';
$adminFlash  = $flash;
$adminFlashType = $flashType;
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">Sales Desk</p>
    <h1>Orders <span class="accent">Inbox</span></h1>
  </div>
</div>

<div class="card">
  <?php if (empty($orders)): ?>
    <p style="color:var(--muted);">No orders yet.</p>
  <?php else: ?>
  <table>
    <thead><tr><th>Ref</th><th>Email</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td class="tab-nums"><?= e($o['order_ref']) ?></td>
      <td><?= e($o['email']) ?></td>
      <td class="tab-nums"><?= e(money((int)$o['total_cents'])) ?></td>
      <td><?= e(ucwords($o['payment_method'])) ?></td>
      <td><?= e(ucwords(str_replace('-', ' ', $o['status']))) ?></td>
      <td><?= e(date('M j, H:i', strtotime($o['created_at']))) ?></td>
      <td>
        <form method="post" action="<?= e(url('admin/orders.php')) ?>" style="display:flex; gap:6px;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
          <select name="status" style="background:var(--surface-2); border:1px solid rgba(255,255,255,.12); color:var(--paper); padding:6px 8px; border-radius:2px;">
            <?php foreach (['pending', 'email-order', 'paid', 'shipped', 'fulfilled', 'cancelled'] as $s): ?>
            <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn sm">Set</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
