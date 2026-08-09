<?php
/**
 * XPOSED — Video archive (cached YouTube uploads).
 */

require __DIR__ . '/app/bootstrap.php';

require_once __DIR__ . '/app/helpers/youtube.php';
(new YoutubeSync())->ensureFresh();

$page = max(1, (int)($_GET['page'] ?? 1));
$data = Video::paginate($page, 12);
$page = min($page, $data['pages']);

$pageTitle       = 'Videos — Xposed';
$metaDescription = 'The full Xposed upload archive — session recaps, high multipliers, and everything in between.';
$active = 'videos';

if ($page > 1) {
    $prevUrl = absolute_url('videos.php' . ($page > 2 ? '?page=' . ($page - 1) : ''));
}
if ($page < $data['pages']) {
    $nextUrl = absolute_url('videos.php?page=' . ($page + 1));
}

$schemaJson = [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => 'Xposed videos',
    'url'      => canonical_url(),
    'itemListElement' => array_map(function ($v, $i) {
        return [
            '@type'     => 'ListItem',
            'position'  => $i + 1,
            'item'      => [
                '@type'        => 'VideoObject',
                'name'         => $v['title'],
                'description'  => mb_substr(strip_tags($v['description'] ?? ''), 0, 200),
                'thumbnailUrl' => $v['thumb'] ?? '',
                'uploadDate'   => date('Y-m-d', strtotime((string)$v['published_at'])),
                'contentUrl'   => $v['youtube_id'] ? 'https://www.youtube.com/watch?v=' . $v['youtube_id'] : '',
            ],
        ];
    }, $data['items'], array_keys($data['items'])),
];

include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">YouTube · @XposedLIVE</p>
    <h1>The <span class="accent">Archive</span></h1>
    <p class="lead">Every upload, newest first. Recent sessions live on Kick — the long-form breakdowns live here.</p>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <?php if (empty($data['items'])): ?>
      <div class="empty">
        <p>No videos yet — check back soon.</p>
      </div>
    <?php else: ?>
    <div class="v-grid">
      <?php foreach ($data['items'] as $v): ?>
      <article class="video-card">
        <div class="thumb">
          <?php if ($v['thumb']): ?><img src="<?= e($v['thumb']) ?>" alt="<?= e($v['title']) ?>" loading="lazy">
          <?php else: ?><div class="fake-art"></div><?php endif; ?>
          <?php if ($v['duration']): ?><span class="duration tab-nums"><?= e($v['duration']) ?></span><?php endif; ?>
        </div>
        <h3><?= e($v['title']) ?></h3>
        <div class="video-meta tab-nums">
          <?php if ($v['view_count'] > 0): ?><span><?= e(fmt_number((int)$v['view_count'])) ?> views</span><span>·</span><?php endif; ?>
          <span><?= e(date('M j, Y', strtotime($v['published_at']))) ?></span>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($data['pages'] > 1): ?>
    <nav class="pager" aria-label="Pages">
      <?php if ($page > 1): ?><a href="<?= e(url('videos.php?page=' . ($page - 1))) ?>">← Prev</a><?php endif; ?>
      <?php for ($i = 1; $i <= $data['pages']; $i++): ?>
        <?php if ($i === $page): ?><span class="cur tab-nums"><?= $i ?></span>
        <?php else: ?><a href="<?= e(url('videos.php?page=' . $i)) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $data['pages']): ?><a href="<?= e(url('videos.php?page=' . ($page + 1))) ?>">Next →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
