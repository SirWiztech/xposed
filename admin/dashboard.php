<?php
/**
 * XPOSED Admin — Dashboard (stats + live toggle)
 */

require __DIR__ . '/_guard.php';
require_admin();

$flash = '';
$flashType = 'ok';

// Live status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'live') {
    csrf_verify();
    $isLive = ($_POST['is_live'] ?? '0') === '1' ? '1' : '0';
    setting_set('is_live', $isLive);
    $flash = $isLive === '1' ? 'Marked as LIVE — the site now shows the ON AIR state.' : 'Marked as offline — the site now shows the offline state.';
}

// Quick stats update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'stats') {
    csrf_verify();
    setting_set('subs', (string)max(0, (int)($_POST['subs'] ?? 0)));
    setting_set('views', (string)max(0, (int)($_POST['views'] ?? 0)));
    setting_set('years_live', (string)max(0, (int)($_POST['years_live'] ?? 0)));
    $flash = 'Stats updated.';
}

$isLive   = setting('is_live', '0') === '1';
$subs     = (int)setting('subs', 541000);
$views    = (int)setting('views', 41290000);
$yearsLive = (int)setting('years_live', 9);
$lastSync = setting('youtube_last_sync', '');

$adminTitle  = 'Dashboard';
$adminActive = 'dashboard';
$adminFlash  = $flash;
$adminFlashType = $flashType;
include __DIR__ . '/_header.php';
?>

<div class="admin-toolbar">
  <div>
    <p class="page-eyebrow">Control Room</p>
    <h1>Dash<span class="accent">board</span></h1>
  </div>
  <a href="<?= e(url('index.php')) ?>" class="btn ghost" target="_blank">View site →</a>
</div>

<div class="stat-grid">
  <div class="stat"><b class="tab-nums"><?= e(fmt_number($subs)) ?></b><span>YouTube Subs</span></div>
  <div class="stat"><b class="tab-nums"><?= e(fmt_number($views)) ?></b><span>Total Views</span></div>
  <div class="stat"><b class="tab-nums"><?= (int)$yearsLive ?></b><span>Years Live</span></div>
  <div class="stat"><b class="tab-nums"><?= (int)Video::count() ?></b><span>Videos</span></div>
  <div class="stat"><b class="tab-nums"><?= (int)Post::count() ?></b><span>Posts</span></div>
  <div class="stat"><b class="tab-nums"><?= (int)Product::count() ?></b><span>Products</span></div>
  <div class="stat"><b class="tab-nums"><?= (int)Order::count() ?></b><span>Orders</span></div>
</div>

<div class="card">
  <h2>Live status</h2>
  <p style="color:var(--muted); margin-bottom:14px;">
    Currently: <strong style="color:<?= $isLive ? 'var(--signal-red)' : 'var(--muted)' ?>"><?= $isLive ? 'LIVE — ON AIR' : 'OFFLINE' ?></strong>
  </p>
  <form method="post" action="<?= e(url('admin/dashboard.php')) ?>" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="live">
    <input type="hidden" name="is_live" value="<?= $isLive ? '0' : '1' ?>">
    <button type="submit" class="btn <?= $isLive ? 'danger' : 'red' ?>"><?= $isLive ? 'Set Offline' : 'Set LIVE' ?></button>
    <span style="color:var(--muted); font-size:.8rem;">Manual toggle for MVP — Kick API auto-status comes later.</span>
  </form>
</div>

<div class="card">
  <h2>Homepage stats</h2>
  <form method="post" action="<?= e(url('admin/dashboard.php')) ?>" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="stats">
    <div class="field"><label>Subscribers</label><input type="number" name="subs" value="<?= (int)$subs ?>" min="0"></div>
    <div class="field"><label>Total views</label><input type="number" name="views" value="<?= (int)$views ?>" min="0"></div>
    <div class="field"><label>Years live</label><input type="number" name="years_live" value="<?= (int)$yearsLive ?>" min="0"></div>
    <div class="field" style="display:flex; align-items:flex-end;"><button type="submit" class="btn red">Save stats</button></div>
  </form>
  <p class="field" style="color:var(--muted); font-size:.78rem; margin-top:12px;">
    YouTube last sync: <?= $lastSync !== '' ? e($lastSync) : 'never' ?>.
  </p>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
