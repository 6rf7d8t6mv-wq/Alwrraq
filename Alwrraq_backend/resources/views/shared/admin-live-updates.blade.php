@if (auth()->check() && auth()->user()->role === 'admin' && auth()->user()->hasAdminPermission('orders_view'))
    @php
        $adminLiveSnapshot = app(\App\Services\AdminLiveUpdateService::class)->snapshot();
        $adminLiveRefreshMain = $adminLiveRefreshMain ?? false;
    @endphp
    <script>
        (() => {
            const endpoint = @json(route('admin.live-status'));
            const refreshMain = @json((bool) $adminLiveRefreshMain);
            let revision = @json($adminLiveSnapshot['revision']);
            let updating = false;

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

                    await refreshVisibleContent(status.revision);
                    if (!refreshMain) revision = status.revision;
                } catch (error) {
                    // The next interval retries automatically without interrupting the current work.
                }
            };

            const timer = setInterval(poll, 5000);
            poll();
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) poll();
            });
            window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
        })();
    </script>
@endif
