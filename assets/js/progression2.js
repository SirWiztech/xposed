(function () {
  'use strict';

  /* ================= STRATEGY DATA (fixed sequences, base=1) ================= */
  var STRATEGIES = [
    {
      id: 'martingale',
      label: 'Martingale',
      rec: '18',
      spins: 12,
      sequence: [1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048],
      total: 4095,
      left: 903
    },
    {
      id: 'grand',
      label: 'Grand Martingale',
      rec: '18',
      spins: 11,
      sequence: [1, 3, 7, 15, 31, 63, 127, 255, 511, 1023, 2047],
      total: 4083,
      left: 915
    },
    {
      id: 'super',
      label: 'Super Martingale',
      rec: '18',
      spins: 6,
      sequence: [1, 4, 16, 64, 256, 1024],
      total: 1365,
      left: 3633
    },
    {
      id: 'fibonacci',
      label: 'Fibonacci',
      rec: '12, 13',
      spins: 17,
      sequence: [1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610, 987, 1597],
      total: 4180,
      left: 818
    },
    {
      id: 'lucas',
      label: 'Lucas',
      rec: '12, 13',
      spins: 16,
      sequence: [2, 1, 3, 4, 7, 11, 18, 29, 47, 76, 123, 199, 322, 521, 843, 1364],
      total: 3570,
      left: 1428
    },
    {
      id: 'trippingale',
      label: 'Trippingale',
      rec: '24',
      spins: 8,
      sequence: [1, 3, 9, 27, 81, 243, 729, 2187],
      total: 3280,
      left: 1718
    },
    {
      id: 'padovan',
      label: 'Padovan',
      rec: '6-8',
      spins: 27,
      sequence: [1, 1, 1, 2, 2, 3, 4, 5, 7, 9, 12, 16, 21, 28, 37, 49, 65, 86, 114, 151, 200, 265, 351, 465, 616, 816, 1081],
      total: 4408,
      left: 590
    },
    {
      id: 'hollandish',
      label: 'Hollandish',
      rec: '6-9',
      spins: 122,
      sequence: (function () {
        var exact = [];
        for (var i = 1; i <= 40; i++) {
          var v = 2 * i - 1;
          exact.push(v, v, v);
        }
        exact.push(81, 81);
        return exact;
      })(),
      total: 4962,
      left: 36
    }
  ];

  /* ================= DOM refs ================= */
  var container = document.getElementById('strategyContainer');
  var baseInput = document.getElementById('baseUnit');
  var chipsInput = document.getElementById('chipsSpots');
  var bankInput = document.getElementById('bankrollInput');
  var refreshBtn = document.getElementById('refreshBtn');

  if (!container || !baseInput || !chipsInput || !bankInput) return;

  function formatCurrency(amount) {
    return '$' + amount.toLocaleString();
  }

  function render() {
    var base = parseInt(baseInput.value, 10);
    var chips = parseInt(chipsInput.value, 10);
    var bankroll = parseInt(bankInput.value, 10);
    if (isNaN(base) || base < 1) base = 1;
    if (isNaN(chips) || chips < 1) chips = 1;
    if (isNaN(bankroll) || bankroll < 1) bankroll = 4998;

    baseInput.value = base;
    chipsInput.value = chips;
    bankInput.value = bankroll;

    var multiplier = base * chips;
    var html = '';

    for (var s = 0; s < STRATEGIES.length; s++) {
      var strat = STRATEGIES[s];
      var scaledSeq = strat.sequence.map(function (v) { return v * multiplier; });
      var scaledTotal = strat.total * multiplier;
      var scaledLeft = bankroll - scaledTotal;
      var short = scaledLeft < 0;

      var bustAt = -1;
      var spent = 0;
      for (var b = 0; b < scaledSeq.length; b++) {
        spent += scaledSeq[b];
        if (spent > bankroll) { bustAt = b; break; }
      }

      var chipsHtml = '';
      for (var i = 0; i < scaledSeq.length; i++) {
        var cls = 'p2-chip';
        if (i === 0 || i === scaledSeq.length - 1) cls += ' p2-chip-hl';
        if (i === bustAt) cls += ' p2-chip-bust';
        chipsHtml += '<span class="' + cls + '" title="' +
          (i === bustAt ? 'Bankroll runs out here (cumulative ' + formatCurrency(spent) + ')' : '') +
          '">' + formatCurrency(scaledSeq[i]) + '</span>';
      }

      var recLabel = strat.rec ? 'Recommended numbers to play · ' + strat.rec : '';
      var statusPill = bustAt === -1
        ? '<span class="p2-total p2-ok">OK · fits bankroll</span>'
        : '<span class="p2-total p2-short">Runs out at step ' + (bustAt + 1) + ' of ' + scaledSeq.length + '</span>';

      html +=
        '<div class="p2-card">' +
          '<div class="p2-head">' +
            '<span class="p2-name">' + strat.label + '</span>' +
            '<span class="p2-sub">' +
              '<span class="p2-badge">' + strat.spins + ' SPINS</span>' +
              (recLabel ? '<span class="p2-rec">' + recLabel + '</span>' : '') +
            '</span>' +
          '</div>' +
          '<div class="p2-chips">' + chipsHtml + '</div>' +
          '<div class="p2-totals">' +
            '<span class="p2-total">Bankroll: ' + formatCurrency(bankroll) + '</span>' +
            '<span class="p2-total">Total: ' + formatCurrency(scaledTotal) + '</span>' +
            (short
              ? '<span class="p2-total p2-short">Short: ' + formatCurrency(-scaledLeft) + '</span>'
              : '<span class="p2-total p2-left">Left: ' + formatCurrency(scaledLeft) + '</span>') +
            statusPill +
          '</div>' +
        '</div>';
    }

    container.innerHTML = html;
  }

  if (refreshBtn) refreshBtn.addEventListener('click', render);

  [baseInput, chipsInput, bankInput].forEach(function (inp) {
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') render();
    });
    inp.addEventListener('blur', render);
  });

  render();
})();