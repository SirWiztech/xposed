<?php
/**
 * XPOSED — Live page
 * Side-by-side Kick iframe + Twitch Embed API players.
 * Live status currently driven by the admin toggle (settings.is_live).
 */

require __DIR__ . '/app/bootstrap.php';

$isLive       = setting('is_live', '0') === '1';
$channel      = config('kick.channel');
$player       = config('kick.player');
$twitchChan   = config('twitch.channel');

// Twitch requires the exact parent domain — include configured parents plus the site host.
$twitchParents = (array)(config('twitch.parents') ?: []);
$siteHost      = parse_url((string)config('site_url'), PHP_URL_HOST);
if ($siteHost) {
    $twitchParents[] = $siteHost;
}
$twitchParents = array_values(array_unique($twitchParents));

$vods = Video::latest(3);

$pageTitle       = 'Live — Xposed';
$metaDescription = 'Watch Xposed live — Kick and Twitch. High-stakes slots, competitive gaming, and chat that never gets boring.';
$active = 'live';

$extraScripts = [
    'https://embed.twitch.tv/embed/v1.js',
    'assets/js/live.js',
];

include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">Kick · Twitch</p>
    <h1><?= $isLive ? 'He’s <span class="accent">LIVE</span> right now.' : 'Live — <span class="accent">not right now.</span>' ?></h1>
    <p class="lead">
      <?= $isLive
          ? 'Stream is rolling on Kick and Twitch. Join the chat — it’s the best part.'
          : 'He’s offline at the moment. Check the LIVE pill in the nav, or catch the latest VOD below.' ?>
    </p>
  </section>

  <div class="onair <?= $isLive ? 'live' : '' ?> wrap">
    <span class="dot"></span>
    <span><?= $isLive ? 'ON AIR — LIVE NOW' : 'OFFLINE' ?></span>
    <span class="statline"><?= $isLive ? 'Streaming live now' : 'Last VOD below' ?></span>
  </div>

  <section class="section-pad wrap" style="padding-top:40px;">
    <div class="stream-grid">
      <!-- KICK -->
      <div class="stream-card is-kick">
        <div class="stream-head">
          <span class="stream-badge kick">KICK</span>
          <span class="stream-state <?= $isLive ? 'live' : '' ?>">
            <span class="stream-dot"></span><?= $isLive ? 'LIVE' : 'OFFLINE' ?>
          </span>
          <span class="stream-handle">kick.com/<?= e($channel) ?></span>
        </div>
        <div class="stream-player">
          <iframe src="<?= e($player) ?>?autoplay=false&muted=false"
                  title="Xposed live on Kick" frameborder="0" scrolling="no"
                  allow="autoplay; fullscreen; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>

      <!-- TWITCH -->
      <div class="stream-card is-twitch">
        <div class="stream-head">
          <span class="stream-badge twitch">TWITCH</span>
          <span class="stream-state <?= $isLive ? 'live' : '' ?>">
            <span class="stream-dot"></span><?= $isLive ? 'LIVE' : 'OFFLINE' ?>
          </span>
          <span class="stream-handle">twitch.tv/<?= e($twitchChan) ?></span>
        </div>
        <div class="stream-player"
             id="twitch-embed"
             data-channel="<?= e($twitchChan) ?>"
             data-parents="<?= e(implode(',', $twitchParents)) ?>"></div>
      </div>
    </div>

    <?php if (!$isLive && $vods): ?>
    <div class="section-head" style="margin-top:80px;">
      <h2>Latest<br>Sessions</h2>
      <a href="<?= e(url('videos.php')) ?>" class="view-all">All videos →</a>
    </div>
    <div class="v-grid">
      <?php foreach ($vods as $v): ?>
      <article class="video-card">
        <div class="thumb">
          <?php if ($v['thumb']): ?><img src="<?= e($v['thumb']) ?>" alt="<?= e($v['title']) ?>" loading="lazy">
          <?php else: ?><div class="fake-art"></div><?php endif; ?>
          <?php if ($v['duration']): ?><span class="duration tab-nums"><?= e($v['duration']) ?></span><?php endif; ?>
        </div>
        <h3><?= e($v['title']) ?></h3>
        <div class="video-meta tab-nums"><span><?= e(date('M j, Y', strtotime($v['published_at']))) ?></span></div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
