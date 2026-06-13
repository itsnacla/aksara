{{-- Floating AI Chatbot Component (only for authenticated users) --}}
@auth
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
                <button id="chatbot-history-toggle" class="chatbot-header-btn" aria-label="Toggle history" title="Riwayat Obrolan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
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

        {{-- History Panel --}}
        <div id="chatbot-history-panel" class="chatbot-history-panel" style="display: none;">
            <div class="chatbot-history-header">
                <h4>Riwayat Obrolan</h4>
                <button id="chatbot-new-chat-btn" class="chatbot-new-chat-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg> Chat Baru
                </button>
            </div>
            <div class="chatbot-history-warning" style="font-size: 11px; color: var(--cb-text-faint); padding: 8px 16px; background: rgba(0,0,0,0.03); border-bottom: 1px solid var(--cb-border); text-align: center;">
                ⏳ Riwayat percakapan akan dihapus otomatis setelah 7 hari.
            </div>
            <div id="chatbot-history-list" class="chatbot-history-list">
                {{-- Loaded via JS --}}
                <div class="chatbot-history-loading">Memuat riwayat...</div>
            </div>
            
            {{-- Custom Confirm Dialog --}}
            <div id="chatbot-confirm-dialog" class="chatbot-confirm-dialog" style="display: none;">
                <div class="chatbot-confirm-box">
                    <h5>Hapus Percakapan?</h5>
                    <p>Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="chatbot-confirm-actions">
                        <button id="chatbot-confirm-cancel" class="chatbot-btn-cancel">Batal</button>
                        <button id="chatbot-confirm-yes" class="chatbot-btn-danger">Hapus</button>
                    </div>
                </div>
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

@vite(['resources/css/chatbot.css', 'resources/js/chatbot.js'])
@endauth
