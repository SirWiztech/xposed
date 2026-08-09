<?php
/**
 * XPOSED Admin — Videos (list, manual add, delete, YouTube sync)
 */

require __DIR__ . '/_guard.php';
require_admin();

$flash = '';
$flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'sync') {
        require_once __DIR__ . '/../app/helpers/youtube.php';
        $sync = new YoutubeSync();
        $result = $sync->run();
        $flash = $result ?? 'YouTube sync failed — channel not configured.';
        $flashType = $result ? 'ok' : 'error';
    } elseif ($action === 'add') {
        Video::upsertFromYoutube([
            'youtube_id'   => null,
            'title'        => trim((string)($_POST['title'] ?? '')),
            'description'  => trim((string)($_POST['description'] ?? '')),
            'thumb'        => '',
            'duration'     => (string)($_POST['duration'] ?? ''),
            'view_count'   => (int)($_POST['view_count'] ?? 0),
            'published_at' => (string)($_POST['published_at'] ?? now()),
            'position'     => (int)($_POST['position'] ?? 0),
        ]);
        $flash = 'Video added.';
    } elseif ($action === 'delete') {
        Video::delete((int)($_POST['id'] ?? 0));
        $flash = 'Video deleted.';
    }
}

$videos = db()->query('SELECT * FROM videos ORDER BY published_at DESC, position ASC')->fetchAll();

$adminTitle  = 'Videos';
$adminActive = 'videos';
$adminFlash  = $flash;
$adminFlashType = $flashType;
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">YouTube</p>
    <h1>The <span class="accent">Archive</span></h1>
  </div>
  <div style="display:flex; gap:10px;">
    <form method="post" action="<?= e(url('admin/videos.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="sync">
      <button type="submit" class="btn red">Sync from YouTube</button>
    </form>
  </div>
</div>

<div class="card">
  <h2>Add manually</h2>
  <form method="post" action="<?= e(url('admin/videos.php')) ?>" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="field full"><label>Title</label><input type="text" name="title" required></div>
    <div class="field"><label>Duration (e.g. 18:42)</label><input type="text" name="duration" placeholder="18:42"></div>
    <div class="field"><label>Views</label><input type="number" name="view_count" value="0" min="0"></div>
    <div class="field"><label>Published date</label><input type="datetime-local" name="published_at" value="<?= e(date('Y-m-d\TH:i')) ?>"></div>
    <div class="field"><label>Position (0 = newest)</label><input type="number" name="position" value="0" min="0"></div>
    <div class="field full"><label>Description</label><textarea name="description"></textarea></div>
    <div class="field full"><button type="submit" class="btn red">Add video</button></div>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th></th><th>Title</th><th>Duration</th><th>Views</th><th>Published</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($videos as $v): ?>
    <tr>
      <td><?php if ($v['thumb']): ?><img class="thumb" src="<?= e(upload_url($v['thumb'])) ?>" alt=""><?php endif; ?></td>
      <td><?= e($v['title']) ?><?php if (!empty($v['is_short'])): ?> <span class="badge">SHORT</span><?php endif; ?></td>
      <td class="tab-nums"><?= e($v['duration']) ?></td>
      <td class="tab-nums"><?= e(fmt_number((int)$v['view_count'])) ?></td>
      <td><?= e(date('M j, Y', strtotime($v['published_at']))) ?></td>
      <td>
        <form method="post" action="<?= e(url('admin/videos.php')) ?>" onsubmit="return confirm('Delete this video?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
          <button type="submit" class="btn danger sm">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
