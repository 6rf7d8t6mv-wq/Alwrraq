@php
    $currentLocale = session('ui_locale', 'ar');
    $nextLocale = $currentLocale === 'en' ? 'ar' : 'en';
@endphp

<style>
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
    @media (max-width: 820px) {
        body.customer-app-page { padding: 88px 0 0 !important; }
        .customer-app-page .header,
        .customer-app-page .page-header {
            position: fixed !important;
            top: 0;
            right: 0;
            left: 0;
            width: 100% !important;
            min-height: 0 !important;
            max-height: none !important;
            padding: 6px 8px !important;
            z-index: 100 !important;
        }
        .customer-app-page .header .header-inner,
        .customer-app-page .page-header .header-inner {
            height: auto !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            align-items: center !important;
            gap: 5px !important;
            direction: rtl !important;
        }
        .customer-app-page .header-brand {
            grid-column: 1;
            display: flex !important;
            align-items: center !important;
            justify-self: start;
            gap: 6px !important;
        }
        .customer-app-page .header-identity {
            grid-column: 2;
            justify-self: end;
            margin: 0 !important;
            gap: 0 !important;
            text-align: left;
        }
        .customer-app-page .header-identity strong {
            max-width: 150px;
            overflow: hidden;
            font-size: 10px;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .customer-app-page .header-identity small { font-size: 8px; line-height: 1.2; }
        .customer-app-page .header .brand-logo,
        .customer-app-page .page-header .brand-logo {
            width: 30px !important;
            height: 30px !important;
            margin: 0 !important;
            border-radius: 8px !important;
        }
        .customer-app-page .header .brand,
        .customer-app-page .page-header .brand {
            margin: 0 !important;
            font-size: 15px !important;
            line-height: 1.1 !important;
            white-space: nowrap !important;
        }
        .customer-app-page .brand-subtitle { display: none !important; }
        .customer-app-page .header-actions {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            margin: 0 !important;
            display: grid !important;
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
            gap: 3px !important;
            align-items: stretch !important;
        }
        .customer-app-page .header-user { display: none !important; }
        .customer-app-page .admin-header-link { display: none !important; }
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
            min-height: 27px !important;
            margin: 0 !important;
            padding: 4px 1px !important;
            justify-content: center !important;
            gap: 1px !important;
            border-radius: 6px !important;
            font-size: 7.5px !important;
            line-height: 1.1 !important;
            text-align: center !important;
            white-space: nowrap !important;
            overflow: hidden !important;
        }
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
