<?php
/**
 * FAQ chat + contact form launcher/panel. chat.js wires the AJAX to /chat.php and /contact.php.
 */
$faqSuggestions = [
    'When do you stream?',
    'Is he live right now?',
    'How do I contact you for business?',
    'Where do I buy merch?',
];
?>
<button class="chat-launcher" id="chatLauncher" aria-label="Open FAQ chat">
  <svg viewBox="0 0 24 24" fill="none" stroke="#E10600" stroke-width="1.8">
    <path d="M4 4h16v12H8l-4 4V4z" stroke-linejoin="round" stroke-linecap="round"/>
  </svg>
</button>

<div class="chat-panel" id="chatPanel"
     data-endpoint="chat.php"
     data-contact-endpoint="contact.php">
  <div class="chat-head">
    <span class="logo">X<span>POSED</span></span>
    <nav class="chat-tabs">
      <button type="button" class="chat-tab is-active" data-tab="faq">FAQ</button>
      <button type="button" class="chat-tab" data-tab="contact">Contact</button>
    </nav>
    <button class="chat-close" id="chatClose" aria-label="Close chat">×</button>
  </div>

  <div class="chat-body" id="chatBody"></div>

  <form class="chat-form" id="chatForm" data-view="faq">
    <input type="text" id="chatInput" placeholder="Ask about the stream, schedule, merch…" autocomplete="off">
    <button type="submit">Send</button>
  </form>

  <div class="contact-view" id="contactView" data-view="contact" hidden>
    <form id="contactForm">
      <input type="text" id="contactName" name="name" placeholder="Your name" autocomplete="name" maxlength="120" required>
      <input type="email" id="contactEmail" name="email" placeholder="you@email.com" autocomplete="email" maxlength="191" required>
      <textarea id="contactMessage" name="message" placeholder="Sponsorships, collabs, questions…" maxlength="2000" rows="4" required></textarea>
      <button type="submit">Send message</button>
      <p class="contact-note">Business enquiries also go to <strong>businessxposed@gmail.com</strong> — this form lands in the same inbox.</p>
    </form>
    <div class="contact-msg is-error" id="contactError" hidden></div>
    <div class="contact-msg is-ok" id="contactSuccess" hidden>
      <p>Message received — Cody’s team will get back to you.</p>
      <button type="button" class="contact-again" id="contactAgain">Send another</button>
    </div>
  </div>
</div>
