<?php
/**
 * XPOSED — XML sitemap
 * Served as /sitemap.xml via the .htaccess rewrite. Lists every indexable
 * on-site URL: static pages, posts, products and paginated archives.
 */

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$urls = [];

// Static pages.
$static = [
    ['index.php',  '1.0', 'daily',  ''],
    ['live.php',   '0.9', 'hourly', ''],
    ['videos.php', '0.8', 'daily',  ''],
    ['store.php',  '0.8', 'daily',  ''],
    ['tools.php',  '0.8', 'daily',  ''],
    ['blog.php',   '0.8', 'daily',  ''],
];
foreach ($static as $s) {
    $urls[] = ['loc' => absolute_url($s[0]), 'priority' => $s[1], 'freq' => $s[2], 'lastmod' => $s[3]];
}

// Blog posts (published only).
$posts = db()->query(
    'SELECT slug, published_at FROM posts
     WHERE status = \'published\' AND published_at <= NOW()
     ORDER BY published_at DESC'
)->fetchAll();
foreach ($posts as $p) {
    $urls[] = [
        'loc'      => absolute_url('post.php?slug=' . rawurlencode((string)$p['slug'])),
        'priority' => '0.6',
        'freq'     => 'monthly',
        'lastmod'  => date('Y-m-d', strtotime((string)$p['published_at'])),
    ];
}

// Products (active).
foreach (Product::all() as $p) {
    $urls[] = [
        'loc'      => absolute_url('product.php?id=' . (int)$p['id']),
        'priority' => '0.6',
        'freq'     => 'monthly',
        'lastmod'  => '',
    ];
}

// Paginated archives (videos + blog) so every page is crawlable.
$videoPages = Video::paginate(1, 12)['pages'];
$blogPages  = Post::paginate(1, 9)['pages'];
for ($i = 2; $i <= $videoPages; $i++) {
    $urls[] = ['loc' => absolute_url('videos.php?page=' . $i), 'priority' => '0.4', 'freq' => 'daily', 'lastmod' => ''];
}
for ($i = 2; $i <= $blogPages; $i++) {
    $urls[] = ['loc' => absolute_url('blog.php?page=' . $i), 'priority' => '0.4', 'freq' => 'daily', 'lastmod' => ''];
}
?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= e($u['loc']) ?></loc>
<?php if ($u['lastmod'] !== ''): ?>
    <lastmod><?= e($u['lastmod']) ?></lastmod>
<?php endif; ?>
    <changefreq><?= e($u['freq']) ?></changefreq>
    <priority><?= e($u['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>