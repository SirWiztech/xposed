<?php
/**
 * XPOSED — Tools hub
 * Progressions calculator, session tracker, challenge tracker and
 * progression charts, alongside the RTP/volatility glossary.
 * All tools are client-side (localStorage) — entertainment, not a system.
 */

require __DIR__ . '/app/bootstrap.php';

$glossary = [
    ['term' => 'RTP', 'meta' => 'Return To Player',
     'body' => 'The theoretical percentage of all wagered money a slot returns to players over a very long time. A 96% slot returns $96 for every $100 wagered — on average, over millions of spins. It is not a guarantee for any single session.'],
    ['term' => 'Volatility', 'meta' => 'Low / Medium / High',
     'body' => 'How often and how big a slot pays. High volatility = rare but large wins and long dry spells. Low volatility = frequent, small wins. Choose based on session length, not vibes.'],
    ['term' => 'Hit Rate', 'meta' => 'Frequency',
     'body' => 'The share of rounds that return anything at all. Not every hit is a win above your stake.'],
    ['term' => 'House Edge', 'meta' => 'The maths',
     'body' => '100% minus RTP. It is the house’s built-in advantage and why no strategy can beat the house over time. Bankroll discipline is about surviving variance, not beating it.'],
    ['term' => 'Session Bankroll', 'meta' => 'Pacing',
     'body' => 'Money set aside for one session, separate from life money. Once a session budget is gone, the session is over. Chasing with rent money is how the maths wins.'],
    ['term' => 'Multiplier', 'meta' => 'x N',
     'body' => 'A win expressed as a multiple of the spin cost. Big multipliers are the highlight-reel moments — they are also the rarest ones.'],
];

$toolsUsed = [
    ['name' => 'Progression Calculator', 'type' => 'On this page', 'note' => 'Steps a Martingale / Fibonacci / street progression before you risk a single unit on it.'],
    ['name' => 'Session Tracker', 'type' => 'On this page', 'note' => 'Log strategy, profit, time and notes for every session — the honest record the highlight reel never shows.'],
    ['name' => 'Challenge Tracker', 'type' => 'On this page', 'note' => 'Set a target bankroll and a daily number, log entries, and watch pace against the goal.'],
];

$pageTitle       = 'Tools — Xposed';
$metaDescription = 'Progressions calculator, session & challenge trackers, and the RTP/volatility glossary behind Xposed. 18+ — play responsibly.';
$active = 'tools';

$extraScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js',
    'assets/js/tools.js',
];

include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">Casino Hub · 18+</p>
    <h1>The <span class="accent">Toolbox</span></h1>
    <p class="lead">Everything here is about one thing: bankroll discipline. No system beats the house — tracking and pacing just keeps the session fun.</p>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <!-- ======= TAB BAR ======= -->
    <div class="tools-tabs" role="tablist">
      <button type="button" class="tools-tab is-active" data-tab="calc">Progression Calculator</button>
      <button type="button" class="tools-tab" data-tab="sessions">Session Tracker</button>
      <button type="button" class="tools-tab" data-tab="challenge">Challenge Tracker</button>
      <button type="button" class="tools-tab" data-tab="charts">Progression Charts</button>
    </div>

    <!-- ======= CALCULATOR ======= -->
    <div class="tools-panel is-open" id="panel-calc">
      <div class="tool-block">
        <h3>Progression Calculator</h3>
        <p class="t-sub">Map out a progression before you play — step by step, unit by unit. Pacing math, not a winning system.</p>

        <form id="progression-form" class="t-form-grid">
          <div class="field">
            <label for="strategy-select">Strategy</label>
            <select id="strategy-select">
              <option value="">Select strategy…</option>
              <option value="standard_martingale">1:1 Standard Martingale</option>
              <option value="super_martingale">1:1 Super Martingale (Plus)</option>
              <option value="fibonacci_set">Fibonacci (Fixed)</option>
              <option value="2doz">2 Dozen / Column</option>
              <option value="9streets">9 Streets (Double Streets)</option>
              <option value="5ds_m">9 Streets (5 Streets)</option>
            </select>
          </div>
          <div class="field">
            <label for="base-bet-input">Base bet ($)</label>
            <input type="number" id="base-bet-input" value="5" min="0.01" step="0.01">
          </div>
          <div class="field">
            <label for="multiplier-select">Multiplier</label>
            <select id="multiplier-select">
              <option value="1.5">1.5x</option>
              <option value="2" selected>2x (standard)</option>
              <option value="2.5">2.5x</option>
              <option value="3">3x</option>
              <option value="custom">Custom per step</option>
            </select>
          </div>
          <div class="field">
            <label for="max-steps-input">Max steps</label>
            <input type="number" id="max-steps-input" value="10" min="1" max="100" step="1">
          </div>
          <div class="field full" id="custom-multiplier-section" style="display:none;">
            <label for="custom-multipliers-input">Custom multipliers (comma-separated)</label>
            <input type="text" id="custom-multipliers-input" placeholder="e.g. 2, 2.5, 3, 2, 2.5">
            <p class="t-note">Fewer values than steps? The last one repeats.</p>
          </div>
          <div class="full t-action-row">
            <button type="submit" class="btn btn-primary">Calculate progression</button>
          </div>
        </form>

        <div class="t-strat-box t-strat-box" id="strategy-description" style="display:none;">
          <h3 class="t-card-title">Strategy notes</h3>
          <p class="t-sub" id="strategy-text"></p>
        </div>

        <div id="progression-results" class="t-table-wrap" style="display:none;"></div>
      </div>
    </div>

    <!-- ======= SESSION TRACKER ======= -->
    <div class="tools-panel" id="panel-sessions">
      <div class="tool-block">
        <h3>Record a session</h3>
        <form id="checkin-form" class="t-form-grid">
          <div class="field">
            <label for="checkin-strategy">Strategy / system</label>
            <input type="text" id="checkin-strategy" placeholder="e.g. Martingale">
          </div>
          <div class="field">
            <label for="checkin-profit">Profit / Loss ($)</label>
            <input type="number" id="checkin-profit" step="0.01" placeholder="0.00">
          </div>
          <div class="field">
            <label for="checkin-hours">Hours</label>
            <input type="number" id="checkin-hours" min="0" max="23" value="0" step="1">
          </div>
          <div class="field">
            <label for="checkin-minutes">Minutes</label>
            <input type="number" id="checkin-minutes" min="0" max="59" value="0" step="1">
          </div>
          <div class="field full">
            <label for="checkin-notes">Notes</label>
            <input type="text" id="checkin-notes" placeholder="Observations, tilt, read…">
          </div>
        <div class="full t-action-row">
          <button type="submit" class="btn btn-primary">Save session</button>
        </div>
        </form>
        <div class="t-msg" id="session-msg"></div>
      </div>

      <div class="tool-block" style="margin-top:34px;">
        <div class="t-history-top">
          <h3>Session history</h3>
          <span style="flex:1;"></span>
          <button type="button" class="t-btn-small" id="clear-sessions-btn">Clear all</button>
        </div>

        <div class="kpi-row" id="summary-stats" style="margin-top:24px; display:none;"></div>

        <div id="session-list-wrap">
          <div class="t-empty" id="no-sessions">No sessions recorded yet — run the session tracker and your first check-in appears here.</div>
          <div id="session-list"></div>
        </div>
      </div>
    </div>

    <!-- ======= CHALLENGE TRACKER ======= -->
    <div class="tools-panel" id="panel-challenge">
      <div class="tool-block">
        <div class="t-history-top">
          <h3>Challenge setup</h3>
          <span style="flex:1;"></span>
          <span class="t-pill" id="challenge-name-pill">No challenge</span>
        </div>

        <form class="t-form-grid" id="challenge-setup-form">
          <div class="field">
            <label for="challenge-name">Challenge name</label>
            <input type="text" id="challenge-name" placeholder="e.g. $2K in a week">
          </div>
          <div class="field">
            <label for="challenge-start">Starting balance ($)</label>
            <input type="number" id="challenge-start" value="100" min="0" step="0.01">
          </div>
          <div class="field">
            <label for="challenge-target">Target balance ($)</label>
            <input type="number" id="challenge-target" min="0" step="0.01">
          </div>
          <div class="field">
            <label for="challenge-daily-type">Daily target type</label>
            <select id="challenge-daily-type">
              <option value="fixed">Fixed amount ($)</option>
              <option value="percentage">Percentage (%)</option>
            </select>
          </div>
          <div class="field">
            <label for="challenge-daily-val">Daily target value</label>
            <input type="number" id="challenge-daily-val" min="0" step="0.01">
          </div>
        <div class="full t-action-row">
          <button type="button" class="btn btn-primary" id="challenge-init-btn">Initialize challenge</button>
          <button type="button" class="btn btn-ghost" id="challenge-reset-btn">Reset all</button>
          <span class="t-note" id="challenge-summary"></span>
        </div>
        </form>
        <div class="t-msg" id="challenge-msg"></div>
      </div>

      <div class="tool-block" style="margin-top:34px;">
        <h3>Stats</h3>
        <div class="kpi-row" id="challenge-safe" style="margin-top:20px; display:none;">
          <div class="kpi"><b id="ch-current">—</b><span>Current balance</span></div>
          <div class="kpi"><b id="ch-total-profit">—</b><span>Total profit</span></div>
          <div class="kpi"><b id="ch-days">—</b><span>Days played</span></div>
          <div class="kpi"><b id="ch-avg">—</b><span>Avg daily profit</span></div>
          <div class="kpi"><b id="ch-est">—</b><span>Est. completion</span></div>
        </div>

        <label class="t-check">
          <input type="checkbox" id="show-graph-check"> Show bankroll graph
        </label>
        <div class="t-chart-box" id="challenge-chart-box" style="display:none;"><canvas id="challenge-chart"></canvas></div>
      </div>

      <div class="tool-block" style="margin-top:34px;">
        <h3>Daily entry</h3>
        <form class="t-form-grid" id="challenge-entry-form">
          <div class="field">
            <label for="entry-balance">Ending balance ($)</label>
            <input type="number" id="entry-balance" min="0" step="0.01">
          </div>
          <div class="field" id="daily-goal-field" style="display:none;">
            <label for="entry-goal">Daily goal override ($)</label>
            <input type="number" id="entry-goal" min="0" step="0.01">
            <p class="t-note">Auto goal = previous session’s ending balance. Override to set today’s target.</p>
          </div>
          <div class="full t-action-row">
            <button type="submit" class="btn btn-primary">Save entry</button>
          </div>
        </form>
        <div class="t-msg" id="challenge-entry-msg"></div>

        <div class="t-table-wrap">
          <table class="t-table">
            <thead>
              <tr><th>Date</th><th>Start</th><th>End</th><th>Gain/Loss</th><th>Daily goal</th><th>Action</th></tr>
            </thead>
            <tbody id="challenge-history-body"></tbody>
          </table>
          <div class="t-empty" id="no-challenge-entries">No entries yet — init a challenge and log your first day.</div>
        </div>
      </div>
    </div>

    <!-- ======= CHARTS ======= -->
    <div class="tools-panel" id="panel-charts">
      <div class="tool-block">
        <h3>Progression charts</h3>
        <p class="t-sub">Estimate how a bankroll scales toward a target, unit by unit. Suggested and estimated — not a plan of attack.</p>

        <form class="t-form-grid" id="charts-form">
          <div class="field">
            <label for="charts-bankroll">Target bankroll ($)</label>
            <input type="number" id="charts-bankroll" value="2500" min="50" step="50">
          </div>
          <div class="field">
            <label for="charts-strategy">Strategy</label>
            <select id="charts-strategy">
              <option value="doubleStreets">5 Double Streets</option>
              <option value="nineStreets">9 Streets</option>
              <option value="outsideBets">1:1 Outside Bets</option>
              <option value="dozCols">2 Dozens / Columns</option>
            </select>
          </div>
        <div class="full t-action-row">
          <button type="button" class="btn btn-primary" id="charts-update-btn">Update chart</button>
        </div>
        </form>

        <div id="charts-output" class="t-table-wrap"></div>
        <p class="t-note">Renders rows until it reaches your target (capped at 500 rows). All figures are estimates for pacing.</p>
      </div>
    </div>
  </section>

  <!-- ======= GLOSSARY ======= -->
  <section class="section-pad wrap" style="padding-top:0;">
    <div class="section-head"><h2>RTP &amp; <span class="accent">Volatility</span> Glossary</h2></div>
    <div style="border-top:1px solid rgba(255,255,255,0.08);">
      <?php foreach ($glossary as $g): ?>
      <div class="gloss">
        <h4><?= e($g['term']) ?></h4>
        <div class="meta"><?= e($g['meta']) ?></div>
        <p><?= e($g['body']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ======= WHAT XP USES ======= -->
  <section class="section-pad wrap" style="padding-top:0;">
    <div class="section-head"><h2>What <span class="accent">Xposed</span> Uses</h2></div>
    <div class="tool-grid">
      <?php foreach ($toolsUsed as $t): ?>
      <div class="tool-block" style="padding:24px;">
        <div class="tag-sm" style="font-size:.68rem; text-transform:uppercase; letter-spacing:.1em; color:var(--muted);"><?= e($t['type']) ?></div>
        <h3 style="font-size:1.1rem; margin:10px 0 8px;"><?= e($t['name']) ?></h3>
        <p style="color:var(--muted); font-size:.88rem;"><?= e($t['note']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="rg-notice">
      <h3>Play responsibly · 18+</h3>
      <p>All casino and slots content on this site is real-money gambling shown for entertainment. It carries real financial risk, and no tool on this page changes that.</p>
      <p>Set a limit before you start, never chase losses, and take breaks. If gambling stops being fun — or is starting to cause harm — free help is available:</p>
      <p>
        <a href="https://www.begambleaware.org" target="_blank" rel="noopener" style="text-decoration:underline;">BeGambleAware.org</a> ·
        <a href="https://www.gamblingtherapy.org" target="_blank" rel="noopener" style="text-decoration:underline;">GamblingTherapy.org</a>
      </p>
    </div>
  </section>
</main>

<?php
include __DIR__ . '/app/views/partials/footer.php';
?>