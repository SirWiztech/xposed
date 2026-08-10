(function () {
  'use strict';

  var launcher = document.getElementById('aiLauncher');
  var panel = document.getElementById('aiPanel');
  var navLink = document.getElementById('aiNavLink');
  if (!panel) return;

  var body = document.getElementById('aiBody');
  var form = document.getElementById('aiForm');
  var input = document.getElementById('aiInput');
  var newChat = document.getElementById('aiNewChat');
  var closeBtn = document.getElementById('aiClose');

  var ENDPOINT = panel.dataset.endpoint || '/xposed/ai-chat.php';
  var STORAGE_KEY = 'xposed.ai.history';
  var GREETING = 'Yo, welcome to the Xposed HQ 🎰✨ I’m your AI wingman — ask me anything about the stream, schedule, merch, casino & crash strategies, bankroll tips, or Cody’s latest moves. What are we getting into?';
  var SUGGESTIONS = ['Give me a quick casino strategy', 'What is RTP / house edge?', 'Best way to run a bankroll', 'How do I get in touch for business?'];

  var history = [];

  function saveHistory() {
    try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history.slice(-20))); } catch (e) {}
  }
  function loadHistory() {
    try {
      var raw = sessionStorage.getItem(STORAGE_KEY);
      var arr = raw ? JSON.parse(raw) : [];
      history = Array.isArray(arr) ? arr.slice(-20) : [];
    } catch (e) { history = []; }
  }

  function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }
  function autolink(text) {
    return escapeHTML(text).replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
  }

  function addBubble(text, who, safe) {
    var div = document.createElement('div');
    div.className = 'bubble ' + who;
    if (safe === false) {
      div.textContent = text;
    } else {
      div.innerHTML = autolink(text);
    }
    body.appendChild(div);
    scrollToBottom();
    return div;
  }

  // Defer the layout read to the next frame so rapid bubbles don't force reflow per append.
  var scrollPending = false;
  function scrollToBottom() {
    if (scrollPending) return;
    scrollPending = true;
    requestAnimationFrame(function () {
      scrollPending = false;
      body.scrollTop = body.scrollHeight;
    });
  }

  function typingDots() {
    var wrap = document.createElement('div');
    wrap.className = 'bubble bot typing';
    var dots = document.createElement('span');
    dots.className = 'typing-dots';
    dots.innerHTML = '<span></span><span></span><span></span>';
    wrap.appendChild(dots);
    body.appendChild(wrap);
    scrollToBottom();
    return wrap;
  }

  function renderSuggestions() {
    var row = document.createElement('div');
    row.className = 'chip-row';
    SUGGESTIONS.forEach(function (s) {
      var c = document.createElement('button');
      c.className = 'chip';
      c.type = 'button';
      c.textContent = s;
      c.addEventListener('click', function () {
        input.value = s;
        form.dispatchEvent(new Event('submit'));
      });
      row.appendChild(c);
    });
    body.appendChild(row);
    scrollToBottom();
  }

  function renderHistory() {
    body.innerHTML = '';
    if (!history.length) {
      addBubble(GREETING, 'bot');
      renderSuggestions();
      return;
    }
    history.forEach(function (m) {
      addBubble(m.content, m.role === 'assistant' ? 'bot' : 'user');
    });
  }

  function open() {
    panel.classList.add('open');
    if (launcher) launcher.setAttribute('aria-expanded', 'true');
    if (navLink) navLink.setAttribute('aria-expanded', 'true');
    if (!body.childNodes.length) renderHistory();
    input.focus();
  }
  function close() {
    panel.classList.remove('open');
    if (launcher) launcher.setAttribute('aria-expanded', 'false');
    if (navLink) navLink.setAttribute('aria-expanded', 'false');
  }
  function toggle() {
    if (panel.classList.contains('open')) { close(); } else { open(); }
  }

  if (launcher) launcher.addEventListener('click', toggle);
  if (closeBtn) closeBtn.addEventListener('click', close);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && panel.classList.contains('open')) close();
  });

  if (navLink) {
    navLink.addEventListener('click', function (e) {
      e.preventDefault();
      open();
    });
  }

  var sending = false;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var q = input.value.trim();
    if (!q || sending) return;
    sending = true;
    input.value = '';
    input.disabled = true;
    form.querySelector('button').disabled = true;

    addBubble(q, 'user');
    history.push({ role: 'user', content: q });
    saveHistory();

    var typing = typingDots();

    function finish() {
      sending = false;
      input.disabled = false;
      form.querySelector('button').disabled = false;
      input.focus();
    }

    fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch' },
      body: JSON.stringify({ message: q, history: history.slice(0, -1) })
    })
      .then(function (r) { return r.text().then(function (t) { return { code: r.status, text: t }; }); })
      .then(function (res) {
        var data = null;
        try { data = JSON.parse(res.text); } catch (e) { data = null; }
        if (typing.parentNode) typing.parentNode.removeChild(typing);

        var isSuccess = res.code >= 200 && res.code < 300 && data && data.ok;

        if (res.code === 429) {
          // Google quota / rate limit — don't drop the user's turn so retry is easy.
          addBubble((data && data.error) || 'The AI assistant hit its limit right now — try again in a few minutes.', 'bot', false);
        } else if (isSuccess) {
          addBubble(data.answer, 'bot');
          history.push({ role: 'assistant', content: data.answer });
        } else {
          history.pop();
          addBubble((data && data.error) || 'Couldn’t get a reply — try again in a moment.', 'bot', false);
        }
        saveHistory();
      })
      .catch(function () {
        if (typing.parentNode) typing.parentNode.removeChild(typing);
        history.pop();
        addBubble('Couldn’t reach the server — check your connection and try again.', 'bot', false);
        saveHistory();
      })
      .then(finish);
  });

  if (newChat) newChat.addEventListener('click', function () {
    history = [];
    saveHistory();
    body.innerHTML = '';
    addBubble(GREETING, 'bot');
    renderSuggestions();
    input.focus();
  });

  loadHistory();
})();