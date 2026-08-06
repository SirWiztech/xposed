<?php
/**
 * XPOSED Admin — Blog posts (list + delete)
 */

require __DIR__ . '/_guard.php';
require_admin();

$flash = '';
$flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    Post::delete((int)($_POST['id'] ?? 0));
    $flash = 'Post deleted.';
}

$posts = db()->query('SELECT * FROM posts ORDER BY published_at DESC')->fetchAll();

$adminTitle  = 'Blog';
$adminActive = 'posts';
$adminFlash  = $flash;
$adminFlashType = $flashType;
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">The Blog</p>
    <h1>Blog <span class="accent">Posts</span></h1>
  </div>
  <a href="<?= e(url('admin/post-edit.php')) ?>" class="btn red">+ New post</a>
</div>

<div class="card">
  <table>
    <thead><tr><th>Title</th><th>Status</th><th>Published</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
    <tr>
      <td><a href="<?= e(url('admin/post-edit.php?id=' . (int)$p['id'])) ?>" style="color:var(--paper);"><?= e($p['title']) ?></a></td>
      <td><?= e($p['status']) ?></td>
      <td><?= e(date('M j, Y', strtotime($p['published_at']))) ?></td>
      <td>
        <div class="row-actions">
          <a class="btn ghost sm" href="<?= e(url('admin/post-edit.php?id=' . (int)$p['id'])) ?>">Edit</a>
          <form method="post" action="<?= e(url('admin/posts.php')) ?>" onsubmit="return confirm('Delete this post?');">
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
