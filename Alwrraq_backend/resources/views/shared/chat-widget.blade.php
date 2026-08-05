@auth
    @php
        $chatIsAdmin = auth()->user()->role === 'admin';
    @endphp

    <style>
        .support-chat-launcher,
        .support-chat-panel button { margin: 0; }
        .support-chat-launcher { position: fixed; left: 18px; bottom: 18px; z-index: 90; width: auto; display: inline-flex; align-items: center; gap: 9px; min-height: 46px; padding: 12px 16px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #0f4c81, #10233f); color: #ffffff; box-shadow: 0 18px 44px rgba(15, 23, 42, 0.24); cursor: pointer; font-family: inherit; font-weight: 900; }
        .support-chat-launcher:hover { transform: translateY(-1px); }
        body.customer-service-view .support-chat-launcher,
        body.customer-service-view .support-chat-panel { display: none !important; }
        .support-chat-launcher .chat-count { display: none; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #dc2626; color: #ffffff; font-size: 12px; line-height: 20px; text-align: center; }
        .support-chat-launcher.has-unread .chat-count { display: inline-block; }
        .support-chat-panel { position: fixed; left: 18px; bottom: 78px; z-index: 91; width: min(420px, calc(100vw - 28px)); height: min(620px, calc(100vh - 104px)); display: none; flex-direction: column; overflow: hidden; border: 1px solid #dbe3ef; border-radius: 18px; background: #ffffff; box-shadow: 0 28px 90px rgba(15, 23, 42, 0.28); direction: rtl; }
        .support-chat-panel.active { display: flex; }
        .support-chat-head { flex: 0 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; background: #0f172a; color: #ffffff; }
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
        .support-message-attachment-image { display: block; max-width: min(100%, 420px); max-height: 440px; margin-bottom: 6px; border-radius: 9px; object-fit: contain; background: #e5e7eb; }
        .support-message-file { display: flex; align-items: center; gap: 9px; min-width: 190px; margin-bottom: 6px; padding: 10px; border-radius: 9px; background: rgba(255,255,255,.58); color: inherit; text-decoration: none; font-weight: 800; }
        .support-message-file-icon { font-size: 22px; }.support-message-file-name { min-width: 0; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .support-chat-form { display: flex; flex-wrap: wrap; gap: 8px; padding: 12px; border-top: 1px solid #e5e7eb; background: #ffffff; }
        .support-chat-attachment-preview { display: none; flex: 0 0 100%; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 11px; border-radius: 10px; background: #e2e8f0; color: #334155; font-size: 12px; font-weight: 800; }
        .support-chat-attachment-preview.active { display: flex; }.support-chat-attachment-preview span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }.support-chat-attachment-remove { border: 0; background: transparent; color: #b91c1c; cursor: pointer; font: inherit; font-weight: 900; }
        .support-chat-attach { flex: 0 0 auto; width: 42px; height: 42px; display: inline-grid; place-items: center; padding: 0; border: 0; border-radius: 50%; background: transparent; color: #475569; cursor: pointer; font-size: 23px; }
        .support-chat-attach input { display: none; }
        .support-chat-input { flex: 1; min-width: 0; resize: none; min-height: 42px; max-height: 96px; padding: 10px 11px; border: 1px solid #cbd5e1; border-radius: 11px; font-family: inherit; font-size: 14px; }
        .support-chat-send { flex: 0 0 auto; width: auto; padding: 10px 14px; border: 0; border-radius: 11px; background: #16a34a; color: #ffffff; font-family: inherit; font-weight: 900; cursor: pointer; }
        @media (max-width: 560px) {
            body.support-chat-open { position: fixed; inset: 0; width: 100%; overflow: hidden; overscroll-behavior: none; }
            .support-chat-launcher { left: 12px; bottom: 12px; }
            body.support-chat-open .support-chat-launcher { display: none; }
            .support-chat-panel,
            .support-chat-panel.keyboard-visible { top: var(--chat-viewport-top, 0px); right: 0; bottom: auto; left: 0; width: 100%; height: var(--chat-viewport-height, 100dvh); max-height: none; border: 0; border-radius: 0; box-shadow: none; z-index: 2147483000; }
            .support-chat-head { min-height: 64px; padding: max(10px, env(safe-area-inset-top)) 12px 10px; justify-content: flex-start; background: #ffffff; color: #111827; border-bottom: 1px solid #e5e7eb; box-shadow: 0 1px 5px rgba(15,23,42,.08); }
            .support-chat-head > div { min-width: 0; flex: 1; }
            .support-chat-title { color: #111827; font-size: 16px; }
            .support-chat-subtitle { color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .support-chat-close { order: -1; display: inline-flex; align-items: center; gap: 5px; padding: 8px 6px; border: 0; background: transparent; color: #0f4c81; border-radius: 10px; font-size: 14px; }
            .support-chat-close-icon { font-size: 24px; line-height: 1; }
            .support-chat-layout { grid-template-columns: 1fr; }
            .support-chat-threads { max-height: 132px; border-left: 0; border-bottom: 1px solid #e5e7eb; }
            .support-chat-messages { padding: 14px 10px; background-color: #efeae2; background-image: radial-gradient(circle at 20% 30%, rgba(15,76,129,.035) 0 2px, transparent 2.5px), radial-gradient(circle at 75% 68%, rgba(15,76,129,.03) 0 2px, transparent 2.5px); background-size: 42px 42px, 54px 54px; }
            .support-message { margin-bottom: 7px; }
            .support-message-bubble { max-width: 86%; border: 0; border-radius: 11px; box-shadow: 0 1px 2px rgba(15,23,42,.14); }
            .support-message.mine .support-message-bubble { background: #d9fdd3; border-color: #d9fdd3; color: #111827; }
            .support-chat-form { flex: 0 0 auto; align-items: flex-end; padding: 7px 8px max(7px, env(safe-area-inset-bottom)); background: #f0f2f5; border-top: 0; }
            .support-chat-input { min-height: 44px; max-height: 104px; padding: 11px 16px; border: 0; border-radius: 23px; background: #ffffff; box-shadow: 0 1px 2px rgba(15,23,42,.1); font-size: 16px; line-height: 22px; outline: none; }
            .support-chat-send { width: 46px; height: 46px; overflow: hidden; padding: 0; border-radius: 50%; font-size: 0; box-shadow: 0 2px 5px rgba(15,23,42,.18); }
            .support-chat-send::after { content: '\27A4'; display: block; color: #ffffff; font-size: 21px; line-height: 46px; transform: rotate(180deg); }
        }
        @media (min-width: 561px) {
            body.support-chat-open { position: fixed; inset: 0; width: 100%; overflow: hidden; overscroll-behavior: none; }
            body.support-chat-open::before { content: ''; position: fixed; inset: 0; z-index: 2147482990; background: rgba(15,23,42,.48); backdrop-filter: blur(3px); }
            body.support-chat-open .support-chat-launcher { display: none; }
            .support-chat-panel,
            .support-chat-panel.keyboard-visible { top: 50%; right: auto; bottom: auto; left: 50%; width: min(1440px, calc(100vw - 72px)); height: min(900px, calc(100vh - 52px)); max-height: none; transform: translate(-50%, -50%); border: 1px solid #d8dde1; border-radius: 16px; box-shadow: 0 28px 90px rgba(15,23,42,.4); z-index: 2147483000; }
            .support-chat-head { min-height: 68px; padding: 11px 22px; justify-content: flex-start; background: #ffffff; color: #111827; border-bottom: 1px solid #dfe3e7; box-shadow: 0 1px 4px rgba(15,23,42,.08); }
            .support-chat-head > div { min-width: 0; flex: 1; }
            .support-chat-title { color: #111827; font-size: 17px; }
            .support-chat-subtitle { color: #64748b; }
            .support-chat-close { order: -1; display: inline-flex; align-items: center; gap: 6px; padding: 9px 10px; border: 0; background: transparent; color: #0f4c81; border-radius: 10px; font-size: 14px; }
            .support-chat-close:hover { background: #eef6fc; }
            .support-chat-close-icon { font-size: 25px; line-height: 1; }
            .support-chat-layout { grid-template-columns: {{ $chatIsAdmin ? '320px minmax(0, 1fr)' : '1fr' }}; background: #efeae2; }
            .support-chat-threads { border-left: 1px solid #d8dde1; background: #ffffff; }
            .support-chat-thread { padding: 14px 16px; }
            .support-chat-thread strong { font-size: 14px; }
            .support-chat-thread span { font-size: 12px; }
            .support-chat-messages { padding: 22px max(28px, 7vw); background-color: #efeae2; background-image: radial-gradient(circle at 20% 30%, rgba(15,76,129,.035) 0 2px, transparent 2.5px), radial-gradient(circle at 75% 68%, rgba(15,76,129,.03) 0 2px, transparent 2.5px); background-size: 42px 42px, 54px 54px; }
            .support-message-bubble { max-width: min(72%, 720px); border: 0; border-radius: 11px; box-shadow: 0 1px 2px rgba(15,23,42,.14); }
            .support-message.mine .support-message-bubble { background: #d9fdd3; border-color: #d9fdd3; color: #111827; }
            .support-chat-form { flex: 0 0 auto; align-items: flex-end; padding: 10px max(18px, 5vw); background: #f0f2f5; border-top: 0; }
            .support-chat-attachment-preview { max-width: 760px; margin-inline: auto; }
            .support-chat-attach { width: 48px; height: 48px; }
            .support-chat-input { min-height: 46px; max-height: 120px; padding: 12px 18px; border: 0; border-radius: 24px; background: #ffffff; box-shadow: 0 1px 2px rgba(15,23,42,.1); font-size: 16px; line-height: 22px; outline: none; }
            .support-chat-send { width: 48px; height: 48px; overflow: hidden; padding: 0; border-radius: 50%; font-size: 0; box-shadow: 0 2px 5px rgba(15,23,42,.18); }
            .support-chat-send::after { content: '\27A4'; display: block; color: #ffffff; font-size: 22px; line-height: 48px; transform: rotate(180deg); }
        }
    </style>

    <button class="support-chat-launcher" id="supportChatLauncher" type="button">
        <span>{{ $chatIsAdmin ? 'محادثات العملاء' : 'تواصل مع خدمة العملاء' }}</span>
        <span class="chat-count" id="supportChatCount">0</span>
    </button>

    <section class="support-chat-panel" id="supportChatPanel" data-is-admin="{{ $chatIsAdmin ? '1' : '0' }}" data-conversations-url="{{ route('chat.conversations') }}" data-base-url="{{ url('/chat/conversations') }}">
        <div class="support-chat-head">
            <div>
                <h2 class="support-chat-title" id="supportChatTitle">{{ $chatIsAdmin ? 'محادثات العملاء' : 'خدمة العملاء' }}</h2>
                <p class="support-chat-subtitle" id="supportChatSubtitle">{{ $chatIsAdmin ? 'اختر العميل وتابع المحادثة' : 'اكتب رسالتك وسيتم الرد عليك من الإدارة' }}</p>
            </div>
            <button class="support-chat-close" id="supportChatClose" type="button"><span class="support-chat-close-icon" aria-hidden="true">&#8594;</span><span>رجوع</span></button>
        </div>
        <div class="support-chat-layout">
            <div class="support-chat-threads" id="supportChatThreads"></div>
            <div class="support-chat-main">
                <div class="support-chat-messages" id="supportChatMessages">
                    <div class="support-chat-empty">اضغط لبدء المحادثة</div>
                </div>
                <form class="support-chat-form" id="supportChatForm">
                    <div class="support-chat-attachment-preview" id="supportChatAttachmentPreview"><span></span><button class="support-chat-attachment-remove" id="supportChatAttachmentRemove" type="button">إزالة</button></div>
                    <label class="support-chat-attach" title="إرفاق صورة أو ملف" aria-label="إرفاق صورة أو ملف">&#128206;<input id="supportChatAttachment" type="file" accept="image/jpeg,image/png,image/webp,image/gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip"></label>
                    <textarea class="support-chat-input" id="supportChatInput" placeholder="اكتب رسالتك هنا..." rows="1"></textarea>
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
            const attachmentInput = document.getElementById('supportChatAttachment');
            const attachmentPreview = document.getElementById('supportChatAttachmentPreview');
            const attachmentPreviewName = attachmentPreview?.querySelector('span');
            const attachmentRemove = document.getElementById('supportChatAttachmentRemove');
            const titleEl = document.getElementById('supportChatTitle');
            const subtitleEl = document.getElementById('supportChatSubtitle');
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
            let renderedConversationId = null;
            let renderedMessagesFingerprint = '';
            let viewportFrame = null;
            let expandedViewportHeight = window.visualViewport?.height || window.innerHeight;
            let lockedPageScrollY = 0;
            let chatHistoryEntry = false;
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

            const formatFileSize = (bytes) => {
                const size = Number(bytes || 0);
                if (!size) return '';
                if (size < 1024 * 1024) return `${Math.max(1, Math.round(size / 1024))} KB`;
                return `${(size / (1024 * 1024)).toFixed(1)} MB`;
            };

            const updateConversationHeader = (conversation = null) => {
                const current = conversation || conversations.find((item) => Number(item.id) === Number(currentConversationId));
                if (isAdmin && current) {
                    titleEl.textContent = current.customer_name || 'عميل';
                    subtitleEl.textContent = current.customer_phone || 'محادثة العميل';
                    return;
                }
                titleEl.textContent = 'خدمة العملاء';
                subtitleEl.textContent = 'متصل — اكتب رسالتك وسيتم الرد عليك';
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
                    openChatPanel();
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
                if (panel.classList.contains('active')) return;
                panel.classList.add('active');
                document.body.dataset.chatScrollLocked = '1';
                lockedPageScrollY = window.scrollY;
                document.body.style.top = `-${lockedPageScrollY}px`;
                document.body.classList.add('support-chat-open');
                if (!chatHistoryEntry) {
                    history.pushState({ ...(history.state || {}), supportChatOpen: true }, '', location.href);
                    chatHistoryEntry = true;
                }
                syncChatViewport();
            };

            const closeChatPanel = ({ fromHistory = false } = {}) => {
                if (!panel.classList.contains('active')) return;
                input.blur();
                panel.classList.remove('active');
                panel.classList.remove('keyboard-visible');
                delete document.body.dataset.chatScrollLocked;
                document.body.style.overflow = '';
                document.body.classList.remove('support-chat-open');
                document.body.style.top = '';
                window.scrollTo(0, lockedPageScrollY);
                if (chatHistoryEntry && !fromHistory) {
                    chatHistoryEntry = false;
                    history.back();
                } else if (fromHistory) {
                    chatHistoryEntry = false;
                }
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
                const keepAtBottom = messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 80;

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

                    if (panel.classList.contains('active') && keepAtBottom) {
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

            const renderMessages = (messages, { forceBottom = false } = {}) => {
                const fingerprint = (messages || []).map((message) => [
                    message.id,
                    message.message,
                    message.attachment_url,
                    message.attachment_name,
                    message.attachment_size,
                    message.read_at,
                    message.created_at,
                ].join(':')).join('|');
                const sameConversation = renderedConversationId === currentConversationId;

                // Polling runs every second. Avoid replacing an unchanged
                // message list because doing so resets a customer's reading position.
                if (sameConversation && fingerprint === renderedMessagesFingerprint) return;

                const previousScrollTop = messagesEl.scrollTop;
                const wasNearBottom = messagesEl.scrollHeight - previousScrollTop - messagesEl.clientHeight < 80;
                const shouldScrollToBottom = forceBottom || !sameConversation || !initialMessagesLoaded || wasNearBottom;
                renderedConversationId = currentConversationId;
                renderedMessagesFingerprint = fingerprint;

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

                messagesEl.innerHTML = messages.map((message, index) => {
                    const attachment = message.attachment_url
                        ? (message.attachment_is_image
                            ? `<a href="${escapeHtml(message.attachment_url)}" target="_blank" rel="noopener"><img class="support-message-attachment-image" src="${escapeHtml(message.attachment_url)}" alt="${escapeHtml(message.attachment_name || 'صورة مرفقة')}" loading="lazy"></a>`
                            : `<a class="support-message-file" href="${escapeHtml(message.attachment_url)}" target="_blank" rel="noopener"><span class="support-message-file-icon">&#128206;</span><span class="support-message-file-name">${escapeHtml(message.attachment_name || 'ملف مرفق')}</span><small>${escapeHtml(formatFileSize(message.attachment_size))}</small></a>`)
                        : '';
                    return `
                    <div class="support-message ${message.is_mine ? 'mine' : 'other'}">
                        <div class="support-message-bubble">
                            <span class="support-message-name">${escapeHtml(message.sender_name || 'مستخدم')}</span>
                            ${attachment}
                            ${message.message ? `<div class="support-message-text" data-message-index="${index}"></div>` : ''}
                            <span class="support-message-time">${formatTime(message.created_at)}</span>
                        </div>
                    </div>
                `;
                }).join('');

                messagesEl.querySelectorAll('.support-message-text').forEach((node) => {
                    node.textContent = messages[Number(node.dataset.messageIndex)]?.message || '';
                });
                if (shouldScrollToBottom) {
                    messagesEl.scrollTop = messagesEl.scrollHeight;
                } else {
                    messagesEl.scrollTop = previousScrollTop;
                }
            };

            const loadConversations = async () => {
                const response = await fetch(conversationsUrl, { cache: 'no-store', headers: { Accept: 'application/json', 'Cache-Control': 'no-cache' } });
                if (!response.ok) throw new Error('chat conversations failed');
                const data = await response.json();
                conversations = data.conversations || [];

                if (!currentConversationId && conversations.length > 0) {
                    currentConversationId = conversations[0].id;
                }

                updateConversationHeader();
                renderThreads();
                updateUnread();
                return conversations;
            };

            const loadMessages = async (conversationId = currentConversationId, { forceBottom = false } = {}) => {
                if (!conversationId) {
                    messagesEl.innerHTML = '<div class="support-chat-empty">لا توجد محادثة مختارة</div>';
                    return;
                }

                const changedConversation = Number(currentConversationId) !== Number(conversationId);
                currentConversationId = conversationId;
                const response = await fetch(`${baseUrl}/${conversationId}`, { cache: 'no-store', headers: { Accept: 'application/json', 'Cache-Control': 'no-cache' } });
                if (!response.ok) throw new Error('chat messages failed');
                const data = await response.json();
                updateConversationHeader(data.conversation);
                renderMessages(data.messages || [], { forceBottom: forceBottom || changedConversation });

                const item = conversations.find((conversation) => conversation.id === conversationId);
                if (item) item.unread_count = 0;
                renderThreads();
                updateUnread();
            };

            let refreshing = false;
            const refresh = async () => {
                if (document.hidden || refreshing) return;
                refreshing = true;
                try {
                    await loadConversations();
                    if (panel.classList.contains('active') && currentConversationId) {
                        await loadMessages(currentConversationId);
                    }
                } catch (error) {
                    console.warn(error);
                } finally {
                    refreshing = false;
                }
            };

            launcher.addEventListener('click', async () => {
                openChatPanel();
                await refresh();
                scanOrderAlerts();
                requestBrowserNotificationPermission().then(scanOrderAlerts);
            });

            closeButton.addEventListener('click', () => closeChatPanel());

            window.addEventListener('popstate', () => {
                if (panel.classList.contains('active')) closeChatPanel({ fromHistory: true });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && panel.classList.contains('active')) closeChatPanel();
            });

            document.addEventListener('pointerdown', (event) => {
                if (!panel.classList.contains('active')) return;
                if (panel.contains(event.target) || launcher.contains(event.target)) return;

                closeChatPanel();
            });

            threadsEl.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-chat-thread]');
                if (!button) return;
                await loadMessages(Number(button.dataset.chatThread), { forceBottom: true });
                focusChatInput();
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const message = input.value.trim();
                const attachment = attachmentInput?.files?.[0] || null;
                if ((!message && !attachment) || !currentConversationId) return;

                const payload = new FormData();
                if (message) payload.append('message', message);
                if (attachment) payload.append('attachment', attachment);
                const response = await fetch(`${baseUrl}/${currentConversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: payload,
                });

                if (response.ok) {
                    input.value = '';
                    input.style.height = '';
                    attachmentInput.value = '';
                    attachmentPreview.classList.remove('active');
                    await loadConversations();
                    await loadMessages(currentConversationId, { forceBottom: true });
                    focusChatInput();
                } else if (response.status === 422) {
                    const error = await response.json().catch(() => null);
                    alert(Object.values(error?.errors || {}).flat()[0] || 'تعذر إرفاق الملف. تأكد من نوعه وألا يتجاوز 15 ميجابايت.');
                }
            });

            attachmentInput?.addEventListener('change', () => {
                const file = attachmentInput.files?.[0];
                if (!file) {
                    attachmentPreview.classList.remove('active');
                    return;
                }
                if (file.size > 15 * 1024 * 1024) {
                    attachmentInput.value = '';
                    attachmentPreview.classList.remove('active');
                    alert('حجم الملف يجب ألا يتجاوز 15 ميجابايت.');
                    return;
                }
                attachmentPreviewName.textContent = `${file.name} — ${formatFileSize(file.size)}`;
                attachmentPreview.classList.add('active');
            });

            attachmentRemove?.addEventListener('click', () => {
                attachmentInput.value = '';
                attachmentPreview.classList.remove('active');
            });

            input.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;

                event.preventDefault();
                form.requestSubmit();
            });

            input.addEventListener('input', () => {
                input.style.height = 'auto';
                const maximumHeight = window.matchMedia('(max-width: 560px)').matches ? 104 : 120;
                input.style.height = `${Math.min(input.scrollHeight, maximumHeight)}px`;
                syncChatViewport();
            });

            window.visualViewport?.addEventListener('resize', syncChatViewport);
            window.visualViewport?.addEventListener('scroll', syncChatViewport);
            window.addEventListener('resize', syncChatViewport);
            window.addEventListener('orientationchange', syncChatViewport);

            refresh();
            requestAnimationFrame(scanOrderAlerts);
            pollTimer = setInterval(refresh, 1000);
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
