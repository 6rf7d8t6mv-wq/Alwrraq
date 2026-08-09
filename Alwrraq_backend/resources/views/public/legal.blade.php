@php
    $locale = session('ui_locale', 'ar') === 'en' ? 'en' : 'ar';
    $isEnglish = $locale === 'en';
    $document = config("legal.{$documentKey}");
    abort_unless(is_array($document), 404);
    $title = $document['title'][$locale];
    $description = $document['intro'][$locale][0];
    $homeUrl = route('public.home');
    $labels = $isEnglish ? [
        'home' => 'Home',
        'how' => 'How It Works',
        'services' => 'Services',
        'about' => 'About Us',
        'contact' => 'Contact Us',
        'login' => 'Sign In',
        'register' => 'Create Account',
        'announcementTitle' => 'Your stationery at your doorstep',
        'announcementText' => 'Choose your products, add them to your cart, and Alwrraq will deliver them to you.',
        'legalBadge' => 'Policies and legal information',
        'back' => 'Back to home',
        'privacy' => 'Privacy Policy',
        'terms' => 'Terms and Conditions',
        'refund' => 'Cancellation and Refund Policy',
        'rights' => 'All rights reserved.',
    ] : [
        'home' => 'الرئيسية',
        'how' => 'كيف يعمل',
        'services' => 'خدماتنا',
        'about' => 'من نحن',
        'contact' => 'تواصل معنا',
        'login' => 'تسجيل الدخول',
        'register' => 'إنشاء حساب',
        'announcementTitle' => 'قرطاسيتك في بيتك',
        'announcementText' => 'اختر منتجاتك، ضيفها بسلتك، والورّاق يوصلها لك.',
        'legalBadge' => 'السياسات والمعلومات القانونية',
        'back' => 'العودة للرئيسية',
        'privacy' => 'سياسة الخصوصية',
        'terms' => 'الشروط والأحكام',
        'refund' => 'سياسة الإلغاء والاسترجاع',
        'rights' => 'جميع الحقوق محفوظة.',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isEnglish ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('shared.tab-brand')
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0f172a">
    <link rel="canonical" href="{{ url($document['slug']) }}">
    <style>
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; margin: 0; overflow-x: hidden; font-family: Arial, Tahoma, sans-serif; background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 44%, #f7fafc 100%); color: #0f172a; line-height: 1.8; }
        a { color: inherit; }
        .container { width: min(1240px, calc(100% - 56px)); margin: 0 auto; }
        .stationery-announcement { position: relative; z-index: 21; background: linear-gradient(135deg, #0f4c81, #10233f); color: #ffffff; border-bottom: 1px solid rgba(147, 197, 253, 0.28); }
        .stationery-announcement-inner { min-height: 72px; display: flex; align-items: center; justify-content: center; gap: 11px; padding: 9px 0; text-align: center; line-height: 1.4; }
        .stationery-announcement-icon { width: 35px; height: 35px; flex: 0 0 auto; display: inline-grid; place-items: center; border-radius: 10px; background: rgba(255, 255, 255, 0.14); font-size: 19px; }
        .stationery-announcement-copy { display: grid; gap: 1px; }
        .stationery-announcement strong { display: block; color: #fef08a; font-size: 26px; font-weight: 1000; line-height: 1.25; }
        .stationery-announcement-text { display: block; color: #ffffff; font-size: 19px; font-weight: 900; }
        .site-header { position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.90); border-bottom: 1px solid rgba(203, 213, 225, 0.74); backdrop-filter: blur(16px); box-shadow: 0 10px 35px rgba(15, 23, 42, 0.05); }
        .nav { min-height: 70px; display: flex; align-items: center; justify-content: space-between; gap: 18px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; font-weight: 900; font-size: 24px; }
        .logo { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 16px; background: #ffffff; overflow: hidden; box-shadow: 0 14px 30px rgba(15, 76, 129, 0.18); border: 1px solid #dbe3ef; }
        .logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .nav-links { display: flex; align-items: center; gap: 7px; color: #334155; font-size: 14px; font-weight: 800; }
        .nav-links a { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; padding: 7px 12px; border: 1px solid #dbe3ef; border-radius: 9px; background: #ffffff; color: #334155; box-shadow: 0 5px 14px rgba(15, 23, 42, 0.05); text-decoration: none; transition: border-color .18s ease, background .18s ease, color .18s ease; }
        .nav-links a:hover { border-color: #60a5fa; background: #eff6ff; color: #0f4c81; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .btn { min-height: 43px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 12px; border: 1px solid #cbd5e1; background: #fff; text-decoration: none; font-weight: 900; font-size: 15px; transition: transform .18s ease; }
        .btn:hover { transform: translateY(-1px); }
        .btn.blue { background: linear-gradient(135deg, #0f4c81, #1d6fa5); border-color: #0f4c81; color: #fff; box-shadow: 0 14px 28px rgba(15, 76, 129, .18); }
        .legal-main { flex: 1 0 auto; padding: clamp(30px, 5vw, 66px) 0 54px; }
        .legal-shell { width: 100%; max-width: 960px; min-width: 0; margin: 0 auto; }
        .legal-hero { position: relative; width: 100%; min-width: 0; overflow: hidden; margin-bottom: 20px; padding: clamp(25px, 5vw, 46px); border: 1px solid rgba(147, 197, 253, .35); border-radius: 26px; background: radial-gradient(circle at 12% 12%, rgba(96, 165, 250, .25), transparent 30%), linear-gradient(135deg, #081426 0%, #102f52 55%, #0f4c81 100%); color: #fff; box-shadow: 0 26px 70px rgba(15, 23, 42, .18); }
        .legal-badge { display: inline-flex; margin-bottom: 12px; padding: 6px 12px; border: 1px solid rgba(255, 255, 255, .2); border-radius: 999px; background: rgba(255, 255, 255, .1); color: #bfdbfe; font-size: 13px; font-weight: 900; }
        .legal-hero h1 { margin: 0; color: #fff; font-size: clamp(27px, 5vw, 43px); line-height: 1.35; }
        .updated { margin: 10px 0 0; color: #fef08a; font-weight: 900; }
        .legal-intro { margin: 18px 0 0; color: #e0f2fe; font-size: 17px; }
        .legal-nav { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 9px; margin-bottom: 20px; }
        .legal-nav a { min-width: 0; padding: 11px 10px; border: 1px solid #dbe3ef; border-radius: 11px; background: #fff; color: #334155; box-shadow: 0 8px 22px rgba(15, 23, 42, .05); text-align: center; text-decoration: none; font-size: 13px; font-weight: 900; }
        .legal-nav a.active { border-color: #0f4c81; background: #0f4c81; color: #fff; }
        .legal-content { display: grid; gap: 13px; }
        .legal-section { width: 100%; min-width: 0; padding: clamp(18px, 3vw, 28px); border: 1px solid #e2e8f0; border-radius: 18px; background: rgba(255, 255, 255, .95); box-shadow: 0 14px 36px rgba(15, 23, 42, .055); overflow-wrap: anywhere; }
        .legal-section h2 { margin: 0 0 10px; color: #0f4c81; font-size: 20px; line-height: 1.5; }
        .legal-section p { margin: 8px 0 0; color: #334155; font-size: 15px; }
        .legal-section ul { margin: 10px 0 0; padding-inline-start: 24px; color: #334155; }
        .legal-section li + li { margin-top: 4px; }
        .legal-site-link { display: inline-flex; direction: ltr; margin-top: 11px; padding: 7px 12px; border-radius: 9px; background: #eff6ff; color: #0f4c81; font-weight: 900; text-decoration: none; }
        .back-home { display: inline-flex; align-items: center; gap: 7px; margin-top: 20px; padding: 10px 15px; border-radius: 11px; background: #10233f; color: #fff; text-decoration: none; font-weight: 900; }
        .site-footer { flex: 0 0 auto; margin-top: auto; padding: 26px 0; border-top: 1px solid #e2e8f0; background: #fff; color: #64748b; }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
        .footer-links { display: flex; gap: 14px; flex-wrap: wrap; }
        .footer-links a { color: #334155; text-decoration: none; font-weight: 800; }
        html[dir="ltr"] .nav { direction: ltr; }
        html[dir="ltr"] .legal-hero, html[dir="ltr"] .legal-section { text-align: left; }
        @media (min-width: 901px) {
            html[dir="ltr"] .nav { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; }
            html[dir="ltr"] .nav-links { grid-column: 1; grid-row: 1; justify-self: start; }
            html[dir="ltr"] .nav-actions { grid-column: 2; grid-row: 1; justify-self: center; }
            html[dir="ltr"] .brand { grid-column: 3; grid-row: 1; justify-self: end; }
        }
        @media (max-width: 900px) {
            .container { width: min(100% - 30px, 1120px); }
            .nav { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .nav-links, .nav-actions { width: 100%; flex-wrap: wrap; }
        }
        @media (max-width: 560px) {
            .container { width: min(100% - 22px, 1120px); }
            .stationery-announcement-inner { min-height: 59px; gap: 7px; padding: 6px 0; }
            .stationery-announcement-icon { width: 27px; height: 27px; border-radius: 8px; font-size: 14px; }
            .stationery-announcement strong { font-size: 19px; }
            .stationery-announcement-text { font-size: 14px; line-height: 1.35; }
            .nav { min-height: 0; display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 5px 8px; padding: 6px 0; }
            .brand { gap: 5px; font-size: 15px; white-space: nowrap; }
            .logo { width: 30px; height: 30px; border-radius: 8px; box-shadow: none; }
            .nav-actions { width: 100%; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3px; }
            .nav-actions .btn, .nav-actions .language-switcher-button { width: 100%; min-width: 0; min-height: 26px !important; margin: 0; padding: 3px 2px !important; border-radius: 6px; font-size: 8px; line-height: 1.15; text-align: center; white-space: nowrap; }
            .nav-actions .language-switcher-form { width: 100% !important; min-width: 0; margin: 0; }
            .nav-links { grid-column: 1 / -1; width: 100%; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 2px; font-size: 8.5px; line-height: 1.2; }
            .nav-links a { min-width: 0; min-height: 0; padding: 3px 1px; border-radius: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .legal-main { padding: 18px 0 28px; }
            .legal-hero { margin-bottom: 10px; padding: 17px 14px; border-radius: 15px; box-shadow: 0 13px 34px rgba(15, 23, 42, .14); }
            .legal-badge { margin-bottom: 7px; padding: 4px 8px; font-size: 9px; }
            .legal-hero h1 { font-size: 21px; }
            .updated { margin-top: 5px; font-size: 11px; }
            .legal-intro { margin-top: 10px; font-size: 11.5px; line-height: 1.7; }
            .legal-nav { gap: 4px; margin-bottom: 10px; }
            .legal-nav a { padding: 7px 4px; border-radius: 7px; font-size: 8px; line-height: 1.4; }
            .legal-content { gap: 7px; }
            .legal-section { padding: 12px; border-radius: 11px; box-shadow: 0 7px 18px rgba(15, 23, 42, .04); }
            .legal-section h2 { margin-bottom: 5px; font-size: 14px; }
            .legal-section p, .legal-section li { font-size: 10.5px; line-height: 1.75; }
            .legal-section ul { margin-top: 6px; padding-inline-start: 19px; }
            .legal-site-link { margin-top: 7px; padding: 4px 8px; border-radius: 6px; font-size: 10px; }
            .back-home { margin-top: 11px; padding: 7px 10px; border-radius: 7px; font-size: 10px; }
            .site-footer { padding: 10px 0; font-size: 9px; }
            .footer-inner, .footer-links { gap: 7px; }
            html[dir="ltr"] .nav { direction: ltr; }
        }
    </style>
</head>
<body>
    <div class="stationery-announcement" role="note" aria-label="{{ $labels['announcementTitle'] }}">
        <div class="container stationery-announcement-inner">
            <span class="stationery-announcement-icon" aria-hidden="true">✦</span>
            <span class="stationery-announcement-copy">
                <strong>{{ $labels['announcementTitle'] }}</strong>
                <span class="stationery-announcement-text">{{ $labels['announcementText'] }}</span>
            </span>
        </div>
    </div>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="{{ $homeUrl }}" aria-label="Alwrraq">
                <span class="logo"><img src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="Alwrraq" width="48" height="48" fetchpriority="high"></span>
                <span>{{ $isEnglish ? 'Alwrraq' : 'الورّاق' }}</span>
            </a>
            <nav class="nav-links" aria-label="{{ $labels['home'] }}">
                <a href="{{ $homeUrl }}#top">{{ $labels['home'] }}</a>
                <a href="{{ $homeUrl }}#how-it-works">{{ $labels['how'] }}</a>
                <a href="{{ $homeUrl }}#services">{{ $labels['services'] }}</a>
                <a href="{{ $homeUrl }}#about">{{ $labels['about'] }}</a>
                <a href="{{ $homeUrl }}#contact">{{ $labels['contact'] }}</a>
            </nav>
            <div class="nav-actions">
                <a class="btn" href="{{ route('login') }}">{{ $labels['login'] }}</a>
                <a class="btn blue" href="{{ route('login') }}#register">{{ $labels['register'] }}</a>
                @include('shared.language-switcher')
            </div>
        </div>
    </header>

    <main class="legal-main">
        <div class="container">
            <div class="legal-shell">
                <section class="legal-hero">
                    <span class="legal-badge">{{ $labels['legalBadge'] }}</span>
                    <h1>{{ $title }}</h1>
                    <p class="updated">{{ $document['updated'][$locale] }}</p>
                    @foreach ($document['intro'][$locale] as $paragraph)
                        <p class="legal-intro">{{ $paragraph }}</p>
                    @endforeach
                </section>

                <nav class="legal-nav" aria-label="{{ $labels['legalBadge'] }}">
                    <a class="{{ $documentKey === 'privacy' ? 'active' : '' }}" href="{{ route('public.privacy') }}">{{ $labels['privacy'] }}</a>
                    <a class="{{ $documentKey === 'terms' ? 'active' : '' }}" href="{{ route('public.terms') }}">{{ $labels['terms'] }}</a>
                    <a class="{{ $documentKey === 'refund' ? 'active' : '' }}" href="{{ route('public.refund') }}">{{ $labels['refund'] }}</a>
                </nav>

                <div class="legal-content">
                    @foreach ($document['sections'] as $section)
                        <section class="legal-section">
                            <h2>{{ $section['title'][$locale] }}</h2>
                            @foreach ($section['paragraphs'][$locale] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                            @if (!empty($section['bullets'][$locale]))
                                <ul>
                                    @foreach ($section['bullets'][$locale] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @foreach ($section['after'][$locale] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                            @if (!empty($section['link']))
                                <a class="legal-site-link" href="{{ $section['link'] }}">alwrraq.com</a>
                            @endif
                        </section>
                    @endforeach
                </div>

                <a class="back-home" href="{{ $homeUrl }}">{{ $labels['back'] }}</a>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div>© {{ date('Y') }} {{ $isEnglish ? 'Alwrraq.' : 'الورّاق.' }} {{ $labels['rights'] }}</div>
            <div class="footer-links">
                <a href="{{ route('public.privacy') }}">{{ $labels['privacy'] }}</a>
                <a href="{{ route('public.terms') }}">{{ $labels['terms'] }}</a>
                <a href="{{ route('public.refund') }}">{{ $labels['refund'] }}</a>
            </div>
        </div>
    </footer>
    @include('shared.language-tools')
    <script>document.title = @json($title);</script>
    @include('shared.guest-live-updates')
</body>
</html>
