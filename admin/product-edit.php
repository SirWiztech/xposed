<?php
/**
 * XPOSED Admin — Create / edit product + variants
 */

require __DIR__ . '/_guard.php';
require_admin();

$id   = (int)($_GET['id'] ?? 0);
$product = $id > 0 ? Product::findAny($id) : null;
$error = '';
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'name'        => trim((string)($_POST['name'] ?? '')),
        'slug'        => trim((string)($_POST['slug'] ?? '')),
        'description' => (string)($_POST['description'] ?? ''),
        'price_cents' => (int)round((float)($_POST['price'] ?? 0) * 100),
        'currency'    => strtoupper((string)($_POST['currency'] ?? 'CAD')),
        'image'       => $product['image'] ?? '',
        'category'    => trim((string)($_POST['category'] ?? '')),
        'type'        => ($_POST['type'] ?? 'apparel') === 'digital' ? 'digital' : 'apparel',
        'featured'    => isset($_POST['featured']) ? 1 : 0,
        'active'      => isset($_POST['active']) ? 1 : 0,
    ];

    if ($data['name'] === '') {
        $error = 'Product name is required.';
    } else {
        if ($data['slug'] === '') {
            $data['slug'] = slugify($data['name']);
        }
        try {
            if (!empty($_FILES['image']['name'])) {
                $data['image'] = handle_upload('image') ?? $data['image'];
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        if ($error === '') {
            if ($product) {
                Product::update((int)$product['id'], $data);
                $variants = array_map('trim', explode("\n", (string)($_POST['variants'] ?? '')));
                Product::saveVariants((int)$product['id'], $variants);
                $product = Product::find((int)$product['id']);
                $flash = 'Product updated.';
            } else {
                try {
                    $newId = Product::create($data);
                    $variants = array_map('trim', explode("\n", (string)($_POST['variants'] ?? '')));
                    Product::saveVariants($newId, $variants);
                    redirect('admin/product-edit.php?id=' . $newId);
                } catch (Throwable $e) {
                    $error = 'Could not save (slug may already exist): ' . $e->getMessage();
                }
            }
        }
    }
}

$product = $product ?: [
    'id' => 0, 'name' => '', 'slug' => '', 'description' => '', 'price_cents' => 0,
    'currency' => 'CAD', 'image' => '', 'category' => '', 'type' => 'apparel',
    'featured' => 0, 'active' => 1,
];
$variantsText = implode("\n", array_column(Product::variants((int)$product['id']), 'name'));

$adminTitle  = $id > 0 ? 'Edit product' : 'New product';
$adminActive = 'products';
$adminFlash  = $flash !== '' ? $flash : ($error !== '' ? $error : '');
$adminFlashType = $error !== '' ? 'error' : 'ok';
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">The Store</p>
    <h1><?= $id > 0 ? 'Edit <span class="accent">Product</span>' : 'New <span class="accent">Product</span>' ?></h1>
  </div>
  <a href="<?= e(url('admin/products.php')) ?>" class="btn ghost">← All products</a>
</div>

<form method="post" action="<?= e(url('admin/product-edit.php' . ($id > 0 ? '?id=' . $id : ''))) ?>" enctype="multipart/form-data" class="card">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field full"><label>Name</label><input type="text" name="name" value="<?= e($product['name']) ?>" required></div>
    <div class="field"><label>Slug (URL)</label><input type="text" name="slug" value="<?= e($product['slug']) ?>" placeholder="leave blank to auto-generate"></div>
    <div class="field"><label>Price</label><input type="number" name="price" value="<?= e(number_format((int)$product['price_cents'] / 100, 2)) ?>" min="0" step="0.01"></div>
    <div class="field"><label>Currency</label><input type="text" name="currency" value="<?= e($product['currency']) ?>" maxlength="8"></div>
    <div class="field"><label>Category</label><input type="text" name="category" value="<?= e($product['category']) ?>" placeholder="Apparel / Digital Tools"></div>
    <div class="field"><label>Type</label>
      <select name="type">
        <option value="apparel" <?= $product['type'] === 'apparel' ? 'selected' : '' ?>>Apparel</option>
        <option value="digital" <?= $product['type'] === 'digital' ? 'selected' : '' ?>>Digital tool</option>
      </select>
    </div>
    <div class="field full"><label>Image</label><input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif"></div>
    <?php if ($product['image']): ?>
    <div class="field full"><img src="<?= e(upload_url($product['image'])) ?>" style="max-width:160px; border-radius:2px;" alt="Current image"></div>
    <?php endif; ?>
    <div class="field full"><label>Description</label><textarea name="description"><?= e($product['description']) ?></textarea></div>
    <div class="field full"><label>Variants (one per line — sizes etc.)</label><textarea name="variants" style="min-height:90px;"><?= e($variantsText) ?></textarea></div>
    <div class="field">
      <label style="display:flex; gap:8px; align-items:center; text-transform:none; letter-spacing:0;"><input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>> Featured on homepage</label>
    </div>
    <div class="field">
      <label style="display:flex; gap:8px; align-items:center; text-transform:none; letter-spacing:0;"><input type="checkbox" name="active" <?= $product['active'] ? 'checked' : '' ?>> Active (visible in store)</label>
    </div>
    <div class="field full"><button type="submit" class="btn red">Save product</button></div>
  </div>
</form>

<?php include __DIR__ . '/_footer.php'; ?>
