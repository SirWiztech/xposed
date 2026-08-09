(function () {
  'use strict';

  var tiles = Array.prototype.slice.call(document.querySelectorAll('.ox-tile'));
  if (!tiles.length) return;

  var backdrop = document.createElement('div');
  backdrop.className = 'ox-backdrop';
  backdrop.setAttribute('role', 'dialog');
  backdrop.setAttribute('aria-modal', 'true');
  backdrop.innerHTML =
    '<button type="button" class="ox-close" aria-label="Close">&times;</button>' +
    '<button type="button" class="ox-nav ox-prev" aria-label="Previous">&#8249;</button>' +
    '<button type="button" class="ox-nav ox-next" aria-label="Next">&#8250;</button>' +
    '<div class="ox-stage"><img class="ox-img" src="" alt=""></div>' +
    '<div class="ox-caption"></div>';
  document.body.appendChild(backdrop);

  var img = backdrop.querySelector('.ox-img');
  var caption = backdrop.querySelector('.ox-caption');
  var closeBtn = backdrop.querySelector('.ox-close');
  var prevBtn = backdrop.querySelector('.ox-prev');
  var nextBtn = backdrop.querySelector('.ox-next');

  var current = -1;

  function urlOf(file) {
    return new URL(file, window.location.href).href;
  }

  function showAt(i) {
    if (i < 0) i = tiles.length - 1;
    if (i >= tiles.length) i = 0;
    current = i;
    var tile = tiles[i];
    var full = tile.getAttribute('data-full') || '';
    img.src = urlOf(full);
    img.alt = tile.getAttribute('data-label') || '';
    caption.textContent = (i + 1) + ' / ' + tiles.length + ' — ' + (tile.getAttribute('data-label') || 'Original');
    document.body.classList.add('ox-open');
  }

  function close() {
    current = -1;
    img.src = '';
    document.body.classList.remove('ox-open');
  }

  function onTileClick(e) {
    var tile = e.currentTarget;
    showAt(tiles.indexOf(tile));
  }

  tiles.forEach(function (tile) {
    tile.addEventListener('click', onTileClick);
  });

  closeBtn.addEventListener('click', close);
  prevBtn.addEventListener('click', function () { showAt(current - 1); });
  nextBtn.addEventListener('click', function () { showAt(current + 1); });

  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) close();
  });

  document.addEventListener('keydown', function (e) {
    if (!document.body.classList.contains('ox-open')) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') showAt(current - 1);
    if (e.key === 'ArrowRight') showAt(current + 1);
  });
})();