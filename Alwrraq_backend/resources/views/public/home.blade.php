@php
    $siteUrl = rtrim(config('app.url') ?: url('/'), '/');
    $pageUrl = $siteUrl . '/';
    $isEnglish = session('ui_locale', 'ar') === 'en';
    $pageTitle = $isEnglish
        ? 'Alwrraq | Printing, Binding, and Stationery Across Saudi Arabia'
        : 'الورّاق | طباعة وتجليد الرسائل والكتب والقرطاسية في السعودية';
    $pageDescription = $isEnglish
        ? 'Print and bind theses, books, and notes, upload PDF and Word files, and shop stationery with delivery across Saudi Arabia. Alwrraq branch is in Madinah.'
        : 'طباعة وتجليد رسائل الماجستير والدكتوراه والكتب والمذكرات، ورفع ملفات PDF وWord وشراء القرطاسية مع التوصيل لجميع مناطق السعودية. فرعنا في المدينة المنورة.';
    $logoUrl = $siteUrl . '/images/alwrraq-logo.jpeg';
    $socialImageUrl = $siteUrl . route('public.showcase-image', ['device' => 'desktop'], false);
    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Store',
                '@id' => $siteUrl . '/#organization',
                'name' => 'الورّاق',
                'alternateName' => ['Alwrraq', 'الوراق'],
                'legalName' => 'شركة مسير المدينة المحدودة',
                'url' => $pageUrl,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logoUrl,
                ],
                'image' => $socialImageUrl,
                'telephone' => '+966542440582',
                'taxID' => '314417169600003',
                'currenciesAccepted' => 'SAR',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'المدينة المنورة',
                    'addressRegion' => 'المدينة المنورة',
                    'addressCountry' => 'SA',
                ],
                'areaServed' => [
                    '@type' => 'Country',
                    'name' => 'المملكة العربية السعودية',
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => '+966542440582',
                    'contactType' => 'customer service',
                    'areaServed' => 'SA',
                    'availableLanguage' => ['ar', 'en'],
                ],
                'hasOfferCatalog' => [
                    '@type' => 'OfferCatalog',
                    'name' => 'خدمات الورّاق',
                    'itemListElement' => collect([
                        'طباعة وتجليد رسائل الماجستير',
                        'طباعة وتجليد رسائل الدكتوراه',
                        'طباعة الكتب والمذكرات والملفات',
                        'تنسيق الرسائل العلمية',
                        'إنشاء البحوث',
                        'منتجات القرطاسية',
                    ])->map(fn ($name) => [
                        '@type' => 'OfferCatalog',
                        'name' => $name,
                    ])->all(),
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '/#website',
                'url' => $pageUrl,
                'name' => 'الورّاق',
                'alternateName' => 'Alwrraq',
                'inLanguage' => 'ar-SA',
                'publisher' => ['@id' => $siteUrl . '/#organization'],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $pageUrl . '#webpage',
                'url' => $pageUrl,
                'name' => $pageTitle,
                'description' => $pageDescription,
                'inLanguage' => 'ar-SA',
                'isPartOf' => ['@id' => $siteUrl . '/#website'],
                'about' => ['@id' => $siteUrl . '/#organization'],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    'url' => $socialImageUrl,
                    'width' => 2879,
                    'height' => 1625,
                ],
            ],
        ],
    ];
    $loginUrl = route('login');
    $registerUrl = route('login') . '#register';
@endphp
<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#0f172a">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <link rel="canonical" href="{{ $pageUrl }}">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">
    <link rel="icon" type="image/jpeg" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">
    <link rel="preload" as="image" href="{{ $logoUrl }}" fetchpriority="high">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ $isEnglish ? 'en_US' : 'ar_SA' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:site_name" content="الورّاق">
    <meta property="og:image" content="{{ $socialImageUrl }}">
    <meta property="og:image:secure_url" content="{{ $socialImageUrl }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="2879">
    <meta property="og:image:height" content="1625">
    <meta property="og:image:alt" content="واجهة منصة الورّاق على الكمبيوتر">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $socialImageUrl }}">
    <meta name="twitter:image:alt" content="واجهة منصة الورّاق على الكمبيوتر">
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    <style>
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 92px; }
        body { min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; margin: 0; font-family: Arial, Tahoma, sans-serif; background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 44%, #f7fafc 100%); color: #0f172a; line-height: 1.8; }
        body > main { flex: 1 0 auto; }
        a { color: inherit; }
        .container { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .stationery-announcement { position: relative; z-index: 21; background: linear-gradient(135deg, #0f4c81, #10233f); color: #ffffff; border-bottom: 1px solid rgba(147, 197, 253, 0.28); }
        .stationery-announcement-inner { min-height: 72px; display: flex; align-items: center; justify-content: center; gap: 11px; padding: 9px 0; text-align: center; line-height: 1.4; }
        .stationery-announcement-icon { width: 35px; height: 35px; flex: 0 0 auto; display: inline-grid; place-items: center; border-radius: 10px; background: rgba(255, 255, 255, 0.14); font-size: 19px; }
        .stationery-announcement-copy { display: grid; gap: 1px; }
        .stationery-announcement strong { display: block; color: #fef08a; font-size: 26px; font-weight: 1000; line-height: 1.25; }
        .stationery-announcement-text { display: block; color: #ffffff; font-size: 19px; font-weight: 900; }
        .site-header { position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.90); border-bottom: 1px solid rgba(203, 213, 225, 0.74); backdrop-filter: blur(16px); box-shadow: 0 10px 35px rgba(15, 23, 42, 0.05); }
        .nav { min-height: 74px; display: flex; align-items: center; justify-content: space-between; gap: 18px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; font-weight: 900; font-size: 22px; }
        .logo { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 16px; background: #ffffff; overflow: hidden; box-shadow: 0 14px 30px rgba(15, 76, 129, 0.18); border: 1px solid #dbe3ef; }
        .logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .nav-links { display: flex; align-items: center; gap: 18px; color: #334155; font-size: 14px; font-weight: 800; }
        .nav-links a { text-decoration: none; padding: 8px 0; border-bottom: 2px solid transparent; transition: color 0.18s ease, border-color 0.18s ease; }
        .nav-links a:hover { color: #0f4c81; border-color: #6ea8d8; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 16px; border-radius: 12px; border: 1px solid #cbd5e1; text-decoration: none; font-weight: 900; font-size: 14px; transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease; }
        .btn:hover { transform: translateY(-1px); }
        .btn.primary { background: #10233f; border-color: #10233f; color: #ffffff; box-shadow: 0 14px 28px rgba(15, 23, 42, 0.14); }
        .btn.blue { background: linear-gradient(135deg, #0f4c81, #1d6fa5); border-color: #0f4c81; color: #ffffff; box-shadow: 0 14px 28px rgba(15, 76, 129, 0.18); }
        .btn.light { background: #ffffff; color: #0f172a; }
        .hero { padding: clamp(64px, 8vw, 104px) 0 46px; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr); gap: 28px; align-items: center; }
        .eyebrow { display: inline-flex; padding: 7px 13px; border-radius: 999px; background: #dbeafe; color: #0f4c81; font-size: 13px; font-weight: 900; margin-bottom: 16px; border: 1px solid #bfdbfe; }
        h1 { margin: 0; font-size: clamp(34px, 6vw, 56px); line-height: 1.25; letter-spacing: 0; }
        .hero-title { width: 100%; max-width: 680px; text-align: center; }
        .hero-title-line { display: block; width: 100%; white-space: nowrap; }
        .hero p { max-width: 620px; margin: 18px 0 0; color: #475569; font-size: clamp(16px, 2vw, 19px); }
        .hero-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 28px; max-width: 620px; }
        .stat { background: rgba(255, 255, 255, 0.82); border: 1px solid #dbe3ef; border-radius: 16px; padding: 14px; box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06); }
        .stat strong { display: block; font-size: 20px; color: #0f4c81; line-height: 1.2; }
        .stat span { display: block; color: #64748b; font-size: 13px; margin-top: 4px; font-weight: 800; }
        .hero-card { background: rgba(255, 255, 255, 0.92); border: 1px solid #dbe3ef; border-radius: 22px; padding: clamp(20px, 4vw, 30px); box-shadow: 0 26px 80px rgba(15, 23, 42, 0.11); position: relative; overflow: hidden; }
        .hero-card::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 6px; background: linear-gradient(90deg, #0f4c81, #60a5fa, #10233f); }
        .hero-card h2 { margin: 0 0 14px; font-size: 22px; }
        .hero-list { display: grid; gap: 12px; margin: 0; padding: 0; list-style: none; }
        .hero-list li { display: flex; gap: 10px; padding: 13px; border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 800; color: #334155; }
        .check { width: 24px; height: 24px; display: inline-grid; place-items: center; flex: 0 0 auto; border-radius: 999px; background: #dcfce7; color: #166534; font-size: 14px; }
        section { padding: 48px 0; }
        .about-box { display: grid; grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr); gap: 22px; align-items: stretch; background: rgba(255, 255, 255, 0.94); border: 1px solid #dbe3ef; border-radius: 24px; padding: clamp(22px, 4vw, 34px); box-shadow: 0 22px 58px rgba(15, 23, 42, 0.08); }
        .about-badge { display: inline-flex; width: fit-content; padding: 7px 13px; border-radius: 999px; background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; font-size: 13px; font-weight: 900; margin-bottom: 12px; }
        .about-box h2 { margin: 0 0 12px; font-size: clamp(26px, 4vw, 34px); line-height: 1.35; }
        .about-box p { margin: 0; color: #475569; font-size: 16px; }
        .about-panel { display: grid; gap: 12px; align-content: center; }
        .about-point { padding: 14px 16px; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .about-point strong { display: block; color: #0f4c81; font-size: 15px; margin-bottom: 4px; }
        .about-point span { color: #64748b; font-size: 14px; }
        .showcase { padding-top: 18px; }
        .showcase-stage { position: relative; display: grid; grid-template-columns: minmax(0, 0.9fr) minmax(340px, 1.1fr); gap: clamp(22px, 4vw, 44px); align-items: center; overflow: hidden; border-radius: 30px; padding: clamp(24px, 5vw, 46px); background: radial-gradient(circle at 18% 24%, rgba(96, 165, 250, 0.28), transparent 28%), linear-gradient(135deg, #10233f 0%, #0f4c81 48%, #17324f 100%); box-shadow: 0 30px 90px rgba(15, 23, 42, 0.18); }
        .showcase-stage::after { content: ""; position: absolute; inset: auto 8% -70px 8%; height: 160px; border-radius: 999px; background: rgba(255, 255, 255, 0.18); filter: blur(30px); }
        .showcase-copy { position: relative; z-index: 2; max-width: 520px; color: #ffffff; }
        .showcase-copy .eyebrow { background: rgba(255, 255, 255, 0.14); color: #ffffff; border-color: rgba(255, 255, 255, 0.22); }
        .showcase-copy h2 { margin: 0 0 12px; font-size: clamp(28px, 4vw, 42px); line-height: 1.35; }
        .showcase-copy p { margin: 0; color: #dbeafe; font-size: 16px; }
        .devices { position: relative; width: 100%; height: clamp(500px, 45vw, 575px); z-index: 1; }
        .device { position: absolute; margin: 0; overflow: hidden; background: transparent; filter: drop-shadow(0 28px 34px rgba(2, 6, 23, 0.3)); }
        .device-desktop { width: min(88%, 550px); aspect-ratio: 2879 / 1625; left: 0; top: 172px; border: 4px solid rgba(255,255,255,.76); border-radius: 18px; transform: rotate(-3deg); }
        .device-phone { width: min(35%, 220px); aspect-ratio: 702 / 1462; right: 0; top: 8px; border-radius: 30px; transform: rotate(3deg); }
        .showcase-preview { display: block; width: 100%; height: 100%; object-fit: contain; }
        .screen { width: 100%; height: 100%; background: #f8fafc; overflow: hidden; color: #0f172a; }
        .mini-header { height: 62px; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; background: linear-gradient(135deg, #0f172a, #0f4c81); color: #ffffff; }
        .mini-brand-wrap { display: inline-flex; align-items: center; gap: 8px; }
        .mini-logo { width: 34px; height: 34px; border-radius: 10px; object-fit: cover; background: #ffffff; border: 1px solid rgba(255,255,255,0.22); }
        .mini-brand { font-weight: 900; line-height: 1.2; }
        .mini-brand small { display: block; color: #bfdbfe; font-size: 10px; font-weight: 800; }
        .mini-user { padding: 6px 10px; border-radius: 999px; background: rgba(255, 255, 255, 0.14); font-size: 11px; font-weight: 900; }
        .mini-body { padding: 14px; }
        .mini-title { margin: 0 0 10px; font-size: 18px; text-align: right; }
        .mini-service-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
        .mini-service { padding: 11px; border-radius: 14px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); font-size: 12px; font-weight: 900; }
        .mini-service span { display: block; color: #64748b; font-size: 10px; font-weight: 800; margin-top: 4px; }
        .mini-order { margin-top: 12px; padding: 12px; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0; }
        .mini-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 0; border-bottom: 1px solid #eef2f7; font-size: 11px; font-weight: 800; }
        .mini-row:last-child { border-bottom: 0; }
        .pill { display: inline-flex; padding: 4px 8px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: 10px; font-weight: 900; }
        .upload-panel { display: grid; gap: 9px; }
        .upload-drop { padding: 14px; border: 1px dashed #93c5fd; border-radius: 16px; background: #eff6ff; text-align: center; color: #0f4c81; font-size: 12px; font-weight: 900; }
        .upload-file { padding: 11px; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
        .upload-file strong { display: block; font-size: 12px; color: #0f172a; margin-bottom: 7px; }
        .upload-meta { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; }
        .upload-meta span { padding: 6px; border-radius: 10px; background: #f8fafc; color: #475569; font-size: 9px; font-weight: 900; text-align: center; }
        .upload-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
        .upload-option { padding: 8px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px; font-weight: 900; color: #334155; }
        .upload-total { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 14px; background: #10233f; color: #ffffff; font-size: 11px; font-weight: 900; }
        .phone-home-title { margin: 0 0 8px; text-align: center; font-size: 15px; }
        .phone-home-grid { display: grid; gap: 6px; }
        .phone-home-service { padding: 7px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05); }
        .phone-home-service strong { display: block; color: #0f172a; font-size: 9px; line-height: 1.45; }
        .phone-home-service span { display: block; color: #64748b; font-size: 8px; font-weight: 800; margin-top: 1px; }
        .phone-home-service .enter { display: block; width: 100%; margin-top: 5px; padding: 4px 6px; border-radius: 8px; background: #0f4c81; color: #ffffff; text-align: center; font-size: 8px; font-weight: 900; }
        .phone-screen { padding-top: 22px; }
        .phone-screen .mini-header { height: 72px; align-items: end; justify-content: center; text-align: center; padding-bottom: 12px; }
        .phone-screen .mini-body { padding: 10px; }
        .login-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 14px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
        .login-card h3 { margin: 0 0 12px; text-align: center; font-size: 18px; }
        .fake-input { height: 36px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; margin-bottom: 9px; }
        .fake-button { height: 36px; border-radius: 10px; background: #0f4c81; color: #ffffff; display: grid; place-items: center; font-size: 12px; font-weight: 900; margin-top: 10px; }
        .fake-button.green { background: #16a34a; }
        .phone-services { display: grid; gap: 8px; margin-top: 12px; }
        .phone-service { padding: 10px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 900; }
        .section-head { margin-bottom: 22px; display: flex; align-items: end; justify-content: space-between; gap: 18px; }
        .section-head h2 { margin: 0 0 8px; font-size: clamp(26px, 4vw, 34px); }
        .section-head p { margin: 0; color: #64748b; max-width: 560px; }
        .cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .card { background: rgba(255, 255, 255, 0.94); border: 1px solid #e2e8f0; border-radius: 18px; padding: 20px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07); transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease; }
        .card:hover { transform: translateY(-3px); border-color: #bfdbfe; box-shadow: 0 22px 48px rgba(15, 76, 129, 0.12); }
        .card-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 14px; background: #eff6ff; color: #0f4c81; font-size: 18px; margin-bottom: 12px; font-weight: 900; }
        .card h3 { margin: 0; font-size: 18px; }
        .card p { margin: 0; color: #64748b; font-size: 14px; }
        .steps { counter-reset: step; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
        .step { position: relative; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 18px 14px; min-height: 150px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05); }
        .step::before { counter-increment: step; content: counter(step); width: 34px; height: 34px; display: grid; place-items: center; border-radius: 12px; background: #0f4c81; color: #ffffff; font-weight: 900; margin-bottom: 12px; }
        .step h3 { margin: 0 0 6px; font-size: 16px; }
        .step p { margin: 0; color: #64748b; font-size: 13px; }
        .features { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .feature { padding: 18px; border-radius: 18px; background: linear-gradient(145deg, #10233f, #0f4c81); color: #ffffff; box-shadow: 0 18px 42px rgba(15, 76, 129, 0.16); }
        .feature h3 { margin: 0; font-size: 16px; color: #ffffff; }
        .feature p { margin: 0; color: #dbeafe; font-size: 13px; }
        .contact { background: linear-gradient(135deg, #ffffff, #f0f7ff); border: 1px solid #dbe3ef; border-radius: 22px; padding: clamp(22px, 4vw, 34px); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: center; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08); }
        .contact h2 { margin: 0 0 8px; }
        .contact p { margin: 0; color: #64748b; }
        .contact-phone { display: inline-flex; align-items: center; gap: 7px; margin-top: 9px; padding: 6px 10px; border-radius: 9px; background: #ffffff; border: 1px solid #dbe3ef; color: #0f172a; font-size: 15px; font-weight: 900; text-decoration: none; direction: ltr; }
        .contact-actions { display: grid; grid-template-columns: repeat(3, minmax(100px, 1fr)); gap: 7px; }
        .contact-channel { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 8px 12px; border-radius: 9px; color: #ffffff; font-size: 13px; font-weight: 900; text-decoration: none; white-space: nowrap; }
        .contact-channel-icon { width: 18px; height: 18px; flex: 0 0 auto; display: block; fill: currentColor; }
        .contact-channel.call { background: #0f4c81; }
        .contact-channel.whatsapp { background: #16a34a; }
        .contact-channel.telegram { background: #0284c7; }
        .site-footer { flex: 0 0 auto; margin-top: auto; padding: 26px 0; border-top: 1px solid #e2e8f0; background: #ffffff; color: #64748b; }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
        .footer-links { display: flex; gap: 14px; flex-wrap: wrap; }
        .footer-links a { color: #334155; text-decoration: none; font-weight: 800; }
        @media (max-width: 900px) {
            .nav { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .nav-links, .nav-actions { width: 100%; flex-wrap: wrap; }
            .hero-grid, .about-box, .contact { grid-template-columns: 1fr; }
            .showcase-stage { grid-template-columns: 1fr; }
            .devices { height: 575px; max-width: 680px; margin: 0 auto; }
            .device-desktop { left: 0; top: 190px; width: min(86%, 520px); }
            .device-phone { right: 0; top: 0; width: min(36%, 220px); }
            .section-head { display: block; }
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .steps, .features { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            html { scroll-padding-top: 78px; }
            .container { width: min(100% - 22px, 1120px); }
            .stationery-announcement-inner { min-height: 59px; gap: 7px; padding: 6px 0; }
            .stationery-announcement-icon { width: 27px; height: 27px; border-radius: 8px; font-size: 14px; }
            .stationery-announcement strong { font-size: 19px; font-weight: 1000; }
            .stationery-announcement-text { font-size: 14px; line-height: 1.35; }
            .site-header { box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07); }
            .nav { min-height: 0; display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 5px 8px; padding: 6px 0; }
            .brand { gap: 5px; font-size: 15px; white-space: nowrap; }
            .logo { width: 30px; height: 30px; border-radius: 8px; box-shadow: none; }
            .nav-actions { width: 100%; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3px; }
            .nav-actions .btn,
            .nav-actions .language-switcher-button { width: 100%; min-width: 0; min-height: 26px !important; margin: 0; padding: 3px 2px !important; border-radius: 6px; font-size: 8px; line-height: 1.15; text-align: center; white-space: nowrap; }
            .nav-actions .language-switcher-form { width: 100% !important; min-width: 0; margin: 0; }
            .nav-links { grid-column: 1 / -1; width: 100%; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 2px; font-size: 8.5px; line-height: 1.2; }
            .nav-links a { min-width: 0; padding: 3px 1px; border: 1px solid #e2e8f0; border-radius: 5px; background: #f8fafc; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .hero { padding: 18px 0 12px; }
            .hero-grid { gap: 8px; }
            .eyebrow { margin-bottom: 6px; padding: 4px 8px; font-size: 10px; }
            h1 { font-size: clamp(18px, 5.8vw, 22px); line-height: 1.35; }
            .hero p { margin-top: 7px; font-size: 11.5px; line-height: 1.65; }
            .hero-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; margin-top: 10px; }
            .stat { min-width: 0; padding: 6px; border-radius: 8px; box-shadow: none; }
            .stat strong { font-size: 10px; line-height: 1.3; }
            .stat span { margin-top: 2px; font-size: 7.5px; line-height: 1.35; }
            .hero-card { padding: 9px; border-radius: 11px; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.07); }
            .hero-card::before { height: 3px; }
            .hero-card h2 { margin-bottom: 6px; font-size: 14px; }
            .hero-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px; }
            .hero-list li { align-items: flex-start; gap: 4px; min-width: 0; padding: 6px; border-radius: 7px; font-size: 8.5px; line-height: 1.45; }
            .check { width: 17px; height: 17px; font-size: 10px; }
            section { padding: 13px 0; }
            .about-box { gap: 8px; padding: 9px; border-radius: 12px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06); }
            .about-badge { margin-bottom: 5px; padding: 3px 7px; font-size: 9px; }
            .about-box h2 { margin-bottom: 5px; font-size: 16px; }
            .about-box p { font-size: 10px; line-height: 1.6; }
            .about-panel { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; }
            .about-point { min-width: 0; padding: 6px; border-radius: 7px; }
            .about-point strong { margin-bottom: 2px; font-size: 8.5px; line-height: 1.35; }
            .about-point span { display: block; font-size: 7.5px; line-height: 1.4; }
            .showcase { padding-top: 5px; }
            .showcase-stage { gap: 8px; padding: 11px; border-radius: 14px; }
            .showcase-copy { text-align: center; }
            .showcase-copy h2 { margin-bottom: 5px; font-size: 17px; }
            .showcase-copy p { font-size: 10px; line-height: 1.55; }
            .devices { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; justify-items: center; gap: 6px; height: 225px; margin: 0; overflow: hidden; }
            .device { position: relative; inset: auto; transform: none; }
            .device-phone { order: 2; width: 90px; height: auto; border-radius: 15px; }
            .device-desktop { order: 1; display: block; width: 100%; height: auto; border-width: 2px; border-radius: 10px; }
            .phone-screen { padding-top: 13px; }
            .phone-screen .mini-header { height: 47px; padding: 6px; }
            .mini-logo { width: 24px; height: 24px; border-radius: 6px; }
            .mini-brand { font-size: 10px; }
            .mini-brand small { font-size: 6px; }
            .phone-screen .mini-body { padding: 6px; }
            .phone-home-title { margin-bottom: 4px; font-size: 10px; }
            .phone-home-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 3px; }
            .phone-home-service { min-width: 0; padding: 4px; border-radius: 6px; }
            .phone-home-service strong { font-size: 5.8px; }
            .phone-home-service span { font-size: 5px; }
            .phone-home-service .enter { margin-top: 2px; padding: 2px; border-radius: 4px; font-size: 5px; }
            .section-head { margin-bottom: 7px; }
            .section-head h2 { margin-bottom: 3px; font-size: 17px; }
            .section-head p { font-size: 10px; line-height: 1.55; }
            .cards { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; }
            .card { min-width: 0; padding: 7px; border-radius: 8px; box-shadow: 0 7px 16px rgba(15, 23, 42, 0.05); }
            .card-icon { width: 23px; height: 23px; margin-bottom: 4px; border-radius: 6px; font-size: 8px; }
            .card h3 { margin: 0; font-size: 9px; line-height: 1.4; }
            .card p { font-size: 7.5px; line-height: 1.45; }
            .steps { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; }
            .step { min-width: 0; min-height: 0; padding: 7px; border-radius: 8px; box-shadow: none; }
            .step::before { width: 22px; height: 22px; margin-bottom: 4px; border-radius: 6px; font-size: 9px; }
            .step h3 { margin-bottom: 3px; font-size: 8.5px; line-height: 1.4; }
            .step p { font-size: 7.5px; line-height: 1.4; }
            .features { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px; }
            .feature { min-width: 0; padding: 8px; border-radius: 8px; box-shadow: none; }
            .feature h3 { margin: 0; font-size: 10px; }
            .feature p { font-size: 8px; line-height: 1.45; }
            .contact { grid-template-columns: minmax(0, 1fr) auto; gap: 7px; padding: 9px; border-radius: 10px; box-shadow: none; }
            .contact h2 { margin-bottom: 3px; font-size: 15px; }
            .contact p { font-size: 9px; line-height: 1.45; }
            .contact .btn { width: auto; min-height: 30px; padding: 5px 8px; border-radius: 7px; font-size: 9px; }
            .contact-phone { margin-top: 5px; padding: 4px 6px; border-radius: 6px; font-size: 10px; }
            .contact-actions { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3px; }
            .contact-channel { min-width: 0; min-height: 29px; padding: 5px 4px; border-radius: 6px; font-size: 8.5px; }
            .contact-channel-icon { width: 13px; height: 13px; }
            .site-footer { padding: 10px 0; font-size: 9px; }
            .footer-inner { gap: 5px; }
            .footer-links { gap: 7px; }
            .upload-meta, .upload-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1100px) {
            .container { width: min(1240px, calc(100% - 56px)); }
            .nav { min-height: 70px; }
            .brand { font-size: 24px; }
            .nav-links { gap: 7px; font-size: 14px; }
            .nav-links a { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; padding: 7px 12px; border: 1px solid #dbe3ef; border-radius: 9px; background: #ffffff; color: #334155; box-shadow: 0 5px 14px rgba(15, 23, 42, 0.05); }
            .nav-links a:hover { border-color: #60a5fa; background: #eff6ff; color: #0f4c81; }
            .btn { min-height: 43px; font-size: 15px; }
            .nav-actions .language-switcher-button { min-height: 43px !important; font-size: 15px !important; }
            .hero { padding: 42px 0 24px; }
            .hero-grid { grid-template-columns: minmax(0, 1.12fr) minmax(380px, 0.88fr); gap: 22px; }
            .eyebrow { margin-bottom: 11px; padding: 7px 12px; font-size: 15px; }
            h1 { font-size: clamp(31px, 3vw, 42px); line-height: 1.25; }
            .hero p { margin-top: 12px; font-size: 18px; line-height: 1.75; }
            .hero-stats { gap: 9px; margin-top: 18px; }
            .stat { padding: 11px 12px; border-radius: 12px; }
            .stat strong { font-size: 18px; }
            .stat span { font-size: 13px; }
            .hero-card { padding: 19px; border-radius: 16px; }
            .hero-card h2 { margin-bottom: 10px; font-size: 22px; }
            .hero-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .hero-list li { min-width: 0; padding: 10px; border-radius: 10px; font-size: 14px; line-height: 1.55; }
            section { padding: 28px 0; }
            .about-box { grid-template-columns: 1fr; gap: 16px; padding: 22px; border-radius: 18px; }
            .about-badge { margin-bottom: 8px; font-size: 14px; }
            .about-box h2 { margin-bottom: 8px; font-size: 31px; }
            .about-box p { max-width: 1100px; font-size: 17px; line-height: 1.75; }
            .about-panel { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
            .about-point { min-width: 0; padding: 13px 15px; border-radius: 12px; }
            .about-point strong { font-size: 16px; }
            .about-point span { font-size: 14px; }
            .showcase { padding-top: 10px; }
            .showcase-stage { gap: 30px; padding: 30px; border-radius: 22px; }
            .showcase-copy h2 { font-size: 37px; }
            .showcase-copy p { font-size: 17px; }
            .devices { height: 490px; }
            .device-desktop { height: auto; top: 155px; }
            .device-phone { height: auto; width: min(35%, 210px); }
            .section-head { margin-bottom: 13px; }
            .section-head h2 { margin-bottom: 4px; font-size: 31px; }
            .section-head p { font-size: 16px; }
            .cards { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
            .card { min-width: 0; min-height: 118px; padding: 15px; border-radius: 13px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
            .card-icon { width: 36px; height: 36px; margin-bottom: 9px; border-radius: 10px; font-size: 13px; }
            .card h3 { font-size: 17px; line-height: 1.5; }
            .steps { gap: 9px; }
            .step { min-width: 0; min-height: 124px; padding: 14px; border-radius: 13px; }
            .step::before { width: 31px; height: 31px; margin-bottom: 8px; border-radius: 9px; }
            .step h3 { font-size: 16px; }
            .step p { font-size: 13px; line-height: 1.55; }
            .features { gap: 9px; }
            .feature { padding: 15px; border-radius: 13px; text-align: center; }
            .feature h3 { font-size: 18px; }
            .contact { padding: 20px 24px; border-radius: 16px; }
            .contact h2 { font-size: 27px; }
            .contact p { font-size: 15px; }
            .contact-phone { font-size: 17px; }
            .contact-channel { min-height: 43px; font-size: 14px; }
            .site-footer { padding: 18px 0; font-size: 14px; }
        }
        @media (min-width: 561px) {
            .nav-links { gap: 7px; font-size: 14px; }
            .nav-links a { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; padding: 7px 12px; border: 1px solid #dbe3ef; border-radius: 9px; background: #ffffff; color: #334155; box-shadow: 0 5px 14px rgba(15, 23, 42, 0.05); }
            .nav-links a:hover { border-color: #60a5fa; background: #eff6ff; color: #0f4c81; }
        }
    </style>
</head>
<body>
    <div class="stationery-announcement" role="note" aria-label="خدمة القرطاسية">
        <div class="container stationery-announcement-inner">
            <span class="stationery-announcement-icon" aria-hidden="true">✦</span>
            <span class="stationery-announcement-copy">
                <strong>قرطاسيتك في بيتك</strong>
                <span class="stationery-announcement-text">اختر منتجاتك، ضيفها بسلتك، والورّاق يوصلها لك.</span>
            </span>
        </div>
    </div>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="{{ route('public.home') }}" aria-label="الورّاق">
                <span class="logo"><img src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق" width="48" height="48" fetchpriority="high"></span>
                <span>الورّاق</span>
            </a>
            <nav class="nav-links" aria-label="روابط الصفحة">
                <a href="#top">الرئيسية</a>
                <a href="#how-it-works">كيف يعمل</a>
                <a href="#services">خدماتنا</a>
                <a href="#about">من نحن</a>
                <a href="#contact">تواصل معنا</a>
            </nav>
            <div class="nav-actions">
                <a class="btn light" href="{{ $loginUrl }}">تسجيل الدخول</a>
                <a class="btn blue" href="{{ $registerUrl }}">إنشاء حساب</a>
                @include('shared.language-switcher')
            </div>
        </div>
    </header>

    <main id="top">
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <span class="eyebrow">طباعة الرسائل العلمية والكتب والمذكرات وتجليدها وخدمات القرطاسية</span>
                    <h1 class="hero-title" aria-label="الورّاق الأول في السعودية لطباعة وتجليد الرسائل العلمية والكتب وبيع الخدمات القرطاسية وتوصلك لبيتك">
                        <span class="hero-title-line">الورّاق الأول في السعــودية لطباعـة</span>
                        <span class="hero-title-line">وتجليد الرسائل العلمية والكتب وبيع</span>
                        <span class="hero-title-line">الخدمـات القرطاسـية وتوصـلك لبيتـك</span>
                    </h1>
                    <p>منصة تخدم الدكتور والأستاذ والمعيد والمعلم والباحث والطالب وجميع الفئات في رفع الملفات، واختيار الطباعة والتجليد، وشراء القرطاسية، ومتابعة الطلب من مكان واحد. فرعنا في المدينة المنورة ونوصل لجميع مناطق المملكة عبر شركة RedBox.</p>
                    <div class="hero-stats" aria-label="مميزات مختصرة">
                        <div class="stat"><strong>رفع إلكتروني</strong><span>ملفاتك داخل حسابك</span></div>
                        <div class="stat"><strong>متابعة مباشرة</strong><span>حالة الطلب والفاتورة</span></div>
                        <div class="stat"><strong>خيارات دقيقة</strong><span>طباعة وتجليد وتوصيل</span></div>
                    </div>
                </div>
                <div class="hero-card" aria-label="ملخص الخدمات">
                    <h2>كل طلبك في خطوات واضحة</h2>
                    <ul class="hero-list">
                        <li><span class="check">✓</span><span>رفع ملفات PDF و Word حسب نوع الخدمة.</span></li>
                        <li><span class="check">✓</span><span>اختيار النسخ، حجم الصفحة، الطباعة، والتجليد.</span></li>
                        <li><span class="check">✓</span><span>تصفح منتجات القرطاسية وإضافتها للسلة بسهولة.</span></li>
                        <li><span class="check">✓</span><span>متابعة الطلب والفواتير والملفات المستلمة.</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="about">
            <div class="container">
                <div class="about-box">
                    <div>
                        <span class="about-badge">من نحن</span>
                        <h2>الورّاق تجربة متكاملة لجميع الفئات بخدمة موثوقة وتنفيذ منظم</h2>
                        <p>
                            الورّاق منصة متخصصة في تسهيل خدمات الطباعة والتجليد والقرطاسية ورفع الملفات ومتابعة الطلبات للدكاترة والأساتذة والمعيدين والمعلمين والباحثين والطلاب وجميع الفئات. صممت لتجمع بين وضوح الإجراءات وسرعة التنفيذ وحفظ تفاصيل الطلب في مكان واحد. نحن إحدى مؤسسات شركة مسير المدينة المحدودة، وفرعنا في المدينة المنورة مع توصيل الطلبات إلى جميع مناطق المملكة العربية السعودية عبر شركة RedBox.
                        </p>
                    </div>
                    <div class="about-panel" aria-label="قيم الورّاق">
                        <div class="about-point"><strong>خدمة مبنية على احتياج العميل</strong><span>نركز على جعل خطوات الطلب واضحة، مختصرة، وسهلة المتابعة لجميع الفئات.</span></div>
                        <div class="about-point"><strong>تنظيم يحفظ التفاصيل</strong><span>كل ملف وخيار وسعر وحالة طلب تظهر للعميل بطريقة مباشرة.</span></div>
                        <div class="about-point"><strong>هوية مهنية موثوقة</strong><span>نعمل تحت مظلة شركة مسير المدينة المحدودة لتقديم خدمة أكثر استقرارًا وجودة.</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="showcase" aria-label="معاينة واجهات الورّاق">
            <div class="container">
                <div class="showcase-stage">
                    <div class="showcase-copy">
                        <span class="eyebrow">واجهة تعمل على الجوال والكمبيوتر</span>
                        <h2>تجربة واحدة مرتبة من تسجيل الدخول حتى متابعة الطلب</h2>
                        <p>صممنا واجهات الورّاق لتظهر تفاصيل الخدمات والمنتجات والملفات والأسعار بوضوح لكل مستخدم، سواء من الجوال أو شاشة الكمبيوتر.</p>
                    </div>
                    <div class="devices">
                        <figure class="device device-desktop">
                            <img class="showcase-preview" src="{{ route('public.showcase-image', ['device' => 'desktop'], false) }}" alt="واجهة منصة الورّاق لطلبات الطباعة والقرطاسية على الكمبيوتر" width="2879" height="1625" loading="lazy" decoding="async">
                        </figure>
                        <figure class="device device-phone">
                            <img class="showcase-preview" src="{{ route('public.showcase-image', ['device' => 'mobile'], false) }}" alt="واجهة منصة الورّاق لخدمات الطباعة والتجليد على الجوال" width="702" height="1462" loading="lazy" decoding="async">
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        <section id="services">
            <div class="container">
                <div class="section-head">
                    <h2>خدماتنا</h2>
                    <p>خدمات أكاديمية وطباعية ومنتجات قرطاسية تخدم جميع الفئات في أنحاء المملكة، مع فرع في المدينة المنورة وشحن عبر RedBox.</p>
                </div>
                <div class="cards">
                    <article class="card"><div class="card-icon">01</div><h3>تنسيق رسائل الدكتوراه والماجستير</h3></article>
                    <article class="card"><div class="card-icon">02</div><h3>تدقيق لغوي للرسائل العلمية</h3></article>
                    <article class="card"><div class="card-icon">03</div><h3>طباعة وتجليد رسائل الماجستير</h3></article>
                    <article class="card"><div class="card-icon">04</div><h3>طباعة وتجليد رسائل الدكتوراه</h3></article>
                    <article class="card"><div class="card-icon">05</div><h3>إنشاء البحوث</h3></article>
                    <article class="card"><div class="card-icon">06</div><h3>طباعة المذكرات والكتب</h3></article>
                    <article class="card"><div class="card-icon">07</div><h3>تجليد الكتب كعب جلد طبيعي</h3></article>
                    <article class="card"><div class="card-icon">08</div><h3>طباعة الملفات بالألوان</h3></article>
                    <article class="card"><div class="card-icon">09</div><h3>رفع الملفات ومتابعة الطلب</h3></article>
                    <article class="card"><div class="card-icon">10</div><h3>القرطاسية</h3></article>
                </div>
            </div>
        </section>

        <section id="how-it-works">
            <div class="container">
                <div class="section-head">
                    <h2>كيف يعمل الورّاق؟</h2>
                    <p>خطوات بسيطة من إنشاء الحساب حتى متابعة الطلب.</p>
                </div>
                <div class="steps">
                    <div class="step"><h3>أنشئ حسابًا أو سجل الدخول</h3><p>ابدأ بحسابك للوصول إلى الخدمات.</p></div>
                    <div class="step"><h3>ارفع ملفاتك</h3><p>أضف ملفًا أو أكثر داخل الطلب.</p></div>
                    <div class="step"><h3>اختر الإعدادات</h3><p>حدد الطباعة والتجليد والعدد.</p></div>
                    <div class="step"><h3>راجع الطلب وأكمل الدفع</h3><p>تأكد من الإجمالي وأكمل العملية.</p></div>
                    <div class="step"><h3>تابع حالة الطلب</h3><p>راجع طلباتك والملفات المستلمة.</p></div>
                </div>
            </div>
        </section>

        <section id="features">
            <div class="container">
                <div class="section-head">
                    <h2>مميزات المنصة</h2>
                </div>
                <div class="features">
                    <div class="feature"><h3>سهولة الاستخدام</h3></div>
                    <div class="feature"><h3>سرعة التنفيذ</h3></div>
                    <div class="feature"><h3>حماية الملفات</h3></div>
                    <div class="feature"><h3>متابعة الطلب</h3></div>
                </div>
            </div>
        </section>

        <section id="contact">
            <div class="container">
                <div class="contact">
                    <div>
                        <h2>تواصل معنا</h2>
                        <p>للاستفسارات أو المساعدة في الطلبات، تواصل معنا مباشرة عبر الاتصال أو واتساب أو تلجرام. فرع الورّاق في المدينة المنورة، والتوصيل متاح لجميع مناطق المملكة عبر RedBox.</p>
                        <a class="contact-phone" href="tel:+966542440582" aria-label="رقم التواصل">+966 54 244 0582</a>
                    </div>
                    <div class="contact-actions" aria-label="قنوات التواصل">
                        <a class="contact-channel call" href="tel:+966542440582" aria-label="اتصال مباشر على رقم الورّاق">
                            <svg class="contact-channel-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.46 15.46 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z"/></svg>
                            <span>اتصال</span>
                        </a>
                        <a class="contact-channel whatsapp" href="https://wa.me/966542440582" aria-label="فتح محادثة واتساب مع الورّاق">
                            <svg class="contact-channel-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2a9.84 9.84 0 0 0-8.43 14.91L2 22l5.22-1.56A9.91 9.91 0 1 0 12.04 2Zm0 17.98a8.1 8.1 0 0 1-4.13-1.13l-.3-.18-3.1.93.96-3.02-.2-.31a8.12 8.12 0 1 1 6.77 3.71Zm4.45-6.07c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.54.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.03-.38-1.96-1.21a7.35 7.35 0 0 1-1.35-1.68c-.14-.24-.02-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.31-.74-1.79-.2-.47-.4-.4-.54-.41h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.69 2.58 4.1 3.62.57.25 1.02.39 1.37.5.58.18 1.1.16 1.51.1.46-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z"/></svg>
                            <span>واتساب</span>
                        </a>
                        <a class="contact-channel telegram" href="https://t.me/+966542440582" aria-label="فتح محادثة تلجرام مع الورّاق">
                            <svg class="contact-channel-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21.7 3.3a1.07 1.07 0 0 0-1.1-.18L2.9 9.95c-.76.3-.72 1.4.06 1.64l4.48 1.4 1.72 5.36c.22.7 1.12.9 1.62.37l2.5-2.66 4.67 3.42c.58.43 1.4.1 1.53-.61l2.57-14.55a1.07 1.07 0 0 0-.35-1.02ZM9.8 13.72l-.36 3.3-1.18-3.68 8.82-5.49-7.28 5.87Zm1.34 3.12.3-2.75 5.96-5.25-6.26 8Z"/></svg>
                            <span>تلجرام</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div>© {{ date('Y') }} الورّاق. جميع الحقوق محفوظة.</div>
            <div class="footer-links">
                <a href="#privacy">سياسة الخصوصية</a>
                <a href="#terms">الشروط والأحكام</a>
            </div>
        </div>
    </footer>
    @include('shared.language-tools')
</body>
</html>
