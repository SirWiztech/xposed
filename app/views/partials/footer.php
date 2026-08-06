<?php
/**
 * Shared footer + chat widget + scripts.
 */
$socials = [
    ['label' => 'Kick',       'handle' => 'kick.com/xposed',    'url' => 'https://kick.com/xposed',    'icon' => 'kick',      'primary' => true],
    ['label' => 'Twitch',     'handle' => 'twitch.tv/xposed',    'url' => 'https://twitch.tv/xposed',   'icon' => 'twitch'],
    ['label' => 'YouTube',    'handle' => '@XposedLIVE',         'url' => 'https://youtube.com/@XposedLIVE', 'icon' => 'youtube'],
    ['label' => 'TikTok',     'handle' => '@xposedhq',           'url' => 'https://tiktok.com/@xposedhq', 'icon' => 'tiktok'],
    ['label' => 'Twitter/X',  'handle' => '@Xposed',             'url' => 'https://twitter.com/Xposed', 'icon' => 'x'],
    ['label' => 'Instagram',  'handle' => '@Xposed',             'url' => 'https://instagram.com/Xposed', 'icon' => 'instagram'],
];
?>
<footer>
  <div class="wrap">
    <div class="footer-row">
      <div>
        <div class="logo" style="font-size:1.1rem;"><span class="logo-mark"><img src="<?= e(url('assets/' . rawurlencode('image-removebg-preview (7).png'))) ?>" alt="Xposed"></span>POSED</div>
        <p class="fine" style="margin-top:8px;">© <?= date('Y') ?> Xposed. All rights reserved.</p>
        <p class="fine"><?php icon('mail', 'icon icon-sm') ?> businessxposed@gmail.com</p>
      </div>
      <div class="footer-links">
        <?php foreach ($socials as $s): ?>
        <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener"><?php icon($s['icon'], 'icon icon-sm') ?><?= e($s['label']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <p class="fine" style="margin-top:24px; max-width:60ch;">
      18+. Slots and casino-related content shown on this site is for entertainment purposes.
      If gambling stops being fun, resources are available at
      <a href="https://www.begambleaware.org" target="_blank" rel="noopener" style="text-decoration:underline;">BeGambleAware.org</a>.
    </p>
  </div>

  <!-- ================= GIANT BLEED WORDMARK ================= -->
  <div class="footer-giant-mark" aria-hidden="true">
    <span id="footerGiantMark">XPOSED</span>
  </div>
</footer>

<script>
(function () {
  var wrap = document.querySelector('.footer-giant-mark');
  var text = document.getElementById('footerGiantMark');
  if (!wrap || !text) return;
  function fit() {
    text.style.transform = 'scaleX(1)';
    var wrapWidth = wrap.clientWidth;
    var textWidth = text.scrollWidth;
    if (textWidth > 0) text.style.transform = 'scaleX(' + (wrapWidth / textWidth) + ')';
  }
  fit();
  window.addEventListener('resize', fit);
  // Re-fit once webfonts load (scrollWidth is wrong before Anton is ready).
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(function () { fit(); });
  }
  window.addEventListener('load', fit);
})();
</script>

<?php include __DIR__ . '/chat-widget.php'; ?>

<?php
$jsVersion = function (string $local) {
    if (preg_match('~^https?://~i', $local)) {
        return $local;
    }
    $abs = __DIR__ . '/../../../' . $local;
    return url($local) . '?v=' . @filemtime($abs);
};
?>
<script src="<?= e($jsVersion('assets/js/main.js')) ?>" defer></script>
<script src="<?= e($jsVersion('assets/js/chat.js')) ?>" defer></script>
<?php if (isset($extraScripts)): foreach ($extraScripts as $s): ?>
<script src="<?= e($jsVersion($s)) ?>" defer></script>
<?php endforeach; endif; ?>
</body>
</html>
