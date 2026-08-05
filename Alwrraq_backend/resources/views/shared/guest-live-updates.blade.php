@guest
    <script>
        (() => {
            if (window.__alwrraqGuestUpdatesStarted) return;
            window.__alwrraqGuestUpdatesStarted = true;

            const endpoint = @json(route('app.revision'));
            let revision = @json(app(\App\Services\LivePageUpdateService::class)->applicationRevision());
            let checking = false;
            let userIsEditing = false;

            const checkForUpdate = async () => {
                if (document.hidden || checking) return;
                checking = true;

                try {
                    const response = await fetch(endpoint, {
                        cache: 'no-store',
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) return;

                    const status = await response.json();
                    if (!status.revision || status.revision === revision || userIsEditing) return;

                    revision = status.revision;
                    const freshUrl = new URL(window.location.href);
                    freshUrl.searchParams.set('_app_revision', status.revision.slice(0, 16));
                    window.location.replace(freshUrl.toString());
                } catch (_) {
                    // The next poll retries automatically.
                } finally {
                    checking = false;
                }
            };

            const timer = window.setInterval(checkForUpdate, 30000);
            document.addEventListener('input', (event) => {
                if (event.isTrusted) userIsEditing = true;
            }, true);
            document.addEventListener('change', (event) => {
                if (event.isTrusted) userIsEditing = true;
            }, true);
            checkForUpdate();
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) checkForUpdate();
            });
            window.addEventListener('pagehide', () => window.clearInterval(timer), { once: true });
        })();
    </script>
@endguest
