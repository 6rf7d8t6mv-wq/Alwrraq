@php
    $currentLocale = session('ui_locale', 'ar');
    $nextLocale = $currentLocale === 'en' ? 'ar' : 'en';
@endphp

@if($currentLocale === 'en')
    <script>document.documentElement.classList.add('ui-auto-english')</script>
@endif

<style>
    html.ui-auto-english body { opacity: 0; }
    .language-switcher-form { margin: 0; width: 100%; }
    .language-switcher-button {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 10px 12px;
        border: 1px solid rgba(96, 165, 250, 0.45);
        border-radius: 10px;
        background: #ffffff;
        color: #0f172a;
        font-family: inherit;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.4;
        cursor: pointer;
        text-align: center;
    }
    .language-switcher-button:hover { background: #eff6ff; border-color: #60a5fa; }
    .nav-actions .language-switcher-form { width: auto; }
    .nav-actions .language-switcher-button { min-height: 42px; padding-inline: 14px; }
    .customer-menu-toggle,
    .customer-menu-backdrop,
    .customer-menu-close { display: none; }
    .customer-app-page .header-identity {
        display: grid;
        gap: 2px;
        margin-top: 10px;
        color: #ffffff;
    }
    .customer-app-page .header-identity strong { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; line-height: 1.3; }
    .customer-app-page .header-identity strong::before { content: '👤'; flex: 0 0 auto; font-size: 13px; line-height: 1; }
    .customer-app-page .header-identity small { color: #94a3b8; font-size: 10px; font-weight: 800; }
    @media (min-width: 821px) {
        html[dir="ltr"] body.customer-app-page {
            padding-right: var(--page-gap) !important;
            padding-left: calc(var(--sidebar-width) + var(--page-gap)) !important;
        }
        html[dir="ltr"] body.customer-app-page .header,
        html[dir="ltr"] body.customer-app-page .page-header {
            right: auto !important;
            left: 0 !important;
            box-shadow: 10px 0 30px rgba(15, 23, 42, 0.15) !important;
        }
    }
    @media (max-width: 820px) {
        body.customer-app-page { padding: 0 !important; }
        .customer-menu-backdrop {
            position: fixed;
            inset: 0;
            z-index: 198;
            display: block;
            background: rgba(15, 23, 42, 0.48);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
            backdrop-filter: blur(2px);
        }
        body.customer-menu-open { overflow: hidden; }
        body.customer-menu-open .customer-menu-backdrop { opacity: 1; visibility: visible; }
        .customer-menu-toggle {
            position: fixed;
            top: max(14px, env(safe-area-inset-top));
            right: 0;
            z-index: 201;
            width: 42px;
            min-height: 72px;
            display: grid;
            place-items: center;
            padding: 10px 7px;
            border: 1px solid rgba(255,255,255,.2);
            border-right: 0;
            border-radius: 14px 0 0 14px;
            background: linear-gradient(180deg, #0f4c81, #0f172a);
            color: #fff;
            box-shadow: -8px 12px 28px rgba(15,23,42,.28);
            font: inherit;
            cursor: pointer;
        }
        .customer-menu-toggle-icon { display: grid; gap: 4px; }
        .customer-menu-toggle-icon span { width: 20px; height: 2px; display: block; border-radius: 99px; background: #fff; }
        .customer-menu-toggle-label { font-size: 8px; font-weight: 900; }
        body.customer-menu-open .customer-menu-toggle { opacity: 0; pointer-events: none; }
        .customer-app-page .header,
        .customer-app-page .page-header {
            position: fixed !important;
            top: 0;
            right: 0 !important;
            left: auto !important;
            width: min(82vw, 300px) !important;
            min-height: 100vh !important;
            min-height: 100dvh !important;
            max-height: 100vh !important;
            max-height: 100dvh !important;
            overflow-y: auto !important;
            padding: max(22px, env(safe-area-inset-top)) 18px 22px !important;
            z-index: 200 !important;
            box-shadow: -18px 0 55px rgba(15,23,42,.32) !important;
            transform: translateX(105%);
            transition: transform .28s cubic-bezier(.22,.8,.24,1) !important;
        }
        body.customer-menu-open .header,
        body.customer-menu-open .page-header {
            transform: translateX(0);
        }
        .customer-menu-close {
            position: absolute;
            top: max(12px, env(safe-area-inset-top));
            left: 12px;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 0;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 10px;
            background: rgba(255,255,255,.08);
            color: #fff;
            font: 900 21px/1 Arial, sans-serif;
            cursor: pointer;
        }
        html[dir="ltr"] .customer-menu-close { right: 12px; left: auto; }
        .customer-app-page .header .header-inner,
        .customer-app-page .page-header .header-inner {
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0 !important;
            direction: rtl !important;
        }
        .customer-app-page .header-brand {
            display: block !important;
        }
        .customer-app-page .header-identity {
            margin-top: 16px !important;
            gap: 3px !important;
            text-align: right;
        }
        .customer-app-page .header-identity strong {
            max-width: 100%;
            overflow: hidden;
            font-size: 14px;
            line-height: 1.5;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .customer-app-page .header-identity small { font-size: 11px; line-height: 1.4; }
        .customer-app-page .header .brand-logo,
        .customer-app-page .page-header .brand-logo {
            width: 52px !important;
            height: 52px !important;
            margin: 0 0 10px !important;
            border-radius: 15px !important;
        }
        .customer-app-page .header .brand,
        .customer-app-page .page-header .brand {
            margin: 0 !important;
            font-size: 24px !important;
            line-height: 1.3 !important;
            white-space: nowrap !important;
        }
        .customer-app-page .brand-subtitle { display: block !important; margin-top: 4px !important; }
        .customer-app-page .header-actions {
            width: 100% !important;
            margin: 24px 0 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            align-items: stretch !important;
        }
        .customer-app-page .header-user { display: none !important; }
        .customer-app-page .header-actions > a,
        .customer-app-page .header-actions > .header-form,
        .customer-app-page .header-actions > .language-switcher-form {
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
        }
        .customer-app-page .header-actions .header-link,
        .customer-app-page .header-actions .home-button,
        .customer-app-page .header-actions .settings-button,
        .customer-app-page .header-actions .logout-button,
        .customer-app-page .header-actions .language-switcher-button {
            width: 100% !important;
            min-width: 0 !important;
            min-height: 44px !important;
            margin: 0 !important;
            padding: 11px 12px !important;
            justify-content: flex-start !important;
            gap: 8px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            line-height: 1.5 !important;
            text-align: right !important;
            white-space: normal !important;
        }
        .customer-app-page .header-actions .language-switcher-button { justify-content: center !important; }
        html[dir="ltr"] body.customer-app-page .header .header-inner,
        html[dir="ltr"] body.customer-app-page .page-header .header-inner,
        html[dir="ltr"] body.customer-app-page .header-brand,
        html[dir="ltr"] body.customer-app-page .header-identity,
        html[dir="ltr"] body.customer-app-page .header-actions,
        html[dir="ltr"] body.customer-app-page .header-actions > * {
            direction: ltr !important;
        }
        html[dir="ltr"] body.customer-app-page .header-identity {
            text-align: left !important;
        }
        html[dir="ltr"] body.customer-app-page .header-actions .header-link,
        html[dir="ltr"] body.customer-app-page .header-actions .home-button,
        html[dir="ltr"] body.customer-app-page .header-actions .settings-button,
        html[dir="ltr"] body.customer-app-page .header-actions .logout-button,
        html[dir="ltr"] body.customer-app-page .header-actions .language-switcher-button {
            direction: ltr !important;
        }
        html[dir="ltr"] .customer-menu-toggle { right: auto; left: 0; border-right: 1px solid rgba(255,255,255,.2); border-left: 0; border-radius: 0 14px 14px 0; }
        html[dir="ltr"] body.customer-app-page .header,
        html[dir="ltr"] body.customer-app-page .page-header { right: auto !important; left: 0 !important; transform: translateX(-105%); }
        html[dir="ltr"] body.customer-menu-open .header,
        html[dir="ltr"] body.customer-menu-open .page-header { transform: translateX(0); }
    }
    @media (min-width: 1100px) {
        .customer-app-page .header-identity strong { font-size: 15px !important; }
        .customer-app-page .header-identity small { font-size: 12px !important; }
        .customer-app-page .header-actions .header-link,
        .customer-app-page .header-actions .home-button,
        .customer-app-page .header-actions .settings-button,
        .customer-app-page .header-actions .logout-button,
        .customer-app-page .header-actions .language-switcher-button { font-size: 14px !important; }
    }
</style>

<form class="language-switcher-form" method="post" action="{{ route('language.switch') }}">
    @csrf
    <input type="hidden" name="locale" value="{{ $nextLocale }}">
    <button class="language-switcher-button" type="submit">
        <span aria-hidden="true">🌐</span>
        <span>{{ $currentLocale === 'en' ? 'Arabic' : 'English' }}</span>
    </button>
</form>
<script>
    document.currentScript.previousElementSibling?.addEventListener('submit', () => {
        document.documentElement.classList.add('ui-auto-english');
    });

    if (document.body.classList.contains('customer-app-page') && !document.querySelector('.customer-menu-toggle')) {
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'customer-menu-toggle';
        toggle.setAttribute('aria-label', 'فتح القائمة');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '<span class="customer-menu-toggle-icon" aria-hidden="true"><span></span><span></span><span></span></span><span class="customer-menu-toggle-label">القائمة</span>';

        const backdrop = document.createElement('div');
        backdrop.className = 'customer-menu-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');

        const setMenuOpen = (open) => {
            document.body.classList.toggle('customer-menu-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
        };

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'customer-menu-close';
        close.setAttribute('aria-label', 'إغلاق القائمة');
        close.innerHTML = '&times;';

        toggle.addEventListener('click', () => setMenuOpen(true));
        close.addEventListener('click', () => setMenuOpen(false));
        backdrop.addEventListener('click', () => setMenuOpen(false));
        const customerHeader = document.querySelector('.customer-app-page .header, .customer-app-page .page-header');
        customerHeader?.querySelector('.header-inner')?.prepend(close);
        customerHeader?.addEventListener('click', (event) => {
                if (event.target.closest('a, button[type="submit"]')) setMenuOpen(false);
            });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setMenuOpen(false);
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth > 820) setMenuOpen(false);
        });

        document.body.append(backdrop, toggle);
    }
</script>
