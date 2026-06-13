import { marked } from "marked";
import DOMPurify from "dompurify";

document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById("aksara-chatbot");
    if (!wrapper) return;

    const toggle = document.getElementById("chatbot-toggle");
    const window_ = document.getElementById("chatbot-window");
    const minimize = document.getElementById("chatbot-minimize");
    const maximizeBtn = document.getElementById("chatbot-maximize");
    const form = document.getElementById("chatbot-form");
    const input = document.getElementById("chatbot-input");
    const messages = document.getElementById("chatbot-messages");
    const fabIcon = document.getElementById("chatbot-fab-icon");
    const fabClose = document.getElementById("chatbot-fab-close");
    const fabPulse = document.querySelector(".chatbot-fab-pulse");
    const statusEl = document.querySelector(".chatbot-status");
    const backdrop = document.getElementById("chatbot-backdrop");

    const historyToggle = document.getElementById("chatbot-history-toggle");
    const historyPanel = document.getElementById("chatbot-history-panel");
    const historyList = document.getElementById("chatbot-history-list");
    const newChatBtn = document.getElementById("chatbot-new-chat-btn");

    let isOpen = false;
    let isMaximized = false;
    let isHistoryOpen = false;
    let configLoaded = false;
    let conversationHistory = [];
    let currentConversationId = null;

    const botAvatarSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1.27A7 7 0 0 1 13 23h-2a7 7 0 0 1-6.73-4H3a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/><circle cx="9.5" cy="15.5" r="1"/><circle cx="14.5" cy="15.5" r="1"/></svg>`;
    const userAvatarSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`;

    const expandIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>`;
    const shrinkIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>`;

    async function loadConfig() {
        if (configLoaded) return;
        try {
            const res = await fetch("/chatbot/config", {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw new Error();
            const cfg = await res.json();
            if (statusEl)
                statusEl.innerHTML = `<span class="chatbot-status-dot"></span> ${cfg.roleName}`;
            addMessage(cfg.greeting);
            if (cfg.chips && cfg.chips.length) {
                const chipsDiv = document.createElement("div");
                chipsDiv.className = "chatbot-chips";
                cfg.chips.forEach((c) => {
                    const btn = document.createElement("button");
                    btn.className = "chatbot-chip";
                    btn.setAttribute("data-message", c.message);
                    btn.textContent = c.label;
                    btn.addEventListener("click", handleChipClick);
                    chipsDiv.appendChild(btn);
                });
                messages.appendChild(chipsDiv);
            }
        } catch {
            addMessage(
                "Halo! 👋 Saya **Aksara AI**. Ada yang bisa saya bantu?",
            );
        }
        configLoaded = true;
    }

    function setupEcho(conversationId) {
        if (
            !window.Echo ||
            !conversationId ||
            currentConversationId === conversationId
        )
            return;

        // Leave old channel if exists
        if (currentConversationId) {
            window.Echo.leave(`conversations.${currentConversationId}`);
        }

        currentConversationId = conversationId;

        window.Echo.private(`conversations.${conversationId}`).listen(
            "MessageSent",
            (e) => {
                console.log("Real-time message received:", e);
                // Prevent duplicate if we already added it from API response
                // But for streaming, this is where we update the bubble
                removeTyping();
                addMessage(e.content, e.role === "user");
            },
        );
    }

    function handleChipClick() {
        input.value = this.getAttribute("data-message");
        form.dispatchEvent(new Event("submit"));
        const c = this.closest(".chatbot-chips");
        if (c) {
            c.style.opacity = "0";
            c.style.transition = "all 0.3s";
            setTimeout(() => c.remove(), 300);
        }
    }

    function openChat() {
        isOpen = true;
        window_.style.display = "flex";
        fabIcon.style.display = "none";
        fabClose.style.display = "flex";
        if (fabPulse) fabPulse.style.display = "none";
        loadConfig();
        setTimeout(() => input.focus(), 100);
    }

    function closeChat() {
        isOpen = false;
        if (isMaximized) toggleMaximize();
        if (isHistoryOpen) toggleHistory();
        window_.style.display = "none";
        fabIcon.style.display = "flex";
        fabClose.style.display = "none";
    }

    function toggleMaximize() {
        isMaximized = !isMaximized;
        if (isMaximized) {
            wrapper.classList.add("chatbot-fullscreen");
            backdrop.classList.add("active");
            maximizeBtn.innerHTML = shrinkIcon;
            maximizeBtn.title = "Perkecil";
        } else {
            wrapper.classList.remove("chatbot-fullscreen");
            backdrop.classList.remove("active");
            maximizeBtn.innerHTML = expandIcon;
            maximizeBtn.title = "Perbesar";
        }
        messages.scrollTop = messages.scrollHeight;
        setTimeout(() => input.focus(), 100);
    }

    async function loadHistory() {
        if (!historyList) return;
        try {
            const res = await fetch("/chatbot/history", {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw new Error();
            const historyData = await res.json();
            
            historyList.innerHTML = "";
            if (historyData.length === 0) {
                historyList.innerHTML = '<div class="chatbot-history-empty">Belum ada riwayat percakapan.</div>';
                return;
            }

            historyData.forEach(conv => {
                const item = document.createElement("div");
                item.className = "chatbot-history-item";
                item.innerHTML = `<h5>${conv.title}</h5><span>${new Date(conv.updated_at).toLocaleString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'})}</span>`;
                item.addEventListener("click", () => fetchConversation(conv.id));
                historyList.appendChild(item);
            });
        } catch {
            historyList.innerHTML = '<div class="chatbot-history-empty">Gagal memuat riwayat.</div>';
        }
    }

    async function fetchConversation(id) {
        try {
            const res = await fetch(`/chatbot/conversation/${id}`, {
                headers: { Accept: "application/json" },
            });
            if (!res.ok) throw new Error();
            const data = await res.json();
            
            // Clear current chat
            messages.innerHTML = "";
            currentConversationId = data.id;
            setupEcho(currentConversationId);
            
            // Add messages
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    addMessage(msg.content, msg.role === 'user');
                });
            } else {
                addMessage(`Percakapan kosong.`);
            }
            toggleHistory(); // close history panel
        } catch {
            alert('Gagal memuat percakapan.');
        }
    }

    function toggleHistory() {
        isHistoryOpen = !isHistoryOpen;
        if (isHistoryOpen) {
            historyPanel.style.display = "flex";
            loadHistory();
        } else {
            historyPanel.style.display = "none";
        }
    }

    function startNewChat() {
        currentConversationId = null;
        messages.innerHTML = "";
        if (window.Echo && currentConversationId) {
            window.Echo.leave(`conversations.${currentConversationId}`);
        }
        configLoaded = false;
        loadConfig(); // load greeting again
        if (isHistoryOpen) toggleHistory();
    }

    if (historyToggle) historyToggle.addEventListener("click", toggleHistory);
    if (newChatBtn) newChatBtn.addEventListener("click", startNewChat);

    if (toggle)
        toggle.addEventListener("click", () =>
            isOpen ? closeChat() : openChat(),
        );
    if (minimize) minimize.addEventListener("click", closeChat);
    if (maximizeBtn) maximizeBtn.addEventListener("click", toggleMaximize);
    if (backdrop)
        backdrop.addEventListener("click", () => {
            if (isMaximized) toggleMaximize();
        });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            if (isMaximized) toggleMaximize();
            else if (isOpen) closeChat();
        }
    });

    function addMessage(text, isUser = false) {
        const d = document.createElement("div");
        d.className = `chatbot-msg ${isUser ? "chatbot-msg-user" : "chatbot-msg-bot"}`;

        let fmt;
        if (!isUser) {
            // Render Markdown and Sanitize
            fmt = DOMPurify.sanitize(marked.parse(text));
        } else {
            fmt = DOMPurify.sanitize(text);
        }

        d.innerHTML = `<div class="chatbot-msg-avatar">${isUser ? userAvatarSvg : botAvatarSvg}</div><div class="chatbot-msg-bubble">${fmt}</div>`;
        messages.appendChild(d);
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
        const d = document.createElement("div");
        d.className = "chatbot-msg chatbot-msg-bot";
        d.id = "chatbot-typing";
        d.innerHTML = `<div class="chatbot-msg-avatar">${botAvatarSvg}</div><div class="chatbot-msg-bubble chatbot-typing"><span class="chatbot-typing-dot"></span><span class="chatbot-typing-dot"></span><span class="chatbot-typing-dot"></span></div>`;
        messages.appendChild(d);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        const t = document.getElementById("chatbot-typing");
        if (t) t.remove();
    }

    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) return;
            addMessage(message, true);
            input.value = "";
            input.disabled = true;
            document.getElementById("chatbot-send").disabled = true;
            showTyping();
            try {
                const csrfToken =
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") ||
                    document.querySelector('input[name="_token"]')?.value;
                const response = await fetch("/chatbot/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ 
                        message, 
                        conversation_id: currentConversationId 
                    }),
                });
                removeTyping();
                if (response.ok) {
                    const data = await response.json();
                    if (data.conversation_id) {
                        setupEcho(data.conversation_id);
                    }
                    // Only add message if it's not already handled by Echo or if Echo is not active
                    if (!window.Echo || !data.conversation_id) {
                        addMessage(data.reply);
                    }
                } else {
                    addMessage(
                        "Maaf, terjadi kesalahan. Silakan coba lagi. 🙏",
                    );
                }
            } catch {
                removeTyping();
                addMessage(
                    "Maaf, koneksi terputus. Periksa koneksi internet Anda. 🔌",
                );
            }
            input.disabled = false;
            document.getElementById("chatbot-send").disabled = false;
            input.focus();
        });
    }
});
