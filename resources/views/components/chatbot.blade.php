{{-- Floating AI Chatbot Component --}}
<div id="chatbot-backdrop" class="chatbot-backdrop"></div>
<div id="aksara-chatbot" class="chatbot-wrapper">
    {{-- Floating Toggle Button --}}
    <button id="chatbot-toggle" class="chatbot-fab" aria-label="Open AI Assistant">
        <span class="chatbot-fab-icon" id="chatbot-fab-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1.27A7 7 0 0 1 13 23h-2a7 7 0 0 1-6.73-4H3a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/>
                <circle cx="9.5" cy="15.5" r="1"/>
                <circle cx="14.5" cy="15.5" r="1"/>
            </svg>
        </span>
        <span class="chatbot-fab-close" id="chatbot-fab-close" style="display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </span>
        {{-- Pulse ring --}}
        <span class="chatbot-fab-pulse"></span>
    </button>

    {{-- Chat Window --}}
    <div id="chatbot-window" class="chatbot-window" style="display: none;">
        {{-- Header --}}
        <div class="chatbot-header">
            <div class="chatbot-header-left">
                <div class="chatbot-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1.27A7 7 0 0 1 13 23h-2a7 7 0 0 1-6.73-4H3a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/>
                        <circle cx="9.5" cy="15.5" r="1"/>
                        <circle cx="14.5" cy="15.5" r="1"/>
                    </svg>
                </div>
                <div>
                    <h3 class="chatbot-title">Aksara AI</h3>
                    <span class="chatbot-status">
                        <span class="chatbot-status-dot"></span>
                        Online
                    </span>
                </div>
            </div>
            <div class="chatbot-header-actions">
                <button id="chatbot-maximize" class="chatbot-header-btn" aria-label="Maximize chat" title="Perbesar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <polyline points="9 21 3 21 3 15"></polyline>
                        <line x1="21" y1="3" x2="14" y2="10"></line>
                        <line x1="3" y1="21" x2="10" y2="14"></line>
                    </svg>
                </button>
                <button id="chatbot-minimize" class="chatbot-header-btn" aria-label="Minimize chat" title="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages Area (populated dynamically based on user role) --}}
        <div id="chatbot-messages" class="chatbot-messages">
            {{-- Greeting & chips loaded via /chatbot/config --}}
        </div>

        {{-- Input Area --}}
        <div class="chatbot-input-area">
            <form id="chatbot-form" class="chatbot-form" autocomplete="off">
                @csrf
                <input
                    type="text"
                    id="chatbot-input"
                    class="chatbot-input"
                    placeholder="Ketik pesan Anda..."
                    maxlength="1000"
                    required
                />
                <button type="submit" id="chatbot-send" class="chatbot-send" aria-label="Send message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
            <div class="chatbot-powered">Powered by Aksara AI</div>
        </div>
    </div>
</div>

<style>
/* ========================================
   Aksara AI Chatbot - Premium Floating Widget
   ======================================== */

.chatbot-wrapper {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
}

/* --- Floating Action Button --- */
.chatbot-fab {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: linear-gradient(135deg, #005da7 0%, #2976c7 50%, #4a90d9 100%);
    color: white;
    box-shadow:
        0 4px 20px rgba(0, 93, 167, 0.35),
        0 8px 40px rgba(0, 93, 167, 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10;
}

.chatbot-fab:hover {
    transform: scale(1.08);
    box-shadow:
        0 6px 28px rgba(0, 93, 167, 0.45),
        0 12px 48px rgba(0, 93, 167, 0.2);
}

.chatbot-fab:active {
    transform: scale(0.95);
}

.chatbot-fab-icon,
.chatbot-fab-close {
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.chatbot-fab-pulse {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid rgba(0, 93, 167, 0.4);
    animation: chatbot-pulse 2s ease-in-out infinite;
    pointer-events: none;
}

@keyframes chatbot-pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: 0; }
}

/* --- Chat Window --- */
.chatbot-window {
    position: absolute;
    bottom: 76px;
    right: 0;
    width: 380px;
    max-height: 560px;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow:
        0 24px 80px rgba(0, 0, 0, 0.12),
        0 8px 32px rgba(0, 0, 0, 0.06),
        0 0 0 1px rgba(0, 93, 167, 0.05);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: chatbot-slide-up 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes chatbot-slide-up {
    from {
        opacity: 0;
        transform: translateY(16px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* --- Header --- */
.chatbot-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: linear-gradient(135deg, #005da7 0%, #2976c7 100%);
    color: white;
    flex-shrink: 0;
}

.chatbot-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
}

.chatbot-title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: -0.01em;
    margin: 0;
    line-height: 1;
}

.chatbot-status {
    font-size: 11px;
    opacity: 0.85;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 3px;
}

.chatbot-status-dot {
    width: 6px;
    height: 6px;
    background: #4ade80;
    border-radius: 50%;
    display: inline-block;
    animation: chatbot-status-blink 2s ease-in-out infinite;
}

@keyframes chatbot-status-blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.chatbot-header-actions {
    display: flex;
    gap: 6px;
}

.chatbot-header-btn {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.chatbot-header-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.08);
}

/* --- Fullscreen Mode --- */
.chatbot-fullscreen {
    bottom: 0;
    right: 0;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
}

.chatbot-fullscreen .chatbot-fab {
    display: none;
}

.chatbot-fullscreen .chatbot-window {
    position: fixed;
    inset: 0;
    width: 100% !important;
    max-height: 100% !important;
    height: 100%;
    border-radius: 0;
    border: none;
    animation: chatbot-expand 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.chatbot-fullscreen .chatbot-messages {
    max-height: none;
    min-height: 0;
    padding: 28px 24px;
}

.chatbot-fullscreen .chatbot-msg {
    max-width: 680px;
}

.chatbot-fullscreen .chatbot-msg-bot {
    max-width: 680px;
}

.chatbot-fullscreen .chatbot-msg-user {
    max-width: 680px;
    align-self: flex-end;
}

.chatbot-fullscreen .chatbot-msg-bubble {
    font-size: 15px;
}

.chatbot-fullscreen .chatbot-input-area {
    padding: 16px 24px 14px;
}

.chatbot-fullscreen .chatbot-form {
    max-width: 720px;
    margin: 0 auto;
    padding: 6px 6px 6px 18px;
    border-radius: 20px;
}

.chatbot-fullscreen .chatbot-input {
    font-size: 15px;
    padding: 10px 0;
}

.chatbot-fullscreen .chatbot-send {
    width: 42px;
    height: 42px;
    min-width: 42px;
    border-radius: 14px;
}

.chatbot-fullscreen .chatbot-header {
    padding: 18px 24px;
}

.chatbot-fullscreen .chatbot-chips {
    max-width: 680px;
}

@keyframes chatbot-expand {
    from {
        opacity: 0.8;
        border-radius: 24px;
        inset: 60px;
    }
    to {
        opacity: 1;
        border-radius: 0;
        inset: 0;
    }
}

/* Backdrop overlay for fullscreen */
.chatbot-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9998;
    animation: chatbot-fade-in 0.3s ease;
}

.chatbot-backdrop.active {
    display: block;
}

@keyframes chatbot-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* --- Messages Area --- */
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-height: 280px;
    max-height: 360px;
    scroll-behavior: smooth;
}

.chatbot-messages::-webkit-scrollbar {
    width: 4px;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: rgba(0, 93, 167, 0.15);
    border-radius: 4px;
}

/* --- Message Bubbles --- */
.chatbot-msg {
    display: flex;
    gap: 8px;
    animation: chatbot-msg-in 0.3s ease;
}

@keyframes chatbot-msg-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.chatbot-msg-bot {
    align-self: flex-start;
    max-width: 88%;
}

.chatbot-msg-user {
    align-self: flex-end;
    flex-direction: row-reverse;
    max-width: 88%;
}

.chatbot-msg-avatar {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 10px;
    background: linear-gradient(135deg, #005da7, #2976c7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    margin-top: 2px;
}

.chatbot-msg-user .chatbot-msg-avatar {
    background: linear-gradient(135deg, #64748b, #94a3b8);
}

.chatbot-msg-bubble {
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.55;
    color: #1e293b;
}

.chatbot-msg-bot .chatbot-msg-bubble {
    background: #f1f5f9;
    border-bottom-left-radius: 6px;
}

.chatbot-msg-user .chatbot-msg-bubble {
    background: linear-gradient(135deg, #005da7, #2976c7);
    color: white;
    border-bottom-right-radius: 6px;
}

/* --- Typing Indicator --- */
.chatbot-typing {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
    align-items: center;
}

.chatbot-typing-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #94a3b8;
    animation: chatbot-typing-bounce 1.4s ease-in-out infinite;
}

.chatbot-typing-dot:nth-child(2) { animation-delay: 0.16s; }
.chatbot-typing-dot:nth-child(3) { animation-delay: 0.32s; }

@keyframes chatbot-typing-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-6px); opacity: 1; }
}

/* --- Quick Action Chips --- */
.chatbot-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 0 4px;
}

.chatbot-chip {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    color: #475569;
    font-family: inherit;
}

.chatbot-chip:hover {
    background: #005da7;
    color: white;
    border-color: #005da7;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 93, 167, 0.25);
}

/* --- Input Area --- */
.chatbot-input-area {
    padding: 12px 16px 10px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.6);
}

.chatbot-form {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    border-radius: 16px;
    padding: 4px 4px 4px 14px;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.chatbot-form:focus-within {
    border-color: rgba(0, 93, 167, 0.3);
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 93, 167, 0.06);
}

.chatbot-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 13.5px;
    color: #1e293b;
    font-family: inherit;
    padding: 8px 0;
}

.chatbot-input::placeholder {
    color: #94a3b8;
}

.chatbot-send {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #005da7, #2976c7);
    color: white;
    transition: all 0.2s;
}

.chatbot-send:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 12px rgba(0, 93, 167, 0.35);
}

.chatbot-send:active {
    transform: scale(0.95);
}

.chatbot-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.chatbot-powered {
    text-align: center;
    font-size: 10px;
    color: #cbd5e1;
    margin-top: 6px;
    letter-spacing: 0.03em;
}

/* --- Responsive --- */
@media (max-width: 480px) {
    .chatbot-window {
        width: calc(100vw - 32px);
        right: -8px;
        bottom: 72px;
        max-height: 70vh;
        border-radius: 20px;
    }

    .chatbot-wrapper {
        bottom: 16px;
        right: 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('aksara-chatbot');
    const toggle = document.getElementById('chatbot-toggle');
    const window_ = document.getElementById('chatbot-window');
    const minimize = document.getElementById('chatbot-minimize');
    const maximizeBtn = document.getElementById('chatbot-maximize');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');
    const fabIcon = document.getElementById('chatbot-fab-icon');
    const fabClose = document.getElementById('chatbot-fab-close');
    const fabPulse = document.querySelector('.chatbot-fab-pulse');
    const statusEl = document.querySelector('.chatbot-status');
    const backdrop = document.getElementById('chatbot-backdrop');

    let isOpen = false;
    let isMaximized = false;
    let configLoaded = false;
    let conversationHistory = [];

    const botAvatarSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1.27A7 7 0 0 1 13 23h-2a7 7 0 0 1-6.73-4H3a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/><circle cx="9.5" cy="15.5" r="1"/><circle cx="14.5" cy="15.5" r="1"/></svg>`;
    const userAvatarSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`;

    // Maximize / Restore icon SVGs
    const expandIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>`;
    const shrinkIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>`;

    async function loadConfig() {
        if (configLoaded) return;
        try {
            const res = await fetch('/chatbot/config', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            const cfg = await res.json();
            if (statusEl) statusEl.innerHTML = `<span class="chatbot-status-dot"></span> ${cfg.roleName}`;
            addMessage(cfg.greeting);
            if (cfg.chips && cfg.chips.length) {
                const chipsDiv = document.createElement('div');
                chipsDiv.className = 'chatbot-chips';
                cfg.chips.forEach(c => {
                    const btn = document.createElement('button');
                    btn.className = 'chatbot-chip';
                    btn.setAttribute('data-message', c.message);
                    btn.textContent = c.label;
                    btn.addEventListener('click', handleChipClick);
                    chipsDiv.appendChild(btn);
                });
                messages.appendChild(chipsDiv);
            }
        } catch {
            addMessage('Halo! 👋 Saya **Aksara AI**. Ada yang bisa saya bantu?');
        }
        configLoaded = true;
    }

    function handleChipClick() {
        input.value = this.getAttribute('data-message');
        form.dispatchEvent(new Event('submit'));
        const c = this.closest('.chatbot-chips');
        if (c) { c.style.opacity = '0'; c.style.transition = 'all 0.3s'; setTimeout(() => c.remove(), 300); }
    }

    // --- Open / Close ---
    function openChat() {
        isOpen = true;
        window_.style.display = 'flex';
        fabIcon.style.display = 'none';
        fabClose.style.display = 'flex';
        if (fabPulse) fabPulse.style.display = 'none';
        loadConfig();
        setTimeout(() => input.focus(), 100);
    }

    function closeChat() {
        isOpen = false;
        if (isMaximized) toggleMaximize(); // restore first
        window_.style.display = 'none';
        fabIcon.style.display = 'flex';
        fabClose.style.display = 'none';
    }

    // --- Maximize / Restore ---
    function toggleMaximize() {
        isMaximized = !isMaximized;
        if (isMaximized) {
            wrapper.classList.add('chatbot-fullscreen');
            backdrop.classList.add('active');
            maximizeBtn.innerHTML = shrinkIcon;
            maximizeBtn.title = 'Perkecil';
        } else {
            wrapper.classList.remove('chatbot-fullscreen');
            backdrop.classList.remove('active');
            maximizeBtn.innerHTML = expandIcon;
            maximizeBtn.title = 'Perbesar';
        }
        messages.scrollTop = messages.scrollHeight;
        setTimeout(() => input.focus(), 100);
    }

    toggle.addEventListener('click', () => isOpen ? closeChat() : openChat());
    minimize.addEventListener('click', closeChat);
    maximizeBtn.addEventListener('click', toggleMaximize);
    backdrop.addEventListener('click', () => { if (isMaximized) toggleMaximize(); });

    // ESC key to restore/close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (isMaximized) toggleMaximize();
            else if (isOpen) closeChat();
        }
    });

    function addMessage(text, isUser = false) {
        const d = document.createElement('div');
        d.className = `chatbot-msg ${isUser ? 'chatbot-msg-user' : 'chatbot-msg-bot'}`;
        let fmt = text;
        if (!isUser) fmt = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\*(.*?)\*/g, '<em>$1</em>').replace(/\n/g, '<br>');
        d.innerHTML = `<div class="chatbot-msg-avatar">${isUser ? userAvatarSvg : botAvatarSvg}</div><div class="chatbot-msg-bubble">${fmt}</div>`;
        messages.appendChild(d);
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
        const d = document.createElement('div');
        d.className = 'chatbot-msg chatbot-msg-bot'; d.id = 'chatbot-typing';
        d.innerHTML = `<div class="chatbot-msg-avatar">${botAvatarSvg}</div><div class="chatbot-msg-bubble chatbot-typing"><span class="chatbot-typing-dot"></span><span class="chatbot-typing-dot"></span><span class="chatbot-typing-dot"></span></div>`;
        messages.appendChild(d); messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() { const t = document.getElementById('chatbot-typing'); if (t) t.remove(); }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        addMessage(message, true);
        input.value = '';
        input.disabled = true;
        document.getElementById('chatbot-send').disabled = true;
        showTyping();
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value;
            const response = await fetch('/chatbot/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ message, history: conversationHistory.slice(-10) }),
            });
            removeTyping();
            if (response.ok) {
                const data = await response.json();
                addMessage(data.reply);
                conversationHistory.push({ role: 'user', text: message });
                conversationHistory.push({ role: 'model', text: data.reply });
            } else {
                addMessage('Maaf, terjadi kesalahan. Silakan coba lagi. 🙏');
            }
        } catch {
            removeTyping();
            addMessage('Maaf, koneksi terputus. Periksa koneksi internet Anda. 🔌');
        }
        input.disabled = false;
        document.getElementById('chatbot-send').disabled = false;
        input.focus();
    });
});
</script>
