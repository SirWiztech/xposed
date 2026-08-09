(function () {
  'use strict';

  var EUROPEAN_NUMBERS = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36];

  function getColor(n) {
    if (n === 0) return 'green';
    var reds = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
    return reds.indexOf(n) >= 0 ? 'red' : 'black';
  }
  function getParity(n) {
    if (n === 0) return 'zero';
    return n % 2 === 0 ? 'even' : 'odd';
  }
  function getLowHigh(n) {
    if (n === 0) return 'zero';
    return (n >= 1 && n <= 18) ? 'low' : 'high';
  }
  function getDozen(n) {
    if (n === 0) return 'zero';
    if (n >= 1 && n <= 12) return '1st';
    if (n >= 13 && n <= 24) return '2nd';
    return '3rd';
  }
  function getColumn(n) {
    if (n === 0) return 'zero';
    var col = n % 3;
    if (col === 1) return '1st';
    if (col === 2) return '2nd';
    return '3rd';
  }

  function generateSpins(count) {
    var spins = [];
    for (var i = 0; i < count; i++) {
      var idx = Math.floor(Math.random() * EUROPEAN_NUMBERS.length);
      spins.push(EUROPEAN_NUMBERS[idx]);
    }
    return spins;
  }

  function analyze(spins) {
    var total = spins.length;
    var red = 0, black = 0, green = 0;
    var odd = 0, even = 0, zeroParity = 0;
    var low = 0, high = 0, zeroLH = 0;
    var d1 = 0, d2 = 0, d3 = 0, zeroD = 0;
    var c1 = 0, c2 = 0, c3 = 0, zeroC = 0;

    var hitMap = {};
    EUROPEAN_NUMBERS.forEach(function (n) { hitMap[n] = 0; });

    for (var i = 0; i < spins.length; i++) {
      var n = spins[i];
      hitMap[n] = (hitMap[n] || 0) + 1;

      var col = getColor(n);
      if (col === 'red') red++;
      else if (col === 'black') black++;
      else green++;

      var par = getParity(n);
      if (par === 'odd') odd++;
      else if (par === 'even') even++;
      else zeroParity++;

      var lh = getLowHigh(n);
      if (lh === 'low') low++;
      else if (lh === 'high') high++;
      else zeroLH++;

      var dz = getDozen(n);
      if (dz === '1st') d1++;
      else if (dz === '2nd') d2++;
      else if (dz === '3rd') d3++;
      else zeroD++;

      var colIdx = getColumn(n);
      if (colIdx === '1st') c1++;
      else if (colIdx === '2nd') c2++;
      else if (colIdx === '3rd') c3++;
      else zeroC++;
    }

    var sorted = Object.keys(hitMap).map(function (k) {
      return [parseInt(k, 10), hitMap[k]];
    }).sort(function (a, b) { return (b[1] - a[1]) || (a[0] - b[0]); });

    var hot = [], cold = [];
    for (var i = 0; i < sorted.length && hot.length < 3; i++) {
      if (sorted[i][1] > 0) hot.push(sorted[i]);
    }
    for (var i = sorted.length - 1; i >= 0 && cold.length < 3; i--) {
      if (sorted[i][1] === 0) cold.push(sorted[i]);
    }

    var pct = function (v) { return total ? ((v / total) * 100) : 0; };

    return {
      total: total,
      colour: { red: red, black: black, green: green, redPct: pct(red), blackPct: pct(black), greenPct: pct(green) },
      parity: { odd: odd, even: even, zero: zeroParity, oddPct: pct(odd), evenPct: pct(even), zeroPct: pct(zeroParity) },
      lowhigh: { low: low, high: high, zero: zeroLH, lowPct: pct(low), highPct: pct(high), zeroPct: pct(zeroLH) },
      dozens: { d1: d1, d2: d2, d3: d3, zero: zeroD, d1Pct: pct(d1), d2Pct: pct(d2), d3Pct: pct(d3), zeroPct: pct(zeroD) },
      columns: { c1: c1, c2: c2, c3: c3, zero: zeroC, c1Pct: pct(c1), c2Pct: pct(c2), c3Pct: pct(c3), zeroPct: pct(zeroC) },
      hot: hot,
      cold: cold,
      history: spins.slice(-100)
    };
  }

  var area = document.getElementById('resultsArea');
  var spinBtn = document.getElementById('spinBtn');
  var resetBtn = document.getElementById('resetBtn');
  var downloadBtn = document.getElementById('downloadBtn');
  var spinInput = document.getElementById('spinCount');
  var countDisplay = document.getElementById('spinCountDisplay');

  if (!area || !spinBtn) return;

  function fmt(v) { return v.toFixed(2); }

  function row(label, hit, obs, exp) {
    return '<div class="rs-row"><span class="rs-label">' + label + '</span>' +
      '<span class="rs-nums">' + hit + ' <span class="rs-obs">' + obs + '</span> <span class="rs-exp">' + exp + '</span></span></div>';
  }

  function render(spins) {
    if (!spins || !spins.length) {
      area.innerHTML = '<div class="rs-empty">No spins yet. Click "Spin" to simulate.</div>';
      if (countDisplay) countDisplay.textContent = '0';
      return;
    }

    var stats = analyze(spins);
    if (countDisplay) countDisplay.textContent = stats.total;
    var T = stats.total;

    var expCol = { red: 48.65, black: 48.65, green: 2.70 };
    var expParity = { odd: 48.65, even: 48.65, zero: 2.70 };
    var expLH = { low: 48.65, high: 48.65, zero: 2.70 };
    var expDozen = { d1: 32.43, d2: 32.43, d3: 32.43, zero: 2.70 };
    var expCols = { c1: 32.43, c2: 32.43, c3: 32.43, zero: 2.70 };

    function pLabel(key) {
      return 'Expected ' + key;
    }

    var html =
      '<div class="rs-cards">' +
        '<div class="rs-card"><div class="rs-title">🎨 Colour</div>' +
          row('Red', stats.colour.red, fmt(stats.colour.redPct) + '%', pLabel(fmt(expCol.red) + '%')) +
          row('Black', stats.colour.black, fmt(stats.colour.blackPct) + '%', pLabel(fmt(expCol.black) + '%')) +
          row('Green', stats.colour.green, fmt(stats.colour.greenPct) + '%', pLabel(fmt(expCol.green) + '%')) +
        '</div>' +
        '<div class="rs-card"><div class="rs-title">🔢 Odd / Even</div>' +
          row('Odd', stats.parity.odd, fmt(stats.parity.oddPct) + '%', pLabel(fmt(expParity.odd) + '%')) +
          row('Even', stats.parity.even, fmt(stats.parity.evenPct) + '%', pLabel(fmt(expParity.even) + '%')) +
          row('Zero', stats.parity.zero, fmt(stats.parity.zeroPct) + '%', pLabel(fmt(expParity.zero) + '%')) +
        '</div>' +
        '<div class="rs-card"><div class="rs-title">⬆ Low / High</div>' +
          row('Low (1–18)', stats.lowhigh.low, fmt(stats.lowhigh.lowPct) + '%', pLabel(fmt(expLH.low) + '%')) +
          row('High (19–36)', stats.lowhigh.high, fmt(stats.lowhigh.highPct) + '%', pLabel(fmt(expLH.high) + '%')) +
          row('Zero', stats.lowhigh.zero, fmt(stats.lowhigh.zeroPct) + '%', pLabel(fmt(expLH.zero) + '%')) +
        '</div>' +
        '<div class="rs-card"><div class="rs-title">📊 Dozens</div>' +
          row('1st (1–12)', stats.dozens.d1, fmt(stats.dozens.d1Pct) + '%', pLabel(fmt(expDozen.d1) + '%')) +
          row('2nd (13–24)', stats.dozens.d2, fmt(stats.dozens.d2Pct) + '%', pLabel(fmt(expDozen.d2) + '%')) +
          row('3rd (25–36)', stats.dozens.d3, fmt(stats.dozens.d3Pct) + '%', pLabel(fmt(expDozen.d3) + '%')) +
          row('Zero', stats.dozens.zero, fmt(stats.dozens.zeroPct) + '%', pLabel(fmt(expDozen.zero) + '%')) +
        '</div>' +
        '<div class="rs-card"><div class="rs-title">📐 Columns</div>' +
          row('1st column', stats.columns.c1, fmt(stats.columns.c1Pct) + '%', pLabel(fmt(expCols.c1) + '%')) +
          row('2nd column', stats.columns.c2, fmt(stats.columns.c2Pct) + '%', pLabel(fmt(expCols.c2) + '%')) +
          row('3rd column', stats.columns.c3, fmt(stats.columns.c3Pct) + '%', pLabel(fmt(expCols.c3) + '%')) +
          row('Zero', stats.columns.zero, fmt(stats.columns.zeroPct) + '%', pLabel(fmt(expCols.zero) + '%')) +
        '</div>' +
      '</div>' +
      '<div class="rs-hc">' +
        '<div class="rs-hc-block"><h4 class="rs-hc-title">🔥 Hot numbers</h4>' +
          (stats.hot.length ? stats.hot.map(function (h) {
            return '<div class="rs-hc-item"><span class="rs-hc-num">' + h[0] + '</span><span>' + h[1] + ' hit' + (h[1] > 1 ? 's' : '') + ' · ' + fmt((h[1] / T) * 100) + '%</span></div>';
          }).join('') : '<div class="rs-hc-item"><span class="rs-hc-num">—</span><span>no hits yet</span></div>') +
        '</div>' +
        '<div class="rs-hc-block"><h4 class="rs-hc-title">❄️ Cold numbers</h4>' +
          (stats.cold.length ? stats.cold.map(function (h) {
            return '<div class="rs-hc-item"><span class="rs-hc-num">' + h[0] + '</span><span>' + h[1] + ' hit' + (h[1] > 1 ? 's' : '') + ' · ' + fmt((h[1] / T) * 100) + '%</span></div>';
          }).join('') : '<div class="rs-hc-item"><span class="rs-hc-num">—</span><span>no cold numbers</span></div>') +
        '</div>' +
      '</div>' +
      '<div class="rs-history"><div class="rs-title">📜 Spin history</div>' +
        '<div class="rs-history-nums">' +
          stats.history.map(function (n) { return '<span class="rs-hist-chip">' + n + '</span>'; }).join('') +
        '</div>' +
      '</div>';

    area.innerHTML = html;
  }

  var currentSpins = [];

  function spin() {
    var count = parseInt(spinInput.value, 10);
    if (isNaN(count) || count < 1) count = 1;
    if (count > 5000) count = 5000;
    spinInput.value = count;

    currentSpins = currentSpins.concat(generateSpins(count));
    if (currentSpins.length > 5000) currentSpins = currentSpins.slice(-5000);
    render(currentSpins);
  }

  function resetSpins() {
    currentSpins = [];
    render(currentSpins);
  }

  function downloadCSV() {
    if (!currentSpins.length) {
      alert('No spins to export. Generate some spins first.');
      return;
    }
    var csv = 'Spin,Number,Colour,Parity,LowHigh,Dozen,Column\n';
    currentSpins.forEach(function (n, idx) {
      csv += (idx + 1) + ',' + n + ',' + getColor(n) + ',' + getParity(n) + ',' + getLowHigh(n) + ',' + getDozen(n) + ',' + getColumn(n) + '\n';
    });
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'roulette_spins_' + currentSpins.length + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  spinBtn.addEventListener('click', spin);
  if (resetBtn) resetBtn.addEventListener('click', resetSpins);
  if (downloadBtn) downloadBtn.addEventListener('click', downloadCSV);
  spinInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') spin(); });

  setTimeout(function () {
    currentSpins = generateSpins(100);
    render(currentSpins);
  }, 50);
})();