(function () {
  'use strict';

  const container = document.getElementById('twitch-embed');
  if (!container) return;

  const channel = container.dataset.channel || 'xposed';
  const parents = (container.dataset.parents || 'localhost')
    .split(',')
    .map(function (s) { return s.trim(); })
    .filter(Boolean);

  function fallback(msg) {
    container.innerHTML =
      '<div class="embed-fallback">' +
      '<p>' + msg + '</p>' +
      '<a class="btn btn-ghost" href="https://twitch.tv/' + encodeURIComponent(channel) +
      '" target="_blank" rel="noopener">Open Twitch →</a>' +
      '</div>';
  }

  function create() {
    if (typeof Twitch === 'undefined' || !Twitch.Embed) {
      fallback('Loading Twitch player…');
      return;
    }
    try {
      new Twitch.Embed('twitch-embed', {
        width: '100%',
        height: '100%',
        channel: channel,
        parent: parents,
        autoplay: false,
        muted: false,
        layout: 'video'
      });
    } catch (e) {
      console.error('Twitch embed error:', e);
      fallback('Could not load the Twitch player.');
    }
  }

  create();
})();