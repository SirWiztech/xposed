<?php
/**
 * XPOSED — Standalone AI chat widget (Google Gemini via ai-chat.php).
 * Opened from the floating launcher (bottom-right, above FAQ launcher) and the navbar AI link.
 * ai-chat.js wires it up. Uses shared chat styles (.chat-body, .chat-form, .chat-close, .bubble).
 */
?>
<button class="ai-launcher" id="aiLauncher" aria-label="Open AI assistant" aria-expanded="false">
  <svg viewBox="0 0 24 24" fill="none" stroke="#E10600" stroke-width="1.8" aria-hidden="true">
    <path d="M7 9A2 2 0 0 1 9 7h6a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V9z"/>
    <path d="M12 7V3.5" stroke-linecap="round"/>
    <circle cx="12" cy="2.8" r="1.1"/>
    <path d="M20 9v6" stroke-linecap="round"/>
    <path d="M19 6.5c0 1-.8 1.8-1.7 1.8S15.6 7.5 15.6 6.5" stroke-linecap="round"/>
    <path d="M9 13h6" stroke-linecap="round"/>
    <circle cx="9.6" cy="10.8" r=".4" fill="#E10600" stroke="none"/>
    <circle cx="14.4" cy="10.8" r=".4" fill="#E10600" stroke="none"/>
  </svg>
  <span>AI</span>
</button>

<div class="ai-panel" id="aiPanel" data-endpoint="<?= e(url('ai-chat.php')) ?>">
  <div class="ai-head">
    <span class="ai-avatar" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="#E10600" stroke-width="1.8">
        <path d="M12 3l1.9 5.3 5.3 1.9-5.3 1.9L12 17.4l-1.9-5.3L4.8 10.2l5.3-1.9z" stroke-linejoin="round"/>
      </svg>
    </span>
    <div class="ai-meta">
      <strong>Xposed AI</strong>
      <span class="ai-status"><i class="dot" aria-hidden="true"></i>Ready</span>
    </div>
    <button class="ai-newchat" id="aiNewChat" type="button">New chat</button>
    <button class="chat-close" id="aiClose" aria-label="Close AI chat" type="button">×</button>
  </div>

  <div class="chat-body" id="aiBody"></div>

  <form class="chat-form" id="aiForm">
    <input type="text" id="aiInput" placeholder="Ask about the stream, merch, schedule…" autocomplete="off" maxlength="4000">
    <button type="submit">Send</button>
  </form>
</div>