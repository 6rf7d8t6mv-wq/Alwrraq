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
</style>

<form class="language-switcher-form" method="post" action="{{ route('language.switch') }}">
    @csrf
    <input type="hidden" name="locale" value="{{ $nextLocale }}">
    <button class="language-switcher-button" type="submit">
        <span aria-hidden="true">🌐</span>
        <span>{{ $currentLocale === 'en' ? 'Arabic' : 'English' }}</span>
    </button>
</form>
