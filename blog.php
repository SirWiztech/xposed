<?php
/**
 * XPOSED — Blog index.
 */

require __DIR__ . '/app/bootstrap.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$data = Post::paginate($page, 9);
$page = min($page, $data['pages']);

$pageTitle       = 'Blog — Xposed';
$metaDescription = 'Notes from nine years live — stream logs, mindset, and the stuff behind the highlights.';
$active = 'blog';
include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">The Blog</p>
    <h1>Notes From <span class="accent">Live</span></h1>
    <p class="lead">Long-form stuff that doesn’t fit in a VOD title.</p>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <?php if (empty($data['items'])): ?>
      <div class="empty"><p>No posts yet — the pen is sharpening.</p></div>
    <?php else: ?>
    <div class="v-grid">
      <?php foreach ($data['items'] as $p): ?>
      <a class="card-link" href="<?= e(url('post.php?slug=' . urlencode($p['slug']))) ?>">
        <article class="product" style="height:100%;">
          <div class="swatch" style="aspect-ratio:16/9;">
            <?php if ($p['cover_image']): ?><img src="<?= e(upload_url($p['cover_image'])) ?>" alt="" loading="lazy"><?php endif; ?>
          </div>
          <span class="tag-sm"><?= e(date('M j, Y', strtotime($p['published_at']))) ?></span>
          <h3 style="font-size:1.05rem;"><?= e($p['title']) ?></h3>
          <p style="color:var(--muted); font-size:.88rem; margin-top:8px;"><?= e($p['excerpt']) ?></p>
        </article>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($data['pages'] > 1): ?>
    <nav class="pager" aria-label="Pages">
      <?php if ($page > 1): ?><a href="<?= e(url('blog.php?page=' . ($page - 1))) ?>">← Prev</a><?php endif; ?>
      <?php for ($i = 1; $i <= $data['pages']; $i++): ?>
        <?php if ($i === $page): ?><span class="cur tab-nums"><?= $i ?></span>
        <?php else: ?><a href="<?= e(url('blog.php?page=' . $i)) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $data['pages']): ?><a href="<?= e(url('blog.php?page=' . ($page + 1))) ?>">Next →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
