<?php
/**
 * XPOSED — Single blog post (long-form reader).
 */

require __DIR__ . '/app/bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$post = $slug !== '' ? Post::findBySlug($slug) : null;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Not found — Xposed';
    include __DIR__ . '/app/views/partials/header.php';
    echo '<main><section class="page-hero wrap"><h1>Not <span class="accent">Found</span></h1>'
       . '<p class="lead">That post doesn’t exist. <a href="' . e(url('blog.php')) . '" style="text-decoration:underline;">Back to the blog →</a></p></section></main>';
    include __DIR__ . '/app/views/partials/footer.php';
    exit;
}

$body = Post::renderBody((string)$post['body']);

$pageTitle       = $post['title'] . ' — Xposed Blog';
$metaDescription = mb_substr(strip_tags($post['excerpt'] ?: ''), 0, 150);
$active = 'blog';
$ogType = 'article';
$publishedTime = date('c', strtotime((string)$post['published_at']));
if (!empty($post['cover_image'])) {
    $ogImage = $post['cover_image'];
}

$schemaJson = [
    '@context' => 'https://schema.org',
    '@type'    => 'Article',
    'headline' => $post['title'],
    'description' => mb_substr(strip_tags($post['excerpt'] ?: ''), 0, 200),
    'datePublished' => $publishedTime,
    'dateModified'  => $publishedTime,
    'image'   => !empty($post['cover_image']) ? absolute_url($post['cover_image']) : default_og_image(),
    'author'  => ['@type' => 'Organization', 'name' => 'Xposed'],
    'publisher' => ['@type' => 'Organization', 'name' => 'Xposed',
        'logo' => ['@type' => 'ImageObject', 'url' => absolute_url('assets/image-removebg-preview%20(7).png')]],
    'mainEntityOfPage' => canonical_url(),
];

include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <article class="section-pad wrap reader">
    <div class="date tab-nums"><?= e(date('F j, Y', strtotime($post['published_at']))) ?></div>
    <h1><?= e($post['title']) ?></h1>

    <?php if ($post['cover_image']): ?>
    <div class="cover"><img src="<?= e(upload_url($post['cover_image'])) ?>" alt="" loading="lazy"></div>
    <?php else: ?>
    <div class="cover"></div>
    <?php endif; ?>

    <div class="body"><?= $body ?></div>

    <p style="margin-top:48px; border-top:1px solid rgba(255,255,255,0.08); padding-top:24px;">
      <a href="<?= e(url('blog.php')) ?>" class="view-all" style="text-decoration:underline;">← All posts</a>
    </p>
  </article>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
