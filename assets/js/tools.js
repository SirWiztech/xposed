(function () {
  'use strict';

  /* ================= Helpers ================= */
  var $ = function (id) { return document.getElementById(id); };

  function num(v, def) {
    var n = parseFloat(v);
    return isFinite(n) ? n : (def || 0);
  }

  function int(v, def) {
    var n = parseInt(v, 10);
    return isFinite(n) ? n : (def || 0);
  }

  function money(n) {
    var v = Math.abs(n);
    return '$' + v.toLocaleString('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function signedMoney(n) {
    return (n >= 0 ? '+' : '-') + money(n);
  }

  function escapeHTML(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }

  var msgTimer = null;
  function showMsg(id, text, ok) {
    var el = $(id);
    if (!el) return;
    if (!text) { el.classList.remove('show'); return; }
    el.textContent = text;
    el.className = 't-msg show ' + (ok ? 'ok' : 'err');
    clearTimeout(msgTimer);
    if (ok) msgTimer = setTimeout(function () { el.classList.remove('show'); }, 4000);
  }

  /* ================= Tabs ================= */
  var TABS = ['calc', 'prog2', 'roulette', 'bankroll', 'origin', 'sessions', 'challenge', 'charts'];

  function switchTab(name) {
    TABS.forEach(function (t) {
      var panel = $('panel-' + t);
      var tab = document.querySelector('.tools-tab[data-tab="' + t + '"]');
      if (panel) panel.classList.toggle('is-open', t === name);
      if (tab) tab.classList.toggle('is-active', t === name);
    });
    if (name === 'sessions') renderSessions();
    if (name === 'challenge') renderChallenge();
    if (name === 'charts') renderCharts();
  }

  document.addEventListener('click', function (e) {
    var tab = e.target.closest ? e.target.closest('.tools-tab') : null;
    if (tab) switchTab(tab.getAttribute('data-tab'));
  });

  /* ============================================================
     PROGRESSION CALCULATOR
     ============================================================ */
  var FIB = [1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610, 987, 1597, 2584, 4181, 6765];

  var STRATEGY_DESC = {
    standard_martingale: 'Standard Martingale for 1:1 bets. Double after a loss; one win recovers the full sequence and nets one base unit.',
    super_martingale: 'Super Martingale. Double the previous total bet plus one extra base unit each step — recovers faster but escalates harder.',
    fibonacci_set: 'Fibonacci: 1, 1, 2, 3, 5, 8… The first two steps are the same size. Fixed progression, gentler than doubling.',
    '2doz': 'Two Dozen/Column bets — covers 24 numbers, pays 2:1. Net profit per win is one unit.',
    '9streets': 'Nine streets (double streets) — covers 30 numbers, pays 5:1 per street. Net profit per win is one unit.',
    '5ds_m': 'Five double streets — covers 30 numbers. Net profit per win is one unit.'
  };

  function calcProgression(strategy, baseBet, multiplier, maxSteps, customArr) {
    var schedule = [];
    var cumulativeLoss = 0;

    function stepMultiplier(step, isCustom) {
      if (isCustom) {
        var idx = step - 2;
        if (idx < 0) return 1;
        var v = num(customArr[idx], customArr[customArr.length - 1] || 2);
        return v || 2;
      }
      return num(multiplier, 2) || 2;
    }

    for (var step = 1; step <= maxSteps; step++) {
      var betUnit = 0, totalBet = 0, profitIfWin = 'N/A';
      var prev = schedule[step - 2];
      var isCustom = multiplier === 'custom';

      if (strategy === 'fibonacci_set') {
        var fibVal = FIB[Math.min(step - 1, FIB.length - 1)];
        betUnit = fibVal * baseBet;
        totalBet = betUnit;
      } else if (strategy === 'super_martingale') {
        totalBet = step === 1 ? baseBet : (prev.totalBet * stepMultiplier(step, isCustom)) + baseBet;
        betUnit = totalBet;
        profitIfWin = totalBet;
      } else if (strategy === 'standard_martingale') {
        betUnit = step === 1 ? baseBet : prev.betUnit * stepMultiplier(step, isCustom);
        totalBet = betUnit;
        profitIfWin = betUnit;
      } else if (strategy === '2doz') {
        betUnit = step === 1 ? baseBet : prev.betUnit * stepMultiplier(step, isCustom);
        totalBet = betUnit * 2;
        profitIfWin = betUnit;
      } else if (strategy === '9streets') {
        betUnit = step === 1 ? baseBet : prev.betUnit * stepMultiplier(step, isCustom);
        totalBet = betUnit * 9;
        profitIfWin = betUnit * 3;
      } else if (strategy === '5ds_m') {
        betUnit = step === 1 ? baseBet : prev.betUnit * stepMultiplier(step, isCustom);
        totalBet = betUnit * 5;
        profitIfWin = betUnit;
      } else {
        betUnit = step === 1 ? baseBet : prev.betUnit * stepMultiplier(step, isCustom);
        totalBet = betUnit;
      }

      betUnit = num(betUnit.toFixed(2), 0);
      totalBet = num(totalBet.toFixed(2), 0);
      cumulativeLoss = num((cumulativeLoss + totalBet).toFixed(2), 0);

      schedule.push({ step: step, betUnit: betUnit, totalBet: totalBet, cumulativeLoss: cumulativeLoss, profitIfWin: profitIfWin });
    }
    return schedule;
  }

  function renderProgression(schedule, baseBet, multiplier, strategyName) {
    var wrap = $('progression-results');
    if (!schedule || !schedule.length) {
      wrap.innerHTML = '<div class="t-empty">Nothing to show — pick a strategy and calculate.</div>';
      wrap.style.display = 'block';
      return;
    }
    var isCustom = multiplier === 'custom';
    var multLabel = isCustom ? 'Custom per step' : (num(multiplier, 2).toFixed(1) + 'x');
    if (strategyName === 'fibonacci_set') multLabel = 'Fixed (Fibonacci)';
    var showNet = schedule[0].profitIfWin !== 'N/A';

    var html = '<div class="t-sub" style="margin-bottom:16px;">' +
      '<span class="t-pill">' + escapeHTML(strategyName) + '</span> ' +
      'Multiplier <b>' + escapeHTML(multLabel) + '</b> · Base unit <b>' + money(baseBet) + '</b></div>' +
      '<div class="t-table-wrap" style="margin-top:0;"><table class="t-table"><thead><tr>' +
      '<th>Step</th><th>Total bet</th><th>Cumulative loss</th>' +
      (showNet ? '<th>Net profit (win)</th>' : '<th>Unit size</th>') +
      '<th>Total P/L</th></tr></thead><tbody>';

    schedule.forEach(function (item, i) {
      var prevLoss = i > 0 ? schedule[i - 1].cumulativeLoss : 0;
      var pl = showNet && typeof item.profitIfWin === 'number' ? item.profitIfWin - prevLoss : null;
      var cell = showNet ? money(item.profitIfWin) : money(item.betUnit);
      var plHtml = pl === null
        ? '<span class="t-tag">—</span>'
        : '<span class="' + (pl >= 0 ? 't-pos' : 't-neg') + '">' + signedMoney(pl) + '</span>';
      html += '<tr><td class="t-num">' + item.step + '</td>' +
        '<td class="t-num">' + money(item.totalBet) + '</td>' +
        '<td class="t-num t-neg">' + money(item.cumulativeLoss) + '</td>' +
        '<td class="t-num">' + cell + '</td>' +
        '<td class="t-num">' + plHtml + '</td></tr>';
    });

    html += '</tbody></table></div>';
    wrap.innerHTML = html;
    wrap.style.display = 'block';
  }

  function updateStrategyBox() {
    var sel = $('strategy-select');
    var box = $('strategy-description');
    var text = $('strategy-text');
    var mult = $('multiplier-select');
    var custom = $('custom-multiplier-section');

    var strat = sel.value;
    var isFixed = strat === 'fibonacci_set';

    if (strat && STRATEGY_DESC[strat]) {
      text.textContent = STRATEGY_DESC[strat];
      box.style.display = 'block';
    } else {
      box.style.display = 'none';
    }

    mult.disabled = isFixed;
    custom.style.display = (isFixed || mult.value !== 'custom') ? 'none' : 'block';
  }

  document.addEventListener('change', function (e) {
    if (e.target.id === 'strategy-select') updateStrategyBox();
    if (e.target.id === 'multiplier-select') updateStrategyBox();
  });

  var progressionForm = $('progression-form');
  if (progressionForm) {
    progressionForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var strat = $('strategy-select').value;
      if (!strat) { showMsg('progression-msg', 'Pick a strategy first.', false); return; }
      var baseBet = num($('base-bet-input').value, 5);
      var mult = $('multiplier-select').value;
      var maxSteps = int($('max-steps-input').value, 10);
      var customArr = [];
      if (mult === 'custom') {
        customArr = $('custom-multipliers-input').value.split(',').map(function (s) {
          return num(s.trim(), 0);
        }).filter(function (v) { return v > 0; });
        if (!customArr.length) customArr = [2];
      }
      var schedule = calcProgression(strat, baseBet, mult, Math.min(maxSteps, 100), customArr);
      var label = ($('strategy-select').selectedOptions[0] || {}).textContent || strat;
      renderProgression(schedule, baseBet, mult, label);
    });
  }

  /* ============================================================
     SESSION TRACKER (localStorage)
     ============================================================ */
  var SESSIONS_KEY = 'xposed_tool_sessions';

  function loadSessions() {
    try {
      return JSON.parse(localStorage.getItem(SESSIONS_KEY) || '[]');
    } catch (e) { return []; }
  }

  function saveSessions(list) {
    localStorage.setItem(SESSIONS_KEY, JSON.stringify(list));
  }

  function fmtDuration(mins) {
    var h = Math.floor(mins / 60);
    var m = mins % 60;
    return (h ? h + 'h ' : '') + m + 'm';
  }

  function renderSessions() {
    var list = loadSessions();
    var wrap = $('session-list-wrap');
    var empty = $('no-sessions');
    var stats = $('summary-stats');
    if (!wrap) return;

    if (!list.length) {
      empty.style.display = 'block';
      $('session-list').innerHTML = '';
      if (stats) stats.style.display = 'none';
      return;
    }
    empty.style.display = 'none';

    var totalProfit = list.reduce(function (s, x) { return s + num(x.profit, 0); }, 0);
    var totalMins = list.reduce(function (s, x) { return s + int(x.minutes, 0) + int(x.hours, 0) * 60; }, 0);
    var hours = totalMins / 60;
    var avgPerHour = hours > 0 ? totalProfit / hours : 0;
    var avgSession = totalProfit / list.length;

    var kpis = [
      ['Total profit/loss', signedMoney(totalProfit), totalProfit >= 0 ? 't-pos' : 't-neg'],
      ['Avg profit / hour', signedMoney(avgPerHour), avgPerHour >= 0 ? 't-pos' : 't-neg'],
      ['Sessions', String(list.length), ''],
      ['Avg per session', signedMoney(avgSession), avgSession >= 0 ? 't-pos' : 't-neg']
    ];
    stats.innerHTML = kpis.map(function (k) {
      return '<div class="kpi"><b class="' + k[2] + ' t-num">' + k[1] + '</b><span>' + k[0] + '</span></div>';
    }).join('');
    stats.style.display = 'grid';

    var html = list.map(function (s, i) {
      var profit = num(s.profit, 0);
      var cls = profit >= 0 ? 't-pos' : 't-neg';
      var mins = int(s.minutes, 0) + int(s.hours, 0) * 60;
      var perHr = mins > 0 ? profit / (mins / 60) : 0;
      var date = s.date || '';
      return '<div class="t-history-item">' +
        '<div class="t-history-top">' +
          '<span class="t-pill">' + escapeHTML(date) + '</span>' +
          '<span class="t-tag">' + escapeHTML(s.strategy || 'No system') + '</span>' +
          '<span class="t-tag">' + fmtDuration(mins) + '</span>' +
          '<span style="flex:1;"></span>' +
          '<button type="button" class="t-btn-small" data-del-session="' + i + '">Delete</button>' +
        '</div>' +
        '<div class="t-history-body">' +
          '<p class="t-tag" style="margin:0;">' + escapeHTML(s.notes || '—') + '</p>' +
          '<div class="t-history-profit"><p class="' + cls + ' t-num" style="font-size:1.5rem; margin:0;">' + signedMoney(profit) + '</p>' +
          '<p class="t-tag" style="margin:4px 0 0;">' + signedMoney(perHr) + ' /hr</p></div>' +
        '</div></div>';
    }).join('');

    $('session-list').innerHTML = html;
  }

  var checkinForm = $('checkin-form');
  if (checkinForm) {
    checkinForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var profit = num($('checkin-profit').value, 0);
      var hours = int($('checkin-hours').value, 0);
      var minutes = int($('checkin-minutes').value, 0);
      var strategy = $('checkin-strategy').value.trim() || 'No system';
      var notes = $('checkin-notes').value.trim();

      if (!profit || (hours + minutes) === 0) {
        showMsg('session-msg', 'Enter a profit/loss and a duration.', false);
        return;
      }

      var list = loadSessions();
      list.unshift({
        date: new Date().toLocaleDateString('en-CA'),
        strategy: strategy,
        profit: profit,
        hours: hours,
        minutes: minutes,
        notes: notes
      });
      saveSessions(list);
      checkinForm.reset();
      $('checkin-hours').value = '0';
      $('checkin-minutes').value = '0';
      renderSessions();
      showMsg('session-msg', 'Session saved.', true);
    });
  }

  document.addEventListener('click', function (e) {
    var del = e.target.closest ? e.target.closest('[data-del-session]') : null;
    if (del) {
      var list = loadSessions();
      list.splice(int(del.getAttribute('data-del-session'), 0), 1);
      saveSessions(list);
      renderSessions();
    }
    if (e.target.id === 'clear-sessions-btn') {
      if (confirm('Wipe ALL session history?')) {
        saveSessions([]);
        renderSessions();
        showMsg('session-msg', 'All sessions cleared.', true);
      }
    }
  });

  /* ============================================================
     CHALLENGE TRACKER (localStorage)
     ============================================================ */
  var CHALLENGE_KEY = 'xposed_tool_challenge';

  function loadChallenge() {
    try {
      return JSON.parse(localStorage.getItem(CHALLENGE_KEY) || 'null') || {};
    } catch (e) { return {}; }
  }

  function saveChallenge(c) {
    localStorage.setItem(CHALLENGE_KEY, JSON.stringify(c));
  }

  function recomputeChallenge(c) {
    if (!c || !c.name) return c;
    var entries = (c.entries || []).slice();
    var prevEnd = num(c.start, 0);
    for (var i = entries.length - 1; i >= 0; i--) {
      entries[i].start = prevEnd;
      entries[i].gain = num(entries[i].end, prevEnd) - prevEnd;
      prevEnd = num(entries[i].end, prevEnd);
    }
    c.entries = entries;
    c.current = entries.length ? num(entries[0].end, c.start) : num(c.start, 0);
    c.totalProfit = c.current - num(c.start, 0);
    c.days = entries.length;
    return c;
  }

  function challengeGoal(c) {
    if (!c || !c.name) return 0;
    var prev = c.entries && c.entries.length ? num(c.entries[0].end, c.start) : num(c.start, 0);
    if (c.dailyType === 'percentage') {
      return prev * (1 + num(c.dailyVal, 0) / 100);
    }
    return prev + num(c.dailyVal, 0);
  }

  function renderChallenge() {
    var c = loadChallenge();
    var noChallenge = !c.name;
    $('challenge-name-pill').textContent = noChallenge ? 'No challenge' : c.name;
    $('challenge-summary').textContent = noChallenge
      ? 'Set a name, start and target to begin.'
      : 'Start ' + money(c.start) + ' → Target ' + money(c.target) + ' · Current ' + money(c.current);

    $('challenge-safe').style.display = noChallenge ? 'none' : 'grid';
    if (!noChallenge) {
      $('ch-current').textContent = money(c.current);
      $('ch-total-profit').textContent = signedMoney(c.totalProfit);
      $('ch-total-profit').className = c.totalProfit >= 0 ? 't-num ' + 't-pos' : 't-num ' + 't-neg';
      $('ch-days').textContent = c.days;
      $('ch-avg').textContent = signedMoney(c.days ? c.totalProfit / c.days : 0);
      var remaining = num(c.target, 0) - c.current;
      var dailyAvg = c.days ? c.totalProfit / c.days : 0;
      $('ch-est').textContent = (remaining > 0 && dailyAvg > 0)
        ? new Date(Date.now() + Math.ceil(remaining / dailyAvg) * 86400000).toLocaleDateString('en-CA')
        : 'TBD';
    }

    var body = $('challenge-history-body');
    var empty = $('no-challenge-entries');
    if (!body) return;

    var entries = c.entries || [];
    empty.style.display = entries.length ? 'none' : 'block';
    body.innerHTML = entries.map(function (x, i) {
      var cls = x.gain >= 0 ? 't-pos' : 't-neg';
      return '<tr>' +
        '<td>' + escapeHTML(x.date) + '</td>' +
        '<td class="t-num">' + money(x.start) + '</td>' +
        '<td class="t-num">' + money(x.end) + '</td>' +
        '<td class="t-num ' + cls + '">' + signedMoney(x.gain) + '</td>' +
        '<td class="t-num">' + money(x.goal) + '</td>' +
        '<td><button type="button" class="t-btn-small" data-del-entry="' + i + '">Delete</button></td></tr>';
    }).join('');

    var goalField = $('daily-goal-field');
    if (goalField && c.name) {
      goalField.style.display = 'block';
      var auto = challengeGoal(c);
      var hint = goalField.querySelector('.t-note');
      if (hint) hint.textContent = 'Auto goal: ' + money(auto) + ' — leave the override blank to use it.';
    } else if (goalField) {
      goalField.style.display = 'none';
    }

    renderChallengeChart();
  }

  var challengeInit = $('challenge-init-btn');
  if (challengeInit) {
    challengeInit.addEventListener('click', function () {
      var name = $('challenge-name').value.trim();
      var start = num($('challenge-start').value, 0);
      var target = num($('challenge-target').value, 0);
      var dailyType = $('challenge-daily-type').value;
      var dailyVal = num($('challenge-daily-val').value, 0);
      if (!name || start <= 0 || target <= 0 || dailyVal <= 0) {
        showMsg('challenge-msg', 'Fill in name, start, target and a daily value.', false);
        return;
      }
      var c = { name: name, start: start, target: target, dailyType: dailyType, dailyVal: dailyVal, entries: [] };
      saveChallenge(recomputeChallenge(c));
      showMsg('challenge-msg', 'Challenge initialized.', true);
      renderChallenge();
    });
  }

  var challengeReset = $('challenge-reset-btn');
  if (challengeReset) {
    challengeReset.addEventListener('click', function () {
      if (confirm('Wipe the challenge and all entries?')) {
        saveChallenge({});
        showMsg('challenge-msg', 'Challenge reset.', true);
        renderChallenge();
      }
    });
  }

  var challengeEntryForm = $('challenge-entry-form');
  if (challengeEntryForm) {
    challengeEntryForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var c = loadChallenge();
      if (!c.name) { showMsg('challenge-entry-msg', 'Initialize a challenge first.', false); return; }
      var end = num($('entry-balance').value, 0);
      if (end <= 0) { showMsg('challenge-entry-msg', 'Enter a valid ending balance.', false); return; }
      var override = num($('entry-goal').value, 0);
      var goal = override > 0 ? override : challengeGoal(c);
      c.entries = c.entries || [];
      c.entries.unshift({ date: new Date().toLocaleDateString('en-CA'), end: end, goal: goal });
      saveChallenge(recomputeChallenge(c));
      challengeEntryForm.reset();
      renderChallenge();
      showMsg('challenge-entry-msg', 'Entry saved.', true);
    });
  }

  document.addEventListener('click', function (e) {
    var del = e.target.closest ? e.target.closest('[data-del-entry]') : null;
    if (del) {
      var c = loadChallenge();
      c.entries.splice(int(del.getAttribute('data-del-entry'), 0), 1);
      saveChallenge(recomputeChallenge(c));
      renderChallenge();
      showMsg('challenge-entry-msg', 'Entry deleted.', true);
    }
  });

  var showGraph = $('show-graph-check');
  if (showGraph) {
    showGraph.addEventListener('change', function () {
      $('challenge-chart-box').style.display = showGraph.checked ? 'block' : 'none';
      if (showGraph.checked) renderChallengeChart();
    });
  }

  var challengeChart = null;
  function renderChallengeChart() {
    var canvas = $('challenge-chart');
    if (!canvas || $('challenge-chart-box').style.display === 'none') return;
    var c = loadChallenge();
    var entries = (c.entries || []).slice().reverse();
    if (challengeChart) challengeChart.destroy();
    if (!entries.length || typeof Chart === 'undefined') {
      challengeChart = null;
      return;
    }
    var labels = entries.map(function (x) { return x.date; });
    var actual = entries.map(function (x) { return x.end; });
    var goals = entries.map(function (x) { return x.goal; });
    challengeChart = new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          { label: 'Bankroll', data: actual, borderColor: '#E10600', backgroundColor: 'rgba(225,6,0,0.12)', fill: true, tension: 0.3, pointRadius: 3 },
          { label: 'Daily goal', data: goals, borderColor: '#22c55e', borderDash: [5, 5], fill: false, tension: 0, pointRadius: 2 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#8A8A8F' } },
          x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#8A8A8F' } }
        },
        plugins: { legend: { labels: { color: '#F5F5F5' } } }
      }
    });
  }

  /* ============================================================
     PROGRESSION CHARTS
     ============================================================ */
  var CHARTS = {
    doubleStreets: { title: '5 Double Streets', playTime: 125, progs: 8, unitRatio: 0.0006 },
    nineStreets:   { title: '9 Streets',       playTime: 250, progs: 10, unitRatio: 0.0001 },
    outsideBets:   { title: '1:1 Outside Bets', playTime: 375, progs: 12, unitRatio: 0.00002 },
    dozCols:       { title: '2 Dozens / Columns', playTime: 75, progs: 9, unitRatio: 0.001 }
  };

  function renderCharts() {
    var target = num($('charts-bankroll').value, 2500);
    var key = $('charts-strategy').value;
    var strat = CHARTS[key] || CHARTS.doubleStreets;
    var out = $('charts-output');
    if (!out) return;

    var rows = '';
    var br = 50;
    var prevLoss = 0;
    var count = 0;

    while (br <= target && count < 500) {
      var b = br;
      if (br >= 1000) b = Math.floor(br / 100) * 100;
      else if (br >= 300) b = Math.max(300, Math.floor(br / 100) * 100);

      var unit = b * strat.unitRatio;
      var goal = b * 0.05;
      var pl = goal - prevLoss;
      prevLoss = goal;

      rows += '<tr><td class="t-num t-pos" style="font-weight:700;">' + money(b) + '</td>' +
        '<td class="t-num">' + strat.playTime + 'm</td>' +
        '<td class="t-num">' + money(unit) + '</td>' +
        '<td class="t-num">' + strat.progs + '</td>' +
        '<td class="t-num t-pos">' + money(goal) + '</td>' +
        '<td class="t-num ' + (pl >= 0 ? 't-pos' : 't-neg') + '">' + signedMoney(pl) + '</td></tr>';

      if (br < 300) br += 50;
      else if (br < 1000) br += 100;
      else br += 200;
      count++;
    }

    out.innerHTML = '<div class="t-sub" style="margin-bottom:14px;">' +
      '<span class="t-pill">' + escapeHTML(strat.title) + '</span> Target ' + money(target) + '</div>' +
      '<div class="t-table-wrap" style="margin-top:0;"><table class="t-table"><thead><tr>' +
      '<th>Bankroll</th><th>Time</th><th>Unit</th><th>Progs</th><th>Target (5%)</th><th>Profit/Loss</th>' +
      '</tr></thead><tbody>' + rows +
      (count >= 500 ? '<tr><td colspan="6" class="t-empty">Capped at 500 rows.</td></tr>' : '') +
      '</tbody></table></div>';
  }

  var chartsBtn = $('charts-update-btn');
  if (chartsBtn) chartsBtn.addEventListener('click', renderCharts);

  /* ============================================================
     INIT
     ============================================================ */
  renderSessions();
  renderChallenge();
  renderCharts();
})();