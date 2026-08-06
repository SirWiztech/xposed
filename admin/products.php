<?php
/**
 * XPOSED Admin — Products (list + delete)
 */

require __DIR__ . '/_guard.php';
require_admin();

$flash = '';
$flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    Product::delete((int)($_POST['id'] ?? 0));
    $flash = 'Product deleted.';
}

$products = db()->query('SELECT * FROM products ORDER BY category ASC, id DESC')->fetchAll();

$adminTitle  = 'Store';
$adminActive = 'products';
$adminFlash  = $flash;
$adminFlashType = $flashType;
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">The Store</p>
    <h1>Shop <span class="accent">Items</span></h1>
  </div>
  <a href="<?= e(url('admin/product-edit.php')) ?>" class="btn red">+ New product</a>
</div>

<div class="card">
  <table>
    <thead><tr><th></th><th>Name</th><th>Category</th><th>Type</th><th>Price</th><th>Active</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <tr>
      <td><?php if ($p['image']): ?><img class="thumb" style="width:44px; aspect-ratio:1/1;" src="<?= e(upload_url($p['image'])) ?>" alt=""><?php endif; ?></td>
      <td><a href="<?= e(url('admin/product-edit.php?id=' . (int)$p['id'])) ?>" style="color:var(--paper);"><?= e($p['name']) ?></a></td>
      <td><?= e($p['category']) ?></td>
      <td><?= e($p['type']) ?></td>
      <td class="tab-nums"><?= e(money((int)$p['price_cents'])) ?></td>
      <td><?= $p['active'] ? 'yes' : 'no' ?></td>
      <td>
        <div class="row-actions">
          <a class="btn ghost sm" href="<?= e(url('admin/product-edit.php?id=' . (int)$p['id'])) ?>">Edit</a>
          <form method="post" action="<?= e(url('admin/products.php')) ?>" onsubmit="return confirm('Delete this product?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button type="submit" class="btn danger sm">Delete</button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
