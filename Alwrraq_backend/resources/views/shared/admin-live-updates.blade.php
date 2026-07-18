@if (auth()->check() && auth()->user()->role === 'admin' && auth()->user()->hasAdminPermission('orders_view'))
    @php
        $adminLiveSnapshot = app(\App\Services\AdminLiveUpdateService::class)->snapshot();
        $adminLiveRefreshMain = $adminLiveRefreshMain ?? false;
    @endphp
    <style>
        .admin-live-indicator {
            position: fixed;
            left: 12px;
            bottom: 12px;
            z-index: 260;
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: min(260px, calc(100vw - 24px));
            padding: 7px 10px;
            border: 1px solid #86efac;
            border-radius: 9px;
            background: #f0fdf4;
            color: #166534;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
            font-size: 10px;
            font-weight: 900;
            text-decoration: none;
            opacity: 0;
            pointer-events: none;
            transform: translateY(8px);
            transition: opacity 160ms ease, transform 160ms ease;
        }
        .admin-live-indicator::before { content: ''; width: 7px; height: 7px; flex: 0 0 auto; border-radius: 999px; background: #16a34a; }
        .admin-live-indicator.active { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .admin-live-indicator.error { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        .admin-live-indicator.error::before { background: #dc2626; }
        @media (max-width: 560px) {
            .admin-live-indicator { left: 7px; bottom: 7px; padding: 5px 7px; border-radius: 7px; font-size: 8px; }
        }
    </style>

    <a class="admin-live-indicator" id="adminLiveIndicator" href="{{ route('admin.orders') }}" aria-live="polite">التحديث المباشر يعمل</a>

    <script>
        (() => {
            const endpoint = @json(route('admin.live-status'));
            const refreshMain = @json((bool) $adminLiveRefreshMain);
            const indicator = document.getElementById('adminLiveIndicator');
            let revision = @json($adminLiveSnapshot['revision']);
            let ordersCount = Number(@json($adminLiveSnapshot['orders_count']));
            let updating = false;
            let indicatorTimer = null;

            const showIndicator = (message, isError = false, duration = 6000) => {
                if (!indicator) return;
                indicator.textContent = message;
                indicator.classList.toggle('error', isError);
                indicator.classList.add('active');
                clearTimeout(indicatorTimer);
                if (duration > 0) {
                    indicatorTimer = setTimeout(() => indicator.classList.remove('active'), duration);
                }
            };

            const updateOrdersNotice = (unseenCount) => {
                const orderLink = document.querySelector('[data-admin-orders-link]');
                if (!orderLink) return;

                let dot = orderLink.querySelector('.nav-notice-dot');
                if (unseenCount > 0 && !dot) {
                    dot = document.createElement('span');
                    dot.className = 'nav-notice-dot';
                    dot.setAttribute('aria-label', 'طلبات جديدة');
                    orderLink.appendChild(dot);
                } else if (unseenCount === 0) {
                    dot?.remove();
                }
            };

            const pageIsBusy = () => {
                const modal = document.getElementById('adminModal');
                if (modal?.classList.contains('active')) return true;

                const active = document.activeElement;
                return Boolean(active?.closest('main') && (
                    active.matches('input, textarea, select') || active.isContentEditable
                ));
            };

            const refreshVisibleContent = async (nextRevision) => {
                if (!refreshMain || updating || pageIsBusy()) return false;

                updating = true;
                try {
                    const response = await fetch(window.location.href, {
                        cache: 'no-store',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Admin-Live-Refresh': '1',
                        },
                    });
                    if (!response.ok) throw new Error('live-refresh-failed');

                    const html = await response.text();
                    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                    const nextMain = nextDocument.querySelector('main');
                    const currentMain = document.querySelector('main');
                    if (!nextMain || !currentMain) throw new Error('live-main-missing');

                    currentMain.innerHTML = nextMain.innerHTML;
                    revision = nextRevision;
                    window.localizeDateTimes?.(currentMain);
                    window.bindAutoSearchForms?.(currentMain);
                    window.bindEnglishNumberWarnings?.(currentMain);
                    return true;
                } catch (error) {
                    showIndicator('تعذر التحديث المباشر مؤقتًا', true, 5000);
                    return false;
                } finally {
                    updating = false;
                }
            };

            const poll = async () => {
                if (document.hidden || updating) return;

                try {
                    const response = await fetch(endpoint, {
                        cache: 'no-store',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('live-status-failed');

                    const status = await response.json();
                    updateOrdersNotice(Number(status.unseen_count || 0));

                    if (status.revision === revision) return;

                    const hasNewOrder = Number(status.orders_count) > ordersCount;
                    const refreshed = await refreshVisibleContent(status.revision);
                    if (!refreshMain) revision = status.revision;
                    if (refreshed || !refreshMain) {
                        showIndicator(hasNewOrder ? 'وصل طلب جديد وتم تحديث الصفحة' : 'تم تحديث بيانات الطلبات');
                    }
                    ordersCount = Number(status.orders_count);
                } catch (error) {
                    // The next interval retries automatically without interrupting the current work.
                }
            };

            showIndicator('التحديث المباشر متصل', false, 2200);
            const timer = setInterval(poll, 2500);
            poll();
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) poll();
            });
            window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
        })();
    </script>
@endif
