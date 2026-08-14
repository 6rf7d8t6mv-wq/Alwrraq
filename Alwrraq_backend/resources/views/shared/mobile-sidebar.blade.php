<style>
    .mobile-sidebar-toggle,
    .mobile-sidebar-backdrop { display: none; }

    @media (max-width: 980px) {
        body.mobile-sidebar-enabled { --mobile-sidebar-width: min(300px, 86vw); }
        body.mobile-sidebar-enabled.mobile-sidebar-open { overflow: hidden; }
        body.mobile-sidebar-enabled .layout { padding-top: 0 !important; }

        body.mobile-sidebar-enabled .mobile-sidebar-panel {
            position: fixed !important;
            inset: 0 0 0 auto !important;
            z-index: 1002 !important;
            display: block !important;
            width: var(--mobile-sidebar-width) !important;
            min-width: 0 !important;
            height: 100vh !important;
            height: 100dvh !important;
            min-height: 100vh !important;
            min-height: 100dvh !important;
            max-height: 100vh !important;
            max-height: 100dvh !important;
            padding: max(18px, env(safe-area-inset-top)) 16px max(18px, env(safe-area-inset-bottom)) !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            background: #0f172a !important;
            color: #f8fafc !important;
            box-shadow: -18px 0 48px rgba(15, 23, 42, .28) !important;
            direction: rtl !important;
            transform: translateX(105%);
            transition: transform .24s ease;
            overscroll-behavior: contain;
        }

        body.mobile-sidebar-enabled .mobile-sidebar-panel.mobile-sidebar-panel-open { transform: translateX(0); }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .header-inner {
            display: flex !important;
            height: auto !important;
            min-height: 100% !important;
            flex-direction: column !important;
            align-items: stretch !important;
            justify-content: flex-start !important;
            gap: 0 !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .header-brand,
        body.mobile-sidebar-enabled .mobile-sidebar-panel .admin-header-brand {
            display: block !important;
            margin: 0 !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .brand-logo {
            display: block !important;
            width: 46px !important;
            height: 46px !important;
            margin: 0 0 10px !important;
            border-radius: 14px !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .brand {
            margin: 0 0 4px !important;
            font-size: 23px !important;
            line-height: 1.3 !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .brand-subtitle { display: block !important; }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .header-identity,
        body.mobile-sidebar-enabled .mobile-sidebar-panel .admin-name {
            display: grid !important;
            margin: 16px 0 0 !important;
            text-align: right !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .admin-name strong,
        body.mobile-sidebar-enabled .mobile-sidebar-panel .admin-name small {
            max-width: none !important;
            font-size: 13px !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .header-actions,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav {
            display: flex !important;
            width: 100% !important;
            margin-top: 22px !important;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 9px !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel .header-user {
            display: block !important;
            margin: 0 0 12px !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav > a,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav > form,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav > .language-switcher-form {
            flex: 0 0 auto !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav a,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .logout,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .language-switcher-button {
            display: flex !important;
            width: 100% !important;
            min-height: 42px !important;
            padding: 9px 10px !important;
            flex-direction: row !important;
            justify-content: flex-start !important;
            gap: 8px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            line-height: 1.45 !important;
            text-align: right !important;
            white-space: normal !important;
            overflow: visible !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .nav-icon {
            display: inline-flex !important;
            width: 26px !important;
            height: 26px !important;
            flex: 0 0 26px !important;
            border-radius: 8px !important;
            background: rgba(255, 255, 255, .10) !important;
            font-size: 14px !important;
        }
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .nav-text,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .settings-link .nav-text,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .logout .nav-text,
        body.mobile-sidebar-enabled .mobile-sidebar-panel nav .language-switcher-button span:last-child {
            display: inline !important;
            width: auto !important;
            overflow: visible !important;
            font-size: inherit !important;
            white-space: normal !important;
        }

        .mobile-sidebar-toggle {
            position: fixed;
            top: max(10px, env(safe-area-inset-top));
            right: 0;
            z-index: 1004;
            display: inline-flex;
            width: 44px;
            height: 48px;
            margin: 0;
            padding: 0;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .28);
            border-inline-end: 0;
            border-radius: 12px 0 0 12px;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .24);
            font: inherit;
            cursor: pointer;
            transition: right .24s ease, background .18s ease;
        }
        .mobile-sidebar-toggle-icon { font-size: 23px; line-height: 1; }
        body.mobile-sidebar-open .mobile-sidebar-toggle {
            right: var(--mobile-sidebar-width);
            background: #b91c1c;
        }
        .mobile-sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1001;
            background: rgba(15, 23, 42, .52);
            backdrop-filter: blur(2px);
        }
        body.mobile-sidebar-open .mobile-sidebar-backdrop { display: block; }
    }

    @media (prefers-reduced-motion: reduce) {
        body.mobile-sidebar-enabled .mobile-sidebar-panel,
        .mobile-sidebar-toggle { transition: none !important; }
    }
</style>

<button class="mobile-sidebar-toggle" type="button" aria-label="فتح القائمة" aria-expanded="false">
    <span class="mobile-sidebar-toggle-icon" aria-hidden="true">☰</span>
</button>
<div class="mobile-sidebar-backdrop" aria-hidden="true"></div>

<script>
    (() => {
        if (window.__alwrraqMobileSidebarStarted) return;
        window.__alwrraqMobileSidebarStarted = true;

        const panel = document.querySelector('body > .header, body > .page-header, .layout > aside');
        const toggle = document.querySelector('.mobile-sidebar-toggle');
        const backdrop = document.querySelector('.mobile-sidebar-backdrop');
        if (!panel || !toggle || !backdrop) return;

        const icon = toggle.querySelector('.mobile-sidebar-toggle-icon');
        panel.id ||= 'alwrraqSidebar';
        toggle.setAttribute('aria-controls', panel.id);
        panel.classList.add('mobile-sidebar-panel');
        document.body.classList.add('mobile-sidebar-enabled');

        const setOpen = (open) => {
            document.body.classList.toggle('mobile-sidebar-open', open);
            panel.classList.toggle('mobile-sidebar-panel-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
            icon.textContent = open ? '×' : '☰';
        };

        toggle.addEventListener('click', () => setOpen(!panel.classList.contains('mobile-sidebar-panel-open')));
        backdrop.addEventListener('click', () => setOpen(false));
        panel.addEventListener('click', (event) => {
            if (event.target.closest('a')) setOpen(false);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setOpen(false);
        });
        window.addEventListener('popstate', () => setOpen(false));
        window.matchMedia('(min-width: 981px)').addEventListener('change', (event) => {
            if (event.matches) setOpen(false);
        });
    })();
</script>
