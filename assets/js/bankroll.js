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
    var lastAffordable = null;

while (remaining >= bet) {
      var stepNo = steps.length + 1;
      var totalBets = 0;
      for (var k = 0; k < steps.length; k++) totalBets += steps[k].bet;
      totalBets += bet;
      var netLoss = totalBets * (1 - returnFactor);
      steps.push({ step: stepNo, bet: bet, netLoss: netLoss, remaining: remaining - bet });
      remaining = remaining - bet;
      if (remaining < bet * multiplier) break;
      bet = bet * multiplier;
    }

    render(steps, multiplier, remaining);
  }

  function render(steps, multiplier, remaining) {
    if (!steps || !steps.length) {
      resultsArea.innerHTML = '<div class="rs-empty">Base bet already exceeds your bankroll — nothing to cover.</div>';
      return;
    }

    var cover = steps.length;
    var html =
      '<div class="bc-head"><h3 class="bc-title">Your results</h3>' +
      '<span class="bc-cover">You can cover ' + cover + ' progression' + (cover > 1 ? 's' : '') + '</span></div>' +
      '<div class="bc-table-wrap"><table class="bc-table"><thead><tr>' +
      '<th>Step</th><th>Bet size</th><th>Net loss</th></tr></thead><tbody>';

    var brokeShown = false;
    for (var i = 0; i < steps.length; i++) {
      var s = steps[i];
      var nextBet = i < steps.length - 1 ? steps[i + 1].bet : stepBetAfter(steps[i].bet, multiplier);
      if (!brokeShown && s.remaining < nextBet) {
        html += '<tr class="bc-broke"><td>' + s.step + '</td><td>' + money(s.bet) + '</td><td>You are broke!</td></tr>';
        brokeShown = true;
      } else {
        html += '<tr><td>' + s.step + '</td><td>' + money(s.bet) + '</td><td>' + money(s.netLoss) + '</td></tr>';
      }
    }

    html += '</tbody></table></div>';
    resultsArea.innerHTML = html;
  }

  function stepBet(current, multiplier) {
    return current * multiplier;
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