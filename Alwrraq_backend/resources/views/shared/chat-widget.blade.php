@auth
    @php
        $chatIsAdmin = auth()->user()->role === 'admin';
    @endphp

    <style>
        .support-chat-launcher,
        .support-chat-panel button { margin: 0; }
        .support-chat-launcher { position: fixed; left: 18px; bottom: 18px; z-index: 90; width: auto; display: inline-flex; align-items: center; gap: 9px; min-height: 46px; padding: 12px 16px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #0f4c81, #10233f); color: #ffffff; box-shadow: 0 18px 44px rgba(15, 23, 42, 0.24); cursor: pointer; font-family: inherit; font-weight: 900; }
        .support-chat-launcher:hover { transform: translateY(-1px); }
        .support-chat-launcher .chat-count { display: none; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #dc2626; color: #ffffff; font-size: 12px; line-height: 20px; text-align: center; }
        .support-chat-launcher.has-unread .chat-count { display: inline-block; }
        .support-chat-panel { position: fixed; left: 18px; bottom: 78px; z-index: 91; width: min(420px, calc(100vw - 28px)); height: min(620px, calc(100vh - 104px)); display: none; flex-direction: column; overflow: hidden; border: 1px solid #dbe3ef; border-radius: 18px; background: #ffffff; box-shadow: 0 28px 90px rgba(15, 23, 42, 0.28); direction: rtl; }
        .support-chat-panel.active { display: flex; }
        .support-chat-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; background: #0f172a; color: #ffffff; }
        .support-chat-title { margin: 0; font-size: 15px; font-weight: 900; }
        .support-chat-subtitle { margin: 2px 0 0; color: #cbd5e1; font-size: 12px; }
        .support-chat-close { width: auto; border: 1px solid rgba(255,255,255,0.18); background: rgba(255,255,255,0.08); color: #ffffff; border-radius: 9px; padding: 6px 10px; cursor: pointer; font-family: inherit; font-weight: 900; }
        .support-chat-layout { min-height: 0; flex: 1; display: grid; grid-template-columns: {{ $chatIsAdmin ? '150px minmax(0, 1fr)' : '1fr' }}; overscroll-behavior: contain; }
        .support-chat-threads { display: {{ $chatIsAdmin ? 'block' : 'none' }}; overflow-y: auto; border-left: 1px solid #e5e7eb; background: #f8fafc; }
        .support-chat-thread { width: 100%; display: block; padding: 11px 10px; border: 0; border-bottom: 1px solid #e5e7eb; background: transparent; text-align: right; cursor: pointer; font-family: inherit; }
        .support-chat-thread.active { background: #e0f2fe; }
        .support-chat-thread strong { display: block; color: #0f172a; font-size: 12px; line-height: 1.5; }
        .support-chat-thread span { display: block; margin-top: 2px; color: #64748b; font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .support-chat-thread .thread-unread { display: inline-flex; margin-top: 5px; min-width: 18px; height: 18px; padding: 0 6px; align-items: center; justify-content: center; border-radius: 999px; background: #dc2626; color: #ffffff; font-size: 11px; font-weight: 900; }
        .support-chat-main { min-width: 0; min-height: 0; display: flex; flex-direction: column; }
        .support-chat-messages { min-height: 0; flex: 1; overflow-y: auto; padding: 14px; background: #f8fafc; overscroll-behavior: contain; }
        .support-chat-empty { height: 100%; display: grid; place-items: center; color: #64748b; text-align: center; font-size: 13px; font-weight: 800; padding: 18px; }
        .support-message { display: flex; margin-bottom: 10px; }
        .support-message.mine { justify-content: flex-start; }
        .support-message.other { justify-content: flex-end; }
        .support-message-bubble { max-width: 82%; padding: 9px 11px; border-radius: 13px; background: #ffffff; border: 1px solid #e2e8f0; color: #0f172a; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06); }
        .support-message.mine .support-message-bubble { background: #0f4c81; border-color: #0f4c81; color: #ffffff; }
        .support-message-name { display: block; margin-bottom: 4px; font-size: 10px; color: inherit; opacity: 0.76; font-weight: 900; }
        .support-message-text { white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.7; font-size: 13px; }
        .support-message-time { display: block; margin-top: 5px; font-size: 10px; opacity: 0.66; }
        .support-chat-form { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e5e7eb; background: #ffffff; }
        .support-chat-input { flex: 1; min-width: 0; resize: none; min-height: 42px; max-height: 96px; padding: 10px 11px; border: 1px solid #cbd5e1; border-radius: 11px; font-family: inherit; font-size: 14px; }
        .support-chat-send { flex: 0 0 auto; width: auto; padding: 10px 14px; border: 0; border-radius: 11px; background: #16a34a; color: #ffffff; font-family: inherit; font-weight: 900; cursor: pointer; }
        @media (max-width: 560px) {
            .support-chat-launcher { left: 12px; bottom: 12px; }
            .support-chat-panel { left: 10px; bottom: 66px; width: calc(100vw - 20px); height: min(620px, calc(100vh - 78px)); }
            .support-chat-panel.keyboard-visible { top: calc(var(--chat-viewport-top, 0px) + 6px); bottom: auto; height: calc(var(--chat-viewport-height, 100dvh) - 12px); max-height: none; border-radius: 14px; }
            .support-chat-layout { grid-template-columns: 1fr; }
            .support-chat-threads { max-height: 132px; border-left: 0; border-bottom: 1px solid #e5e7eb; }
            .support-chat-input { font-size: 16px; }
        }
    </style>

    <button class="support-chat-launcher" id="supportChatLauncher" type="button">
        <span>{{ $chatIsAdmin ? 'محادثات العملاء' : 'تواصل مع خدمة العملاء' }}</span>
        <span class="chat-count" id="supportChatCount">0</span>
    </button>

    <section class="support-chat-panel" id="supportChatPanel" data-is-admin="{{ $chatIsAdmin ? '1' : '0' }}" data-conversations-url="{{ route('chat.conversations') }}" data-base-url="{{ url('/chat/conversations') }}">
        <div class="support-chat-head">
            <div>
                <h2 class="support-chat-title">{{ $chatIsAdmin ? 'محادثات العملاء' : 'خدمة العملاء' }}</h2>
                <p class="support-chat-subtitle">{{ $chatIsAdmin ? 'اختر العميل وتابع المحادثة' : 'اكتب رسالتك وسيتم الرد عليك من الإدارة' }}</p>
            </div>
            <button class="support-chat-close" id="supportChatClose" type="button">إغلاق</button>
        </div>
        <div class="support-chat-layout">
            <div class="support-chat-threads" id="supportChatThreads"></div>
            <div class="support-chat-main">
                <div class="support-chat-messages" id="supportChatMessages">
                    <div class="support-chat-empty">اضغط لبدء المحادثة</div>
                </div>
                <form class="support-chat-form" id="supportChatForm">
                    <textarea class="support-chat-input" id="supportChatInput" placeholder="اكتب رسالتك هنا..." rows="1" required></textarea>
                    <button class="support-chat-send" type="submit">إرسال</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const panel = document.getElementById('supportChatPanel');
            const launcher = document.getElementById('supportChatLauncher');
            if (!panel || !launcher || panel.dataset.chatReady === '1') return;
            panel.dataset.chatReady = '1';

            const closeButton = document.getElementById('supportChatClose');
            const threadsEl = document.getElementById('supportChatThreads');
            const messagesEl = document.getElementById('supportChatMessages');
            const form = document.getElementById('supportChatForm');
            const input = document.getElementById('supportChatInput');
            const countEl = document.getElementById('supportChatCount');
            const isAdmin = panel.dataset.isAdmin === '1';
            const conversationsUrl = panel.dataset.conversationsUrl;
            const baseUrl = panel.dataset.baseUrl;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let conversations = [];
            let currentConversationId = null;
            let pollTimer = null;
            let previousUnreadTotal = null;
            let initialMessagesLoaded = false;
            let viewportFrame = null;
            let expandedViewportHeight = window.visualViewport?.height || window.innerHeight;
            const notifiedReadMessages = new Set();

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));

            const formatTime = (value) => {
                if (!value) return '';
                try {
                    return new Intl.DateTimeFormat('ar-SA', {
                        hour: '2-digit',
                        minute: '2-digit',
                        day: '2-digit',
                        month: '2-digit',
                    }).format(new Date(value));
                } catch {
                    return '';
                }
            };

            const browserNotificationsSupported = () => 'Notification' in window;

            const requestBrowserNotificationPermission = async () => {
                if (!browserNotificationsSupported()) {
                    return 'unsupported';
                }

                if (Notification.permission !== 'default') {
                    return Notification.permission;
                }

                try {
                    return await Notification.requestPermission();
                } catch {
                    return 'denied';
                }
            };

            const notifyBrowser = (title, body, tag = 'alwrraq-notification') => {
                if (!browserNotificationsSupported() || Notification.permission !== 'granted') return;

                const notification = new Notification(title, {
                    body,
                    tag,
                    badge: '{{ asset('images/alwrraq-logo.jpeg') }}',
                    icon: '{{ asset('images/alwrraq-logo.jpeg') }}',
                    dir: 'rtl',
                });

                notification.onclick = () => {
                    window.focus();
                    panel.classList.add('active');
                    notification.close();
                };
            };

            const scanOrderAlerts = () => {
                if (!browserNotificationsSupported() || Notification.permission !== 'granted') return;

                const adminOrdersDot = document.querySelector('.nav-notice-dot');
                const customerOrdersDot = document.querySelector('.customer-notice-dot');
                const deliveredFilesDot = document.querySelector('[data-delivered-files-dot], [data-delivered-file-dot]');

                const alerts = [
                    {
                        active: !!adminOrdersDot,
                        key: 'alwrraq-admin-orders-alert',
                        title: 'طلب جديد',
                        body: 'وصل طلب أو ملف جديد يحتاج المتابعة.',
                    },
                    {
                        active: !!customerOrdersDot,
                        key: 'alwrraq-customer-orders-alert',
                        title: 'تحديث على طلبك',
                        body: 'يوجد تحديث جديد في صفحة طلباتك.',
                    },
                    {
                        active: !!deliveredFilesDot,
                        key: 'alwrraq-delivered-files-alert',
                        title: 'ملف مستلم جديد',
                        body: 'تم إرفاق ملف جديد لك داخل طلباتك.',
                    },
                ];

                alerts.forEach((alert) => {
                    if (!alert.active) {
                        sessionStorage.removeItem(alert.key);
                        return;
                    }

                    if (sessionStorage.getItem(alert.key) === '1') return;
                    sessionStorage.setItem(alert.key, '1');
                    notifyBrowser(alert.title, alert.body, alert.key);
                });
            };

            const updateUnread = () => {
                const total = conversations.reduce((sum, item) => sum + Number(item.unread_count || 0), 0);
                countEl.textContent = total;
                launcher.classList.toggle('has-unread', total > 0);

                if (previousUnreadTotal !== null && total > previousUnreadTotal) {
                    const newest = conversations.find((item) => Number(item.unread_count || 0) > 0);
                    notifyBrowser(
                        isAdmin ? 'رسالة جديدة من عميل' : 'رسالة جديدة من خدمة العملاء',
                        newest?.last_message || 'وصلتك رسالة جديدة في المحادثة.',
                        'alwrraq-chat-message'
                    );
                }

                previousUnreadTotal = total;
            };

            const openChatPanel = () => {
                panel.classList.add('active');
                document.body.dataset.chatScrollLocked = '1';
                document.body.style.overflow = 'hidden';
                syncChatViewport();
            };

            const closeChatPanel = () => {
                input.blur();
                panel.classList.remove('active');
                panel.classList.remove('keyboard-visible');
                delete document.body.dataset.chatScrollLocked;
                document.body.style.overflow = '';
            };

            const focusChatInput = () => {
                if (!panel.classList.contains('active')) return;

                try {
                    input.focus({ preventScroll: true });
                } catch {
                    input.focus();
                }
                input.setSelectionRange(input.value.length, input.value.length);

                requestAnimationFrame(() => {
                    if (!panel.classList.contains('active') || document.activeElement === input) return;
                    input.focus({ preventScroll: true });
                    input.setSelectionRange(input.value.length, input.value.length);
                });
            };

            const syncChatViewport = () => {
                if (viewportFrame) cancelAnimationFrame(viewportFrame);

                viewportFrame = requestAnimationFrame(() => {
                    viewportFrame = null;
                    const viewport = window.visualViewport;
                    const viewportHeight = viewport?.height || window.innerHeight;
                    const viewportTop = viewport?.offsetTop || 0;
                    if (document.activeElement !== input) {
                        expandedViewportHeight = Math.max(expandedViewportHeight, viewportHeight);
                    }
                    const keyboardInset = Math.max(
                        0,
                        window.innerHeight - viewportHeight - viewportTop,
                        expandedViewportHeight - viewportHeight
                    );

                    panel.style.setProperty('--chat-viewport-height', `${Math.round(viewportHeight)}px`);
                    panel.style.setProperty('--chat-viewport-top', `${Math.round(viewportTop)}px`);
                    panel.classList.toggle('keyboard-visible', panel.classList.contains('active') && keyboardInset > 100);

                    if (panel.classList.contains('active')) {
                        messagesEl.scrollTop = messagesEl.scrollHeight;
                    }
                });
            };

            const renderThreads = () => {
                if (!isAdmin) return;

                if (conversations.length === 0) {
                    threadsEl.innerHTML = '<div class="support-chat-empty">لا توجد محادثات بعد</div>';
                    return;
                }

                threadsEl.innerHTML = conversations.map((item) => `
                    <button class="support-chat-thread ${item.id === currentConversationId ? 'active' : ''}" type="button" data-chat-thread="${item.id}">
                        <strong>${escapeHtml(item.customer_name || 'عميل')}</strong>
                        <span>${escapeHtml(item.last_message || item.customer_phone || 'محادثة جديدة')}</span>
                        ${Number(item.unread_count || 0) > 0 ? `<em class="thread-unread">${item.unread_count}</em>` : ''}
                    </button>
                `).join('');
            };

            const renderMessages = (messages) => {
                if (!messages || messages.length === 0) {
                    messagesEl.innerHTML = '<div class="support-chat-empty">لا توجد رسائل بعد. ابدأ المحادثة الآن.</div>';
                    initialMessagesLoaded = true;
                    return;
                }

                if (initialMessagesLoaded) {
                    const readMessage = messages.find((message) => message.is_mine && message.read_at && !notifiedReadMessages.has(message.id));
                    if (readMessage) {
                        notifiedReadMessages.add(readMessage.id);
                        notifyBrowser('تمت قراءة رسالتك', 'فتح الطرف الآخر المحادثة واطلع على رسالتك.', `alwrraq-chat-read-${readMessage.id}`);
                    }
                } else {
                    messages.filter((message) => message.is_mine && message.read_at).forEach((message) => notifiedReadMessages.add(message.id));
                    initialMessagesLoaded = true;
                }

                messagesEl.innerHTML = messages.map((message) => `
                    <div class="support-message ${message.is_mine ? 'mine' : 'other'}">
                        <div class="support-message-bubble">
                            <span class="support-message-name">${escapeHtml(message.sender_name || 'مستخدم')}</span>
                            <div class="support-message-text"></div>
                            <span class="support-message-time">${formatTime(message.created_at)}</span>
                        </div>
                    </div>
                `).join('');

                messagesEl.querySelectorAll('.support-message-text').forEach((node, index) => {
                    node.textContent = messages[index]?.message || '';
                });
                messagesEl.scrollTop = messagesEl.scrollHeight;
            };

            const loadConversations = async () => {
                const response = await fetch(conversationsUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('chat conversations failed');
                const data = await response.json();
                conversations = data.conversations || [];

                if (!currentConversationId && conversations.length > 0) {
                    currentConversationId = conversations[0].id;
                }

                renderThreads();
                updateUnread();
                return conversations;
            };

            const loadMessages = async (conversationId = currentConversationId) => {
                if (!conversationId) {
                    messagesEl.innerHTML = '<div class="support-chat-empty">لا توجد محادثة مختارة</div>';
                    return;
                }

                currentConversationId = conversationId;
                const response = await fetch(`${baseUrl}/${conversationId}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('chat messages failed');
                const data = await response.json();
                renderMessages(data.messages || []);

                const item = conversations.find((conversation) => conversation.id === conversationId);
                if (item) item.unread_count = 0;
                renderThreads();
                updateUnread();
            };

            const refresh = async () => {
                try {
                    await loadConversations();
                    if (panel.classList.contains('active') && currentConversationId) {
                        await loadMessages(currentConversationId);
                    }
                } catch (error) {
                    console.warn(error);
                }
            };

            launcher.addEventListener('click', async () => {
                openChatPanel();
                focusChatInput();
                await requestBrowserNotificationPermission();
                await refresh();
                scanOrderAlerts();
                focusChatInput();
            });

            closeButton.addEventListener('click', closeChatPanel);

            document.addEventListener('pointerdown', (event) => {
                if (!panel.classList.contains('active')) return;
                if (panel.contains(event.target) || launcher.contains(event.target)) return;

                closeChatPanel();
            });

            threadsEl.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-chat-thread]');
                if (!button) return;
                await loadMessages(Number(button.dataset.chatThread));
                focusChatInput();
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const message = input.value.trim();
                if (!message || !currentConversationId) return;

                input.value = '';
                const response = await fetch(`${baseUrl}/${currentConversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ message }),
                });

                if (response.ok) {
                    await loadConversations();
                    await loadMessages(currentConversationId);
                    focusChatInput();
                }
            });

            input.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;

                event.preventDefault();
                form.requestSubmit();
            });

            window.visualViewport?.addEventListener('resize', syncChatViewport);
            window.visualViewport?.addEventListener('scroll', syncChatViewport);
            window.addEventListener('resize', syncChatViewport);
            window.addEventListener('orientationchange', syncChatViewport);

            refresh();
            requestAnimationFrame(scanOrderAlerts);
            pollTimer = setInterval(refresh, 12000);
            window.addEventListener('beforeunload', () => {
                clearInterval(pollTimer);
                if (viewportFrame) cancelAnimationFrame(viewportFrame);
                if (document.body.dataset.chatScrollLocked === '1') {
                    document.body.style.overflow = '';
                }
            });
        })();
    </script>
@endauth
