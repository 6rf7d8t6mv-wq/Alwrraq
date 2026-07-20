@if (auth()->check())
    @php
        $livePageSnapshot = app(\App\Services\LivePageUpdateService::class)->snapshot(auth()->user());
        $liveRouteName = request()->route()?->getName() ?? '';
        $liveNoticeOnlyRoutes = ['home', 'orders.file.view', 'orders.delivered-file', 'admin.files.view', 'admin.files.download', 'admin.delivered-files.download'];
        $liveReloadRoutes = ['cart.index', 'cart.show', 'cart.payment', 'account.settings'];
        $liveRefreshMode = in_array($liveRouteName, $liveNoticeOnlyRoutes, true)
            ? 'notice'
            : (in_array($liveRouteName, $liveReloadRoutes, true) ? 'reload' : 'main');
    @endphp

    <style>
        .live-page-indicator {
            position: fixed;
            left: 50%;
            bottom: max(10px, env(safe-area-inset-bottom));
            z-index: 290;
            max-width: min(330px, calc(100vw - 24px));
            padding: 7px 11px;
            border: 1px solid #86efac;
            border-radius: 999px;
            background: #f0fdf4;
            color: #166534;
            box-shadow: 0 12px 30px rgba(15,23,42,.16);
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            opacity: 0;
            pointer-events: none;
            transform: translate(-50%, 8px);
            transition: opacity .16s ease, transform .16s ease;
        }
        .live-page-indicator.active { opacity: 1; transform: translate(-50%, 0); }
        .live-page-indicator.waiting { border-color: #fde68a; background: #fffbeb; color: #92400e; }
        .live-page-indicator.error { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        @media (max-width: 560px) {
            .live-page-indicator { bottom: max(7px, env(safe-area-inset-bottom)); padding: 5px 8px; font-size: 8px; }
        }
    </style>

    <div class="live-page-indicator" id="livePageIndicator" aria-live="polite"></div>

    <script>
        (() => {
            if (window.__alwrraqLiveUpdatesStarted) return;
            window.__alwrraqLiveUpdatesStarted = true;

            const endpoint = @json(route('live-status'));
            const refreshMode = @json($liveRefreshMode);
            const ordersUrl = @json(route(auth()->user()->role === 'admin' ? 'admin.orders' : 'orders.index'));
            const indicator = document.getElementById('livePageIndicator');
            const scrollKey = `alwrraq-live-scroll:${window.location.pathname}${window.location.search}`;
            let revision = @json($livePageSnapshot['revision']);
            let pricingRevision = @json($livePageSnapshot['pricing_revision']);
            let ordersCount = Number(@json($livePageSnapshot['orders_count']));
            let updating = false;
            let dirty = false;
            let indicatorTimer = null;

            const savedScroll = sessionStorage.getItem(scrollKey);
            if (savedScroll !== null) {
                sessionStorage.removeItem(scrollKey);
                requestAnimationFrame(() => window.scrollTo({ top: Number(savedScroll) || 0, behavior: 'auto' }));
            }

            const showIndicator = (message, state = '', duration = 3500) => {
                if (!indicator) return;
                indicator.textContent = message;
                indicator.classList.remove('waiting', 'error');
                if (state) indicator.classList.add(state);
                indicator.classList.add('active');
                clearTimeout(indicatorTimer);
                if (duration > 0) indicatorTimer = setTimeout(() => indicator.classList.remove('active'), duration);
            };

            const updateOrderNotice = (unseenCount) => {
                const link = document.querySelector(`[data-admin-orders-link], a[href="${ordersUrl}"], a[href$="/my-orders"]`);
                if (!link) return;

                let dot = link.querySelector('.nav-notice-dot, .customer-notice-dot');
                if (unseenCount > 0 && !dot) {
                    dot = document.createElement('span');
                    dot.className = document.body.classList.contains('customer-app-page') ? 'customer-notice-dot' : 'nav-notice-dot';
                    dot.setAttribute('aria-label', 'تحديث جديد');
                    link.appendChild(dot);
                } else if (unseenCount === 0) {
                    dot?.remove();
                }
            };

            const pageIsBusy = () => {
                if (dirty) return true;
                if (document.querySelector('dialog[open]')) return true;
                if ([...document.querySelectorAll('input[type="file"]')].some((input) => input.files?.length)) return true;

                const active = document.activeElement;
                return Boolean(active?.closest('main, #adminModalBody') && (
                    active.matches('input, textarea, select') || active.isContentEditable
                ));
            };

            const refreshMain = async (nextRevision) => {
                const response = await fetch(window.location.href, {
                    cache: 'no-store',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Live-Page-Refresh': '1' },
                });
                if (!response.ok) throw new Error('live-refresh-failed');

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const nextMain = nextDocument.querySelector('main');
                const currentMain = document.querySelector('main');
                if (!nextMain || !currentMain) throw new Error('live-main-missing');

                const activeModal = document.getElementById('adminModal');
                const modalWasOpen = activeModal?.classList.contains('active') || false;
                const modalTemplateId = modalWasOpen ? activeModal.dataset.templateId : '';
                const modalTitle = modalWasOpen ? activeModal.dataset.modalTitle : '';
                const modalBody = modalWasOpen ? document.getElementById('adminModalBody') : null;
                const modalScrollTop = modalBody?.scrollTop || 0;

                currentMain.innerHTML = nextMain.innerHTML;
                revision = nextRevision;
                dirty = false;
                window.localizeDateTimes?.(currentMain);
                window.bindAutoSearchForms?.(currentMain);
                window.bindEnglishNumberWarnings?.(currentMain);
                document.dispatchEvent(new CustomEvent('alwrraq:content-updated', { detail: { root: currentMain } }));

                if (modalWasOpen && modalTemplateId) {
                    const reopened = window.openAdminModal?.(modalTitle, modalTemplateId);
                    if (reopened) {
                        requestAnimationFrame(() => {
                            const refreshedModalBody = document.getElementById('adminModalBody');
                            if (refreshedModalBody) refreshedModalBody.scrollTop = modalScrollTop;
                        });
                    } else {
                        activeModal?.classList.remove('active');
                    }
                }
            };

            const applyUpdate = async (nextRevision, hasNewOrder) => {
                if (refreshMode === 'notice') {
                    revision = nextRevision;
                    showIndicator(hasNewOrder ? 'وصل تحديث جديد في الطلبات' : 'تم تحديث البيانات');
                    return;
                }

                if (pageIsBusy()) {
                    showIndicator('يوجد تحديث جديد وسيُطبق بعد الانتهاء من الإدخال', 'waiting', 4500);
                    return;
                }

                if (refreshMode === 'reload') {
                    sessionStorage.setItem(scrollKey, String(window.scrollY));
                    window.location.reload();
                    return;
                }

                await refreshMain(nextRevision);
                showIndicator(hasNewOrder ? 'وصل طلب جديد وتم تحديث الصفحة' : 'تم تحديث الصفحة تلقائيًا');
            };

            const poll = async () => {
                if (document.hidden || updating) return;
                updating = true;

                try {
                    const response = await fetch(endpoint, {
                        cache: 'no-store',
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('live-status-failed');

                    const status = await response.json();
                    updateOrderNotice(Number(status.unseen_count || 0));
                    const pricingChanged = status.pricing_revision && status.pricing_revision !== pricingRevision;
                    if (pricingChanged) {
                        if (pageIsBusy()) {
                            showIndicator('تم تعديل الأسعار وستظهر بعد الانتهاء من الإدخال', 'waiting', 4500);
                            return;
                        }

                        pricingRevision = status.pricing_revision;
                        sessionStorage.setItem(scrollKey, String(window.scrollY));
                        window.location.reload();
                        return;
                    }
                    if (status.revision === revision) return;

                    const hasNewOrder = Number(status.orders_count) > ordersCount;
                    await applyUpdate(status.revision, hasNewOrder);
                    ordersCount = Number(status.orders_count);
                } catch (error) {
                    showIndicator('تعذر التحديث المباشر مؤقتًا وسيعاد الاتصال تلقائيًا', 'error', 3000);
                } finally {
                    updating = false;
                }
            };

            document.addEventListener('input', (event) => {
                if (event.target.closest('main, #adminModalBody') && event.isTrusted) dirty = true;
            }, true);
            document.addEventListener('change', (event) => {
                if (event.target.closest('main, #adminModalBody') && event.isTrusted) dirty = true;
            }, true);
            document.addEventListener('submit', () => { dirty = true; }, true);

            showIndicator('التحديث المباشر متصل', '', 1800);
            const timer = setInterval(poll, 1000);
            poll();
            document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
            window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
        })();
    </script>
@endif
