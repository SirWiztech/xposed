<?php
/**
 * XPOSED — Landing / Homepage
 * Data is pulled from the database via models (seeded / synced).
 */

require __DIR__ . '/app/bootstrap.php';

require_once __DIR__ . '/app/helpers/youtube.php';
(new YoutubeSync())->ensureFresh();

$isLive     = setting('is_live', '0') === '1';
$videos     = Video::latest(5);
$products   = Product::featured(3);
$posts      = Post::latest(1);
$subs       = (int)setting('subs', 541000);
$views      = (int)setting('views', 41290000);
$yearsLive  = (int)setting('years_live', 9);
$tagline    = setting('tagline', 'Xposed — 9 years live. No filter. All in.');
$latestVideoTitle = $videos[0]['title'] ?? 'Latest upload';

$active = 'home';

$socials = [
    ['label' => 'Kick',       'handle' => 'kick.com/xposed',    'url' => 'https://kick.com/xposed',    'icon' => 'kick',      'primary' => true],
    ['label' => 'Twitch',     'handle' => 'twitch.tv/xposed',    'url' => 'https://twitch.tv/xposed',   'icon' => 'twitch'],
    ['label' => 'YouTube',    'handle' => '@XposedLIVE',         'url' => 'https://youtube.com/@XposedLIVE', 'icon' => 'youtube'],
    ['label' => 'TikTok',     'handle' => '@xposedhq',           'url' => 'https://tiktok.com/@xposedhq', 'icon' => 'tiktok'],
    ['label' => 'Twitter/X',  'handle' => '@Xposed',             'url' => 'https://twitter.com/Xposed', 'icon' => 'x'],
    ['label' => 'Instagram',  'handle' => '@Xposed',             'url' => 'https://instagram.com/Xposed', 'icon' => 'instagram'],
];

$schemaJson = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'Organization',
            '@id'         => canonical_url() . '#organization',
            'name'        => 'Xposed',
            'url'         => canonical_url(),
            'logo'        => absolute_url('assets/image-removebg-preview%20(7).png'),
            'sameAs'      => array_column($socials, 'url'),
        ],
        [
            '@type'       => 'WebSite',
            '@id'         => canonical_url() . '#website',
            'url'         => canonical_url(),
            'name'        => 'Xposed',
            'publisher'   => ['@id' => canonical_url() . '#organization'],
        ],
    ],
];

include __DIR__ . '/app/views/partials/header.php';
?>

<!--
  Self-contained additions below (kept local to this file since header.php /
  footer.php own the shared <head>/<script> and weren't in scope here).
  Relies on the existing design tokens from header.php's :root block:
  --oil-black, --signal-red, --deep-red, --glow-high, --ease
-->
<style>
  body{ font-family:'Inter', system-ui, sans-serif; }

  h1, h2, h3, .display, .logo{
    font-family:'Anton', sans-serif;
    font-weight:400;
    text-transform:uppercase;
    letter-spacing:.01em;
    line-height:.92;
  }

  .tab-nums{ font-variant-numeric:tabular-nums; font-feature-settings:'tnum' 1; }

  /* ---------- ICONS ---------- */
  .icon{ width:1.15em; height:1.15em; flex-shrink:0; vertical-align:-.2em; }
  .btn .icon{ width:16px; height:16px; }
  .view-all{ display:inline-flex; align-items:center; gap:6px; }
  .view-all .icon{ width:15px; height:15px; transition:transform .25s var(--ease, ease); }
  .view-all:hover .icon{ transform:translateX(4px); }

  .stat{ display:flex; flex-direction:column; }
  .stat .icon{ width:22px; height:22px; color:var(--signal-red, #E10600); margin-bottom:10px; }

  .connect-item{ position:relative; }
  .connect-item .connect-icon{ color:var(--signal-red, #E10600); margin-bottom:8px; }
  .connect-item .connect-icon .icon{ width:24px; height:24px; }
  .connect-item .external-icon{
    position:absolute; top:20px; right:20px; color:var(--muted, #8A8A8F);
    opacity:0; transform:translate(-4px,4px); transition:opacity .2s var(--ease, ease), transform .2s var(--ease, ease);
  }
  .connect-item .external-icon .icon{ width:16px; height:16px; }
  .connect-item:hover .external-icon{ opacity:1; transform:translate(0,0); color:var(--signal-red, #E10600); }

  .marquee-track span{ display:inline-flex !important; align-items:center; gap:8px; }
  .marquee-track .icon{ width:14px; height:14px; color:var(--signal-red, #E10600); }

  .footer-links a{ display:inline-flex; align-items:center; gap:6px; }
  .footer-links a .icon{ width:15px; height:15px; }
  .footer-row .fine .icon{ width:14px; height:14px; margin-right:5px; vertical-align:-.15em; }

  /* ---------- ANIMATED / LIVELY BACKGROUND ---------- */
  #bg-particles{
    position:fixed; inset:0; z-index:0; pointer-events:none;
    opacity:.55; mix-blend-mode:screen;
  }
  .bg-orbs{ position:fixed; inset:0; z-index:0; pointer-events:none; overflow:hidden; }
  .orb-wrap{ position:absolute; inset:0; will-change:transform; }
  .orb{
    position:absolute; border-radius:50%; opacity:.5; will-change:transform;
    background:radial-gradient(circle, var(--glow-high, rgba(225,6,0,.35)) 0%, transparent 70%);
    filter:blur(4px);
  }
  .orb-1{ width:560px; height:560px; top:-14%; left:-12%; animation:orbit-1 32s ease-in-out infinite alternate; }
  .orb-2{
    width:440px; height:440px; bottom:-16%; right:-8%; opacity:.4;
    background:radial-gradient(circle, var(--deep-red, #7A0000) 0%, transparent 70%);
    animation:orbit-2 40s ease-in-out infinite alternate;
  }
  .orb-3{ width:320px; height:320px; top:38%; left:58%; opacity:.32; animation:orbit-3 24s ease-in-out infinite alternate; }
  @keyframes orbit-1{ 0%{ transform:translate(0,0); } 100%{ transform:translate(10vw,16vh); } }
  @keyframes orbit-2{ 0%{ transform:translate(0,0); } 100%{ transform:translate(-9vw,-13vh); } }
  @keyframes orbit-3{ 0%{ transform:translate(0,0) scale(1); } 100%{ transform:translate(-14vw,9vh) scale(1.25); } }

  main#top{ position:relative; }
  main#top > section{ position:relative; z-index:2; }

  /* ---------- HERO ENTRANCE ANIMATION ---------- */
  @keyframes fadeUpIn{
    from{ opacity:0; transform:translateY(26px); }
    to{ opacity:1; transform:translateY(0); }
  }
  .hero-anim{ opacity:0; animation:fadeUpIn .9s var(--ease, ease) forwards; }
  .hero h1 .word{ display:inline-block; opacity:0; animation:fadeUpIn 1s var(--ease, ease) forwards; }

  @media (prefers-reduced-motion: reduce){
    .orb{ animation:none !important; }
    .hero-anim, .hero h1 .word{ animation:none !important; opacity:1 !important; }
  }
</style>

<main id="top">

  <canvas id="bg-particles" aria-hidden="true"></canvas>
  <div class="bg-orbs" aria-hidden="true">
    <div class="orb-wrap" id="orbWrap1"><span class="orb orb-1"></span></div>
    <div class="orb-wrap" id="orbWrap2"><span class="orb orb-2"></span></div>
    <div class="orb-wrap" id="orbWrap3"><span class="orb orb-3"></span></div>
  </div>

  <!-- ================= HERO ================= -->
  <section class="hero">
    <div class="wrap">
      <p class="eyebrow hero-anim" style="animation-delay:.05s">Twitch &amp; Kick Partner · Est. 2017</p>
      <h1>
        <span class="word" style="animation-delay:.2s">ALL IN,</span><br>
        <span class="word" style="animation-delay:.4s">NO <span class="accent">FILTER.</span></span>
      </h1>
      <p class="hero-sub hero-anim" style="animation-delay:.6s">
        Cody — known live as <strong>Xposed</strong> — has streamed nearly every day for nine years straight.
        Competitive gaming, high-stakes slots, and chat that never gets boring. This is where it all lives.
      </p>
      <div class="cta-row hero-anim" style="animation-delay:.8s">
        <a href="<?= e(url('live.php')) ?>" class="btn btn-primary <?= $isLive ? 'pulsing' : '' ?>">
          <?php icon('play') ?>
          <?= $isLive ? 'Watch Live Now' : 'Go to Live Page' ?>
        </a>
        <a href="<?= e(url('videos.php')) ?>" class="btn btn-ghost">
          <?php icon('film') ?>
          Latest Video
        </a>
      </div>
    </div>
  </section>

  <!-- ================= MARQUEE / SIGNATURE STRIP ================= -->
  <div class="marquee" aria-hidden="true">
    <div class="marquee-track">
      <?php for ($i = 0; $i < 2; $i++): ?>
      <div class="marquee-group">
        <span><?php icon('dot-live') ?><em><?= $isLive ? 'LIVE NOW' : 'OFFLINE' ?></em> ON KICK</span>
        <span><?php icon('play') ?>LATEST UPLOAD — <?= e($latestVideoTitle) ?></span>
        <span><?php icon('calendar') ?><em><?= $yearsLive ?> YEARS</em> LIVE, NO BREAKS</span>
        <span><?php icon('users') ?><?= fmt_number($subs) ?> ON YOUTUBE</span>
        <span><?php icon('trophy') ?>RECENT WIN — 340x MULTIPLIER</span>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- ================= VIDEO RAIL ================= -->
  <section id="videos" class="section-pad wrap reveal">
    <div class="section-head">
      <h2>Latest<br>Uploads</h2>
      <a href="<?= e(url('videos.php')) ?>" class="view-all">View all videos <?php icon('arrow-right') ?></a>
    </div>
    <div class="rail" id="videoRail">
      <?php foreach ($videos as $v): ?>
      <article class="video-card">
        <div class="thumb">
          <?php if ($v['thumb']): ?>
          <img src="<?= e($v['thumb']) ?>" alt="<?= e($v['title']) ?>" loading="lazy">
          <?php else: ?>
          <div class="fake-art"></div>
          <?php endif; ?>
          <?php if ($v['duration']): ?><span class="duration tab-nums"><?= e($v['duration']) ?></span><?php endif; ?>
        </div>
        <h3><?= e($v['title']) ?></h3>
        <div class="video-meta tab-nums">
          <?php if ($v['view_count'] > 0): ?><span><?= e(fmt_number((int)$v['view_count'])) ?> views</span><span>·</span><?php endif; ?>
          <span><?= e($v['published_at'] ? date('M j, Y', strtotime($v['published_at'])) : '') ?></span>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ================= ABOUT ================= -->
  <section id="about" class="section-pad wrap reveal">
    <div class="about-grid">
      <div class="portrait">
        <img src="<?= e(url('assets/Cody-image.jpg')) ?>" alt="Cody — Xposed" loading="lazy">
        <span class="mono">CODY / XPOSED</span>
      </div>
      <div class="about-copy">
        <span class="tag">About</span>
        <h2 style="font-size:clamp(2rem,4vw,3.2rem);">Nine years live,<br>zero days off the record.</h2>
        <p>
          Cody built Xposed from a bedroom setup into a Twitch-and-Kick partnership by showing up daily —
          through slow chats, bad beats, and the wins that make the highlight reels. These days the focus
          is competitive gaming and high-stakes slots content, but the throughline has never changed:
          film everything, filter nothing.
        </p>
        <p>Off stream, he's building a life with his fiancée Shania and their two sons — rarely the headline, always the reason.</p>

        <div class="stat-row tab-nums">
          <div class="stat">
            <?php icon('users') ?>
            <b data-count="<?= (int)$subs ?>">0</b>
            <span>YouTube Subscribers</span>
          </div>
          <div class="stat">
            <?php icon('eye') ?>
            <b data-count="<?= (int)$views ?>">0</b>
            <span>Total Views</span>
          </div>
          <div class="stat">
            <?php icon('calendar') ?>
            <b data-count="<?= (int)$yearsLive ?>">0</b>
            <span>Years Live</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= STORE TEASER ================= -->
  <section id="store" class="section-pad wrap reveal">
    <div class="section-head">
      <h2>From The<br>Store</h2>
      <a href="<?= e(url('store.php')) ?>" class="view-all">Shop everything <?php icon('arrow-right') ?></a>
    </div>
    <div class="store-grid">
      <?php foreach ($products as $item): ?>
      <a class="product" href="<?= e(url('product.php?id=' . (int)$item['id'])) ?>">
        <div class="swatch">
          <?php if ($item['image']): ?><img src="<?= e(upload_url($item['image'])) ?>" alt="<?= e($item['name']) ?>" loading="lazy"><?php endif; ?>
        </div>
        <span class="tag-sm"><?= e($item['category']) ?></span>
        <h3><?= e($item['name']) ?></h3>
        <div class="price tab-nums"><?= e(money((int)$item['price_cents'])) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ================= BLOG TEASER ================= -->
  <?php if ($posts): $post = $posts[0]; ?>
  <section class="section-pad wrap reveal">
    <div class="section-head">
      <h2>From The<br>Blog</h2>
      <a href="<?= e(url('blog.php')) ?>" class="view-all">Read the blog <?php icon('arrow-right') ?></a>
    </div>
    <a href="<?= e(url('post.php?slug=' . urlencode($post['slug']))) ?>" class="blog-card" style="display:grid;">
      <div class="swatch">
        <?php if ($post['cover_image']): ?><img src="<?= e(upload_url($post['cover_image'])) ?>" alt="" loading="lazy"><?php endif; ?>
      </div>
      <div>
        <span class="date"><?= e(date('F j, Y', strtotime($post['published_at']))) ?></span>
        <h3><?= e($post['title']) ?></h3>
        <p><?= e($post['excerpt']) ?></p>
      </div>
    </a>
  </section>
  <?php endif; ?>

  <!-- ================= CONNECT ================= -->
  <section id="connect" class="section-pad wrap reveal">
    <div class="section-head">
      <h2>Connect</h2>
    </div>
    <div class="connect-grid">
      <?php $socials = [
        ['label' => 'Kick',       'handle' => 'kick.com/xposed',    'url' => 'https://kick.com/xposed',    'icon' => 'kick',      'primary' => true],
        ['label' => 'Twitch',     'handle' => 'twitch.tv/xposed',    'url' => 'https://twitch.tv/xposed',   'icon' => 'twitch'],
        ['label' => 'YouTube',    'handle' => '@XposedLIVE',         'url' => 'https://youtube.com/@XposedLIVE', 'icon' => 'youtube'],
        ['label' => 'TikTok',     'handle' => '@xposedhq',           'url' => 'https://tiktok.com/@xposedhq', 'icon' => 'tiktok'],
        ['label' => 'Twitter/X',  'handle' => '@Xposed',             'url' => 'https://twitter.com/Xposed', 'icon' => 'x'],
        ['label' => 'Instagram',  'handle' => '@Xposed',             'url' => 'https://instagram.com/Xposed', 'icon' => 'instagram'],
      ]; ?>
      <?php foreach ($socials as $s): ?>
      <a class="connect-item" href="<?= e($s['url']) ?>" target="_blank" rel="noopener">
        <span class="connect-icon"><?php icon($s['icon']) ?></span>
        <span class="label"><?= e($s['label']) ?></span>
        <span class="handle"><?= e($s['handle']) ?></span>
        <?php if (!empty($s['primary'])): ?><span class="primary-badge">Primary Stream</span><?php endif; ?>
        <span class="external-icon"><?php icon('external') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<script>
(function () {
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------- Floating ember particles (lively background) ----------
  var canvas = document.getElementById('bg-particles');
  if (canvas && !prefersReducedMotion) {
    var ctx = canvas.getContext('2d');
    var w, h, particles;

    function resizeCanvas() {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
    }
    function makeParticles() {
      var count = Math.min(40, Math.floor((w * h) / 45000));
      particles = Array.from({ length: count }, function () {
        return {
          x: Math.random() * w,
          y: Math.random() * h,
          r: Math.random() * 1.6 + 0.6,
          speed: Math.random() * 0.35 + 0.08,
          drift: (Math.random() - 0.5) * 0.25,
          alpha: Math.random() * 0.5 + 0.15
        };
      });
    }
    resizeCanvas();
    makeParticles();
    window.addEventListener('resize', function () { resizeCanvas(); makeParticles(); });

    (function tick() {
      ctx.clearRect(0, 0, w, h);
      particles.forEach(function (p) {
        p.y -= p.speed;
        p.x += p.drift;
        if (p.y < -10) { p.y = h + 10; p.x = Math.random() * w; }
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(225,6,0,' + p.alpha + ')';
        ctx.fill();
      });
      requestAnimationFrame(tick);
    })();
  }

  // ---------- Ambient orb parallax on scroll ----------
  if (!prefersReducedMotion) {
    var orbWraps = [
      document.getElementById('orbWrap1'),
      document.getElementById('orbWrap2'),
      document.getElementById('orbWrap3')
    ];
    var ticking = false;
    window.addEventListener('scroll', function () {
      if (!ticking) {
        requestAnimationFrame(function () {
          var y = window.scrollY;
          orbWraps.forEach(function (wrap, i) {
            if (wrap) wrap.style.transform = 'translate3d(0,' + (y * (0.04 + i * 0.02)) + 'px,0)';
          });
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });
  }

  // ---------- Buttery smooth wheel scroll (desktop, fine pointer only) ----------
  if (!prefersReducedMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    var target = window.scrollY;
    var smoothing = false;

    function smoothStep() {
      var current = window.scrollY;
      var diff = target - current;
      if (Math.abs(diff) < 0.5) {
        window.scrollTo(0, target);
        smoothing = false;
        return;
      }
      window.scrollTo(0, current + diff * 0.14);
      requestAnimationFrame(smoothStep);
    }

    window.addEventListener('wheel', function (e) {
      e.preventDefault();
      var max = document.body.scrollHeight - window.innerHeight;
      target = Math.min(Math.max(target + e.deltaY, 0), max);
      if (!smoothing) { smoothing = true; requestAnimationFrame(smoothStep); }
    }, { passive: false });

    // keep our target in sync after native anchor-link jumps (nav, "view all", etc.)
    document.querySelectorAll('a[href*="#"]').forEach(function (link) {
      link.addEventListener('click', function () {
        setTimeout(function () { target = window.scrollY; }, 700);
      });
    });
  }
})();
</script>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>