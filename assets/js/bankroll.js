(function () {
  'use strict';

  var bankInput = document.getElementById('bcBankroll');
  var baseInput = document.getElementById('bcBaseBet');
  var multiInput = document.getElementById('bcMultiplier');
  var returnInput = document.getElementById('bcReturnPct');
  var calcBtn = document.getElementById('bcCalcBtn');
  var resultsArea = document.getElementById('bkResultsArea');

  if (!bankInput || !baseInput || !multiInput || !calcBtn || !resultsArea) return;

  function money(v) {
    return '$' + v.toFixed(2);
  }

  function calculate() {
    var bankroll = parseFloat(bankInput.value);
    var baseBet = parseFloat(baseInput.value);
    var multiplier = parseFloat(multiInput.value);
    var returnPct = parseFloat(returnInput.value);

    if (isNaN(bankroll) || bankroll < 0.01) bankroll = 500;
    if (isNaN(baseBet) || baseBet < 0.01) baseBet = 8;
    if (isNaN(multiplier) || multiplier < 1.01) multiplier = 2;
    if (isNaN(returnPct) || returnPct < 0) returnPct = 0;
    if (returnPct > 100) returnPct = 100;

    bankInput.value = bankroll;
    baseInput.value = baseBet;
    multiInput.value = multiplier;
    returnInput.value = returnPct;

    var returnFactor = returnPct / 100;

    var remaining = bankroll;
    var bet = baseBet;
    var steps = [];
    var totalWagered = 0;

    while (remaining >= bet) {
      totalWagered += bet;
      remaining -= bet;
      steps.push({
        step: steps.length + 1,
        bet: bet,
        netLoss: totalWagered * (1 - returnFactor),
        remaining: remaining
      });
      if (remaining < bet * multiplier) break;
      bet = bet * multiplier;
    }

    render(steps, multiplier, remaining, totalWagered);
  }

  function render(steps, multiplier, remaining, totalWagered) {
    if (!steps || !steps.length) {
      resultsArea.innerHTML = '<div class="rs-empty">Base bet already exceeds your bankroll — nothing to cover.</div>';
      return;
    }

    var cover = steps.length;
    var nextBet = steps[steps.length - 1].bet * multiplier;

    var html =
      '<div class="bc-head">' +
        '<h3 class="bc-title">Your results</h3>' +
        '<span class="bc-cover">Covers ' + cover + (cover > 1 ? ' steps' : ' step') + ' · next bet ' + money(nextBet) + ' unfunded</span>' +
      '</div>' +
      '<div class="bc-table-wrap"><table class="bc-table"><thead><tr>' +
        '<th>Step</th><th>Bet size</th><th>Net loss</th><th>Remaining</th><th>Status</th>' +
      '</tr></thead><tbody>';

    for (var i = 0; i < steps.length; i++) {
      var s = steps[i];
      var isBroke = i === steps.length - 1;
      html += '<tr' + (isBroke ? ' class="bc-broke"' : '') + '>' +
        '<td>' + s.step + '</td>' +
        '<td>' + money(s.bet) + '</td>' +
        '<td>' + money(s.netLoss) + '</td>' +
        '<td>' + money(s.remaining) + '</td>' +
        '<td>' + (isBroke ? 'Broke' : '') + '</td>' +
      '</tr>';
    }

    html += '<tr class="bc-total">' +
      '<td>Total</td><td>' + money(totalWagered) + '</td>' +
      '<td>' + money(steps[steps.length - 1].netLoss) + '</td><td>' + money(remaining) + '</td><td></td>' +
    '</tr>';

    html += '</tbody></table></div>';
    resultsArea.innerHTML = html;
  }

  calcBtn.addEventListener('click', calculate);

  [bankInput, baseInput, multiInput, returnInput].forEach(function (inp) {
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') calculate();
    });
    inp.addEventListener('blur', calculate);
  });

  calculate();
})();