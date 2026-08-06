<?php
/**
 * XPOSED Admin — Create / edit blog post
 */

require __DIR__ . '/_guard.php';
require_admin();

$id   = (int)($_GET['id'] ?? 0);
$post = $id > 0 ? Post::find($id) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'title'        => trim((string)($_POST['title'] ?? '')),
        'slug'         => trim((string)($_POST['slug'] ?? '')),
        'excerpt'      => trim((string)($_POST['excerpt'] ?? '')),
        'body'         => (string)($_POST['body'] ?? ''),
        'status'       => ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
        'published_at' => (string)($_POST['published_at'] ?? now()),
    ];

    if ($data['title'] === '') {
        $error = 'Title is required.';
    } else {
        if ($data['slug'] === '') {
            $data['slug'] = slugify($data['title']);
        }
        $data['cover_image'] = $post['cover_image'] ?? '';
        try {
            if (!empty($_FILES['cover']['name'])) {
                $data['cover_image'] = handle_upload('cover') ?? $data['cover_image'];
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        if ($error === '') {
            if ($post) {
                Post::update((int)$post['id'], $data);
                $post = Post::find((int)$post['id']);
                $flash = 'Post updated.';
            } else {
                try {
                    $newId = Post::create($data);
                    redirect('admin/post-edit.php?id=' . $newId);
                } catch (Throwable $e) {
                    $error = 'Could not save (slug may already exist): ' . $e->getMessage();
                }
            }
        }
    }
}
$flash = $flash ?? '';
$post  = $post ?: ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'cover_image' => '', 'body' => '', 'status' => 'draft', 'published_at' => now()];

$adminTitle  = $id > 0 ? 'Edit post' : 'New post';
$adminActive = 'posts';
$adminFlash  = $flash !== '' ? $flash : ($error !== '' ? $error : '');
$adminFlashType = $error !== '' ? 'error' : 'ok';
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">The Blog</p>
    <h1><?= $id > 0 ? 'Edit <span class="accent">Post</span>' : 'New <span class="accent">Post</span>' ?></h1>
  </div>
  <a href="<?= e(url('admin/posts.php')) ?>" class="btn ghost">← All posts</a>
</div>

<form method="post" action="<?= e(url('admin/post-edit.php' . ($id > 0 ? '?id=' . $id : ''))) ?>" enctype="multipart/form-data" class="card">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field full"><label>Title</label><input type="text" name="title" value="<?= e($post['title']) ?>" required></div>
    <div class="field"><label>Slug (URL)</label><input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="leave blank to auto-generate"></div>
    <div class="field"><label>Status</label>
      <select name="status">
        <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
      </select>
    </div>
    <div class="field"><label>Published date</label><input type="datetime-local" name="published_at" value="<?= e(date('Y-m-d\TH:i', strtotime($post['published_at']))) ?>"></div>
    <div class="field full"><label>Excerpt</label><input type="text" name="excerpt" value="<?= e($post['excerpt']) ?>" maxlength="500"></div>
    <div class="field full"><label>Cover image</label><input type="file" name="cover" accept="image/png,image/jpeg,image/webp,image/gif"></div>
    <?php if ($post['cover_image']): ?>
    <div class="field full"><img src="<?= e(upload_url($post['cover_image'])) ?>" style="max-width:240px; border-radius:2px;" alt="Current cover"></div>
    <?php endif; ?>
    <div class="field full">
      <label>Body (Markdown-ish: ## headings, **bold**, - bullets)</label>
      <textarea name="body" style="min-height:320px; font-family:ui-monospace,monospace; font-size:.85rem;"><?= e($post['body']) ?></textarea>
      <p class="hint">Plain text is escaped on output — no raw HTML runs.</p>
    </div>
    <div class="field full"><button type="submit" class="btn red">Save post</button></div>
  </div>
</form>

<?php include __DIR__ . '/_footer.php'; ?>
