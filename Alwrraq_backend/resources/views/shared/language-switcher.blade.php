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

</script>
