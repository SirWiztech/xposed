(function () {
  'use strict';

  const launcher = document.getElementById('chatLauncher');
  const panel = document.getElementById('chatPanel');
  if (!launcher || !panel) return;

  const body = document.getElementById('chatBody');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatInput');
  const contactView = document.getElementById('contactView');
  const contactForm = document.getElementById('contactForm');
  const contactError = document.getElementById('contactError');
  const contactSuccess = document.getElementById('contactSuccess');
  const contactAgain = document.getElementById('contactAgain');
  const tabs = Array.prototype.slice.call(document.querySelectorAll('.chat-tab'));

  const CHAT_URL = panel.dataset.endpoint || '/xposed/chat.php';
  const CONTACT_URL = panel.dataset.contactEndpoint || '/xposed/contact.php';

  const SUGGESTIONS = [
    'When do you stream?',
    'How do I contact you for business?',
    'Is he live right now?',
    'Where do I buy merch?',
  ];

  function addBubble(text, who) {
    const div = document.createElement('div');
    div.className = 'bubble ' + who;
    div.innerHTML = text;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
    return div;
  }

  function renderSuggestions() {
    const row = document.createElement('div');
    row.className = 'chip-row';
    SUGGESTIONS.forEach(function (s) {
      const c = document.createElement('button');
      c.className = 'chip';
      c.textContent = s;
      c.type = 'button';
      c.addEventListener('click', function () {
        ask(s);
      });
      row.appendChild(c);
    });
    body.appendChild(row);
  }

  function ask(q) {
    input.value = q;
    form.dispatchEvent(new Event('submit'));
  }

  /* ---------- FAQ / Contact tab switching ---------- */
  function setTab(name) {
    tabs.forEach(function (t) {
      t.classList.toggle('is-active', t.dataset.tab === name);
    });
    const faqMode = name === 'faq';
    form.hidden = !faqMode;
    contactView.hidden = faqMode;
    if (faqMode) {
      resetContact();
      input.focus();
    }
  }

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      setTab(tab.dataset.tab);
    });
  });

  launcher.addEventListener('click', function () {
    panel.classList.toggle('open');
    if (panel.classList.contains('open') && !panel.dataset.started) {
      panel.dataset.started = '1';
      addBubble('Yo, welcome to Xposed HQ. Ask me about the stream, schedule, merch or anything else.', 'bot');
      renderSuggestions();
    }
  });
  document.getElementById('chatClose').addEventListener('click', function () {
    panel.classList.remove('open');
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const q = input.value.trim();
    if (!q) return;
    input.value = '';
    addBubble(escapeHTML(q), 'user');

    const typing = addBubble('Xposed is typing…', 'bot');
    typing.id = 'typingEl';

    fetch(CHAT_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch' },
      body: JSON.stringify({ q: q })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        const t = document.getElementById('typingEl');
        if (t) t.remove();
        if (data.ok) {
          addBubble(data.answer, 'bot');
        } else {
          addBubble(data.error || 'Something went wrong — email businessxposed@gmail.com instead.', 'bot');
        }
      })
      .catch(function () {
        const t = document.getElementById('typingEl');
        if (t) t.remove();
        addBubble('Could not reach the server. Email businessxposed@gmail.com and we’ll sort you out.', 'bot');
      });
  });

  /* ---------- Contact form ---------- */
  function showContactMsg(el, text) {
    el.textContent = text;
    el.hidden = !text;
  }

  function resetContact() {
    contactForm.reset();
    showContactMsg(contactError, '');
    showContactMsg(contactSuccess, '');
    contactForm.hidden = false;
    contactSuccess.hidden = true;
  }

  contactAgain.addEventListener('click', resetContact);

  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const payload = {
      name: document.getElementById('contactName').value.trim(),
      email: document.getElementById('contactEmail').value.trim(),
      message: document.getElementById('contactMessage').value.trim()
    };

    if (!payload.name || !payload.email || !payload.message) {
      showContactMsg(contactError, 'Fill in your name, email and a message.');
      return;
    }

    showContactMsg(contactError, '');
    const submitBtn = contactForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending…';

    fetch(CONTACT_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch' },
      body: JSON.stringify(payload)
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send message';
        if (data.ok) {
          contactForm.hidden = true;
          showContactMsg(contactSuccess, data.note || 'Message received — we’ll get back to you.');
        } else {
          showContactMsg(contactError, data.error || 'Could not send — email businessxposed@gmail.com instead.');
        }
      })
      .catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send message';
        showContactMsg(contactError, 'Could not reach the server. Email businessxposed@gmail.com instead.');
      });
  });

  function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }
})();