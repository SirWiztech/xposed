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
    ['name' => 'Progression Calculator 2', 'type' => 'On this page', 'note' => 'Martingale, Grand, Super, Fibonacci, Lucas, Trippingale, Padovan and Hollandish — full sequences scaled to your units.'],
    ['name' => 'Roulette Spin Simulator', 'type' => 'On this page', 'note' => 'Simulate European-wheel spins and see colour, dozen, column and hot/cold stats vs the expected maths.'],
    ['name' => 'Bankroll Calculator', 'type' => 'On this page', 'note' => 'See exactly how many progression steps your bankroll covers — and the step where you go broke.'],
    ['name' => 'Originals', 'type' => 'On this page', 'note' => 'Click-through boards for Chicken, Keno, Mines, Plinko, Tower and Wheel — view the full image up close.'],
    ['name' => 'Session Tracker', 'type' => 'On this page', 'note' => 'Log strategy, profit, time and notes for every session — the honest record the highlight reel never shows.'],
    ['name' => 'Challenge Tracker', 'type' => 'On this page', 'note' => 'Set a target bankroll and a daily number, log entries, and watch pace against the goal.'],
];

$pageTitle       = 'Tools — Xposed';
$metaDescription = 'Progressions calculator, session & challenge trackers, and the RTP/volatility glossary behind Xposed. 18+ — play responsibly.';
$active = 'tools';

$extraScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js',
    'assets/js/tools.js',
    'assets/js/progression2.js',
    'assets/js/roulette.js',
    'assets/js/bankroll.js',
    'assets/js/originals.js',
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
      <button type="button" class="tools-tab is-active" data-tab="calc"><?php icon('chart', 'icon tools-tab-icon'); ?>Progression Calculator</button>
      <button type="button" class="tools-tab" data-tab="prog2"><?php icon('calc', 'icon tools-tab-icon'); ?>Progression Calculator 2</button>
      <button type="button" class="tools-tab" data-tab="roulette"><?php icon('wheel', 'icon tools-tab-icon'); ?>Roulette Spin Simulator</button>
      <button type="button" class="tools-tab" data-tab="bankroll"><?php icon('bank', 'icon tools-tab-icon'); ?>Bankroll Calculator</button>
      <button type="button" class="tools-tab" data-tab="origin"><?php icon('grid', 'icon tools-tab-icon'); ?>Originals</button>
      <button type="button" class="tools-tab" data-tab="sessions"><?php icon('calendar', 'icon tools-tab-icon'); ?>Session Tracker</button>
      <button type="button" class="tools-tab" data-tab="challenge"><?php icon('trophy', 'icon tools-tab-icon'); ?>Challenge Tracker</button>
      <button type="button" class="tools-tab" data-tab="charts"><?php icon('chart', 'icon tools-tab-icon'); ?>Progression Charts</button>
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

    <!-- ======= PROGRESSION CALCULATOR 2 ======= -->
    <div class="tools-panel" id="panel-prog2">
      <div class="tool-block">
        <h3>Progression Calculator 2</h3>
        <p class="t-sub">Full breakdown of the classic escalation systems — sequences scaled to your base unit, chips and bankroll. Pacing math, not a plan of attack.</p>

        <div class="p2-top">
          <div class="p2-field"><label class="p2-label">🌐 Language</label><select class="p2-select"><option>English</option></select></div>
          <div class="p2-field"><label class="p2-label">💰 Currency</label><select class="p2-select"><option>USD ($)</option></select></div>
          <div class="p2-field"><label class="p2-label">🎯 System Focus</label><select class="p2-select"><option>-- No focus --</option></select></div>
          <div class="p2-field"><label class="p2-label" for="baseUnit">🔢 Base Unit</label><input class="p2-input" type="number" value="1" min="1" step="1" id="baseUnit"></div>
          <div class="p2-field"><label class="p2-label" for="chipsSpots">🎲 Chips / Spots</label><input class="p2-input" type="number" value="1" min="1" step="1" id="chipsSpots"></div>
          <div class="p2-field"><label class="p2-label" for="bankrollInput">🏦 Bankroll</label><input class="p2-input" type="number" value="4998" min="1" step="1" id="bankrollInput"></div>
          <button class="btn btn-primary p2-btn" type="button" id="refreshBtn">⟳ Update</button>
        </div>

        <div class="p2-grid" id="strategyContainer"></div>
        <p class="p2-foot">Recommended numbers shown · all sequences based on strategy</p>
      </div>
    </div>

    <!-- ======= ROULETTE SPIN SIMULATOR ======= -->
    <div class="tools-panel" id="panel-roulette">
      <div class="tool-block">
        <h3>Roulette Spin Simulator</h3>
        <p class="t-sub">Simulate European-wheel spins and compare observed stats against the maths. Random variance, not a winning edge.</p>

        <div class="rs-top">
          <div class="p2-field"><label class="p2-label">Wheel type</label><select class="p2-select" id="wheelType"><option value="european">European (single zero, 37 pockets)</option></select></div>
          <div class="p2-field"><label class="p2-label" for="spinCount">Number of spins</label><input class="p2-input" type="number" id="spinCount" value="100" min="1" max="5000" step="1"></div>
          <button class="btn btn-primary p2-btn" type="button" id="spinBtn">▶ Spin</button>
          <button class="btn btn-ghost p2-btn" type="button" id="resetBtn">↺ Reset</button>
          <button class="btn btn-ghost p2-btn" type="button" id="downloadBtn">⬇ Download CSV</button>
        </div>

        <div id="resultsArea"></div>

        <p class="p2-foot">European wheel · <span id="spinCountDisplay">100</span> spins · house edge 2.70%</p>
      </div>
    </div>

    <!-- ======= BANKROLL CALCULATOR ======= -->
    <div class="tools-panel" id="panel-bankroll">
      <div class="tool-block">
        <h3>Bankroll Calculator</h3>
        <p class="t-sub">Work out how many progression steps your bankroll sustains and where you break. The honest number before you spin.</p>

        <div class="bc-setup">
          <div class="bc-item">
            <label class="p2-label">Currency</label>
            <div class="bc-static">USD ($)<span>fixed</span></div>
          </div>
          <div class="bc-item">
            <label class="p2-label" for="bcBankroll">Total bankroll</label>
            <input class="p2-input bc-input" type="number" id="bcBankroll" value="500" min="1" step="0.01">
          </div>
          <div class="bc-item">
            <label class="p2-label" for="bcBaseBet">Base bet</label>
            <input class="p2-input bc-input" type="number" id="bcBaseBet" value="8" min="0.01" step="0.01">
          </div>
          <div class="bc-item">
            <label class="p2-label" for="bcMultiplier">Increase on loss (bet multiplier)</label>
            <input class="p2-input bc-input" type="number" id="bcMultiplier" value="2" min="1.01" step="0.01">
          </div>
          <div class="bc-item bc-wide">
            <label class="p2-label" for="bcReturnPct">Return on loss (%)</label>
            <div class="bc-return-row">
              <input class="p2-input" type="number" id="bcReturnPct" style="width:120px;" value="0" min="0" step="0.1">
              <span class="p2-rec">optional: % of net loss recovered</span>
              <p class="bc-hint">If set, reduces the net loss after each step (simulates cashback/rakeback).</p>
            </div>
          </div>
        </div>

        <div class="t-action-row">
          <button class="btn btn-primary p2-btn" type="button" id="bcCalcBtn">⟳ Calculate</button>
        </div>

        <div id="bkResultsArea"></div>
        <p class="p2-foot">Progression steps · "You are broke!" when bet exceeds remaining bankroll.</p>
      </div>
    </div>

    <!-- ======= ORIGINALS ======= -->
    <div class="tools-panel" id="panel-origin">
      <div class="tool-block">
        <h3>Originals</h3>
        <p class="t-sub">The classic Origin-style games anyone can play on the channel. Pick one to view the full board.</p>

        <div class="ox-grid">
          <button type="button" class="ox-tile" data-full="assets/CHICKEN.png" data-label="Chicken">
            <span class="ox-thumb"><img src="assets/CHICKEN.png" alt="Chicken" loading="lazy"></span>
            <span class="ox-name">Chicken</span>
          </button>
          <button type="button" class="ox-tile" data-full="assets/KENO.png" data-label="Keno">
            <span class="ox-thumb"><img src="assets/KENO.png" alt="Keno" loading="lazy"></span>
            <span class="ox-name">Keno</span>
          </button>
          <button type="button" class="ox-tile" data-full="assets/Mines.png" data-label="Mines">
            <span class="ox-thumb"><img src="assets/Mines.png" alt="Mines" loading="lazy"></span>
            <span class="ox-name">Mines</span>
          </button>
          <button type="button" class="ox-tile" data-full="assets/PLINKO.png" data-label="Plinko">
            <span class="ox-thumb"><img src="assets/PLINKO.png" alt="Plinko" loading="lazy"></span>
            <span class="ox-name">Plinko</span>
          </button>
          <button type="button" class="ox-tile" data-full="assets/TOWER.png" data-label="Tower">
            <span class="ox-thumb"><img src="assets/TOWER.png" alt="Tower" loading="lazy"></span>
            <span class="ox-name">Tower</span>
          </button>
          <button type="button" class="ox-tile" data-full="assets/WHEEL.png" data-label="Wheel">
            <span class="ox-thumb"><img src="assets/WHEEL.png" alt="Wheel" loading="lazy"></span>
            <span class="ox-name">Wheel</span>
          </button>
        </div>
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