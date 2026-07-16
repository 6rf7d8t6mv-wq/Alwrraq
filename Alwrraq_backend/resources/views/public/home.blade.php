@php
    $siteUrl = rtrim(config('app.url') ?: url('/'), '/');
    $pageUrl = $siteUrl . '/';
    $isEnglish = session('ui_locale', 'ar') === 'en';
    $pageTitle = $isEnglish
        ? 'Alwrraq | Printing, Binding, and File Upload Services for Students'
        : 'الورّاق | خدمات الطباعة والتجليد ورفع الملفات للطلاب';
    $pageDescription = $isEnglish
        ? 'Alwrraq is a student platform for managing printing and binding orders, file uploads, and order tracking easily and securely.'
        : 'الورّاق منصة طلابية لإدارة طلبات الطباعة والتجليد ورفع الملفات ومتابعة حالة الطلب بسهولة وأمان.';
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
    <link rel="canonical" href="{{ $pageUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ $isEnglish ? 'en_US' : 'ar_SA' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:site_name" content="Alwrraq">
    <style>
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; font-family: Arial, Tahoma, sans-serif; background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 44%, #f7fafc 100%); color: #0f172a; line-height: 1.8; }
        a { color: inherit; }
        .container { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .site-header { position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.90); border-bottom: 1px solid rgba(203, 213, 225, 0.74); backdrop-filter: blur(16px); box-shadow: 0 10px 35px rgba(15, 23, 42, 0.05); }
        .nav { min-height: 74px; display: flex; align-items: center; justify-content: space-between; gap: 18px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; font-weight: 900; font-size: 22px; }
        .logo { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 14px; background: linear-gradient(135deg, #0f4c81, #10233f); color: #ffffff; font-weight: 900; box-shadow: 0 14px 30px rgba(15, 76, 129, 0.24); }
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
        .device { position: absolute; background: #0b1120; border: 8px solid #070b14; box-shadow: 0 28px 70px rgba(2, 6, 23, 0.34); overflow: hidden; }
        .device-desktop { width: min(76%, 480px); height: 324px; left: 0; top: 116px; border-radius: 24px; transform: rotate(-5deg); }
        .device-phone { width: min(38%, 236px); height: 506px; right: 0; top: 18px; border-radius: 36px; transform: rotate(4deg); }
        .device-phone::before { content: ""; position: absolute; top: 9px; left: 50%; width: 74px; height: 20px; border-radius: 999px; background: #05070d; transform: translateX(-50%); z-index: 3; }
        .screen { width: 100%; height: 100%; background: #f8fafc; overflow: hidden; color: #0f172a; }
        .mini-header { height: 62px; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; background: linear-gradient(135deg, #0f172a, #0f4c81); color: #ffffff; }
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
        .card h3 { margin: 0 0 8px; font-size: 18px; }
        .card p { margin: 0; color: #64748b; font-size: 14px; }
        .steps { counter-reset: step; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
        .step { position: relative; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 18px 14px; min-height: 150px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05); }
        .step::before { counter-increment: step; content: counter(step); width: 34px; height: 34px; display: grid; place-items: center; border-radius: 12px; background: #0f4c81; color: #ffffff; font-weight: 900; margin-bottom: 12px; }
        .step h3 { margin: 0 0 6px; font-size: 16px; }
        .step p { margin: 0; color: #64748b; font-size: 13px; }
        .features { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .feature { padding: 18px; border-radius: 18px; background: linear-gradient(145deg, #10233f, #0f4c81); color: #ffffff; box-shadow: 0 18px 42px rgba(15, 76, 129, 0.16); }
        .feature h3 { margin: 0 0 6px; font-size: 16px; color: #ffffff; }
        .feature p { margin: 0; color: #dbeafe; font-size: 13px; }
        .contact { background: linear-gradient(135deg, #ffffff, #f0f7ff); border: 1px solid #dbe3ef; border-radius: 22px; padding: clamp(22px, 4vw, 34px); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: center; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08); }
        .contact h2 { margin: 0 0 8px; }
        .contact p { margin: 0; color: #64748b; }
        .site-footer { padding: 26px 0; border-top: 1px solid #e2e8f0; background: #ffffff; color: #64748b; }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
        .footer-links { display: flex; gap: 14px; flex-wrap: wrap; }
        .footer-links a { color: #334155; text-decoration: none; font-weight: 800; }
        @media (max-width: 900px) {
            .nav { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .nav-links, .nav-actions { width: 100%; flex-wrap: wrap; }
            .hero-grid, .about-box, .contact { grid-template-columns: 1fr; }
            .showcase-stage { grid-template-columns: 1fr; }
            .devices { height: 575px; max-width: 680px; margin: 0 auto; }
            .device-desktop { left: 0; top: 166px; width: min(76%, 480px); }
            .device-phone { right: 0; top: 0; width: min(40%, 236px); }
            .section-head { display: block; }
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .steps, .features { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .container { width: min(100% - 22px, 1120px); }
            .nav-links { gap: 10px; font-size: 13px; }
            .nav-actions .btn, .contact .btn { width: 100%; }
            .hero-stats { grid-template-columns: 1fr; }
            .showcase-stage { padding: 22px 14px; border-radius: 24px; }
            .devices { display: grid; gap: 18px; justify-items: center; height: auto; }
            .device { position: relative; inset: auto; transform: none; border-width: 7px; }
            .device-phone { order: 1; width: min(86%, 236px); height: 506px; border-radius: 34px; }
            .device-desktop { order: 2; width: 100%; height: 322px; border-radius: 22px; }
            .upload-meta, .upload-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cards, .steps, .features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="{{ route('public.home') }}" aria-label="الورّاق">
                <span class="logo">و</span>
                <span>الورّاق</span>
            </a>
            <nav class="nav-links" aria-label="روابط الصفحة">
                <a href="#top">الرئيسية</a>
                <a href="#about">من نحن</a>
                <a href="#services">خدماتنا</a>
                <a href="#how-it-works">كيف يعمل</a>
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
                    <span class="eyebrow">خدمات طباعة وتجليد للطلاب</span>
                    <h1>الورّاق لإدارة طلبات الطباعة ورفع الملفات بسهولة</h1>
                    <p>منصة تساعد الطلاب على رفع ملفاتهم، اختيار إعدادات الطباعة والتجليد، مراجعة الطلب، إكمال الدفع، ومتابعة حالة الطلب من مكان واحد.</p>
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
                        <h2>الورّاق تجربة طلابية متكاملة بخدمة موثوقة وتنفيذ منظم</h2>
                        <p>
                            الورّاق منصة متخصصة في تسهيل خدمات الطباعة والتجليد ورفع الملفات ومتابعة الطلبات للطلاب، صممت لتجمع بين وضوح الإجراءات وسرعة التنفيذ وحفظ تفاصيل الطلب في مكان واحد. نحن إحدى مؤسسات شركة مسير المدينة المحدودة، ونعمل على تقديم تجربة رقمية مرتبة تساعد الطالب على إنجاز طلبه بثقة وراحة من لحظة رفع الملف حتى استلامه.
                        </p>
                    </div>
                    <div class="about-panel" aria-label="قيم الورّاق">
                        <div class="about-point"><strong>خدمة مبنية على احتياج الطالب</strong><span>نركز على جعل خطوات الطلب واضحة، مختصرة، وسهلة المتابعة.</span></div>
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
                        <p>صممنا واجهات الورّاق لتظهر تفاصيل الخدمة والملفات والأسعار والطلبات بوضوح، سواء استخدمها الطالب من الجوال أو من شاشة الكمبيوتر.</p>
                    </div>
                    <div class="devices" aria-hidden="true">
                        <div class="device device-desktop">
                            <div class="screen">
                                <div class="mini-header">
                                    <div class="mini-brand">الورّاق<small>خدمات الطباعة والتجليد</small></div>
                                    <div class="mini-user">semi</div>
                                </div>
                                <div class="mini-body">
                                    <h3 class="mini-title">طباعة وتجليد رسالة دكتوراه</h3>
                                    <div class="upload-panel">
                                        <div class="upload-drop">تحميل ملف PDF إجباري + ملف Word للكعب والكليشة</div>
                                        <div class="upload-file">
                                            <strong>doctoral-research.pdf</strong>
                                            <div class="upload-meta">
                                                <span>126 صفحة</span>
                                                <span>نسختان</span>
                                                <span>A4</span>
                                                <span>وجهين</span>
                                            </div>
                                        </div>
                                        <div class="upload-options">
                                            <div class="upload-option">لون الرسالة: كحلي</div>
                                            <div class="upload-option">لون الكتابة: ذهبي</div>
                                            <div class="upload-option">التجليد: جلد طبيعي</div>
                                        </div>
                                        <div class="upload-total">
                                            <span>الإجمالي قبل التوصيل</span>
                                            <span>240 ريال</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="device device-phone">
                            <div class="screen phone-screen">
                                <div class="mini-header">
                                    <div class="mini-brand">الورّاق<small>خدمات الطباعة والتجليد</small></div>
                                </div>
                                <div class="mini-body">
                                    <h3 class="phone-home-title">اختر الخدمة المطلوبة</h3>
                                    <div class="phone-home-grid">
                                        <div class="phone-home-service"><strong>طباعة المذكرات وملفات PDF</strong><span>أبيض وأسود، أحجام، تغليف</span><span class="enter">الدخول للخدمة</span></div>
                                        <div class="phone-home-service"><strong>طباعة الملفات بالألوان</strong><span>A4 / A3 وتغليف حراري</span><span class="enter">الدخول للخدمة</span></div>
                                        <div class="phone-home-service"><strong>طباعة وتجليد كتب كعب جلد طبيعي</strong><span>كتب وملازم وتجليد احترافي</span><span class="enter">الدخول للخدمة</span></div>
                                        <div class="phone-home-service"><strong>طباعة وتجليد رسالة ماجستير</strong><span>ألوان رسالة وكتابة وتجليد</span><span class="enter">الدخول للخدمة</span></div>
                                        <div class="phone-home-service"><strong>طباعة وتجليد رسالة دكتوراه</strong><span>PDF للطباعة و Word للكعب</span><span class="enter">الدخول للخدمة</span></div>
                                        <div class="phone-home-service"><strong>تنسيق الرسائل الجامعية</strong><span>Word وخدمة تسليم داخل الطلبات</span><span class="enter">الدخول للخدمة</span></div>
                                        <div class="phone-home-service"><strong>إنشاء بحث</strong><span>اكتب عنوان البحث وعدد الصفحات</span><span class="enter">الدخول للخدمة</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="services">
            <div class="container">
                <div class="section-head">
                    <h2>خدماتنا</h2>
                    <p>مجموعة خدمات أكاديمية وطباعية متكاملة تخدم طلاب المدارس والجامعات والباحثين.</p>
                </div>
                <div class="cards">
                    <article class="card"><div class="card-icon">01</div><h3>تنسيق رسائل الدكتوراه والماجستير</h3><p>خدمة تنسيق أكاديمي للرسائل الجامعية مع متابعة تسليم الملف بعد الانتهاء.</p></article>
                    <article class="card"><div class="card-icon">02</div><h3>تدقيق لغوي للرسائل العلمية</h3><p>مراجعة لغوية احترافية لرسائل الدكتوراه والماجستير والبحوث الأكاديمية.</p></article>
                    <article class="card"><div class="card-icon">03</div><h3>طباعة وتجليد رسائل الماجستير</h3><p>خيارات مخصصة للطباعة والتجليد وألوان الرسالة والكتابة حسب متطلباتك.</p></article>
                    <article class="card"><div class="card-icon">04</div><h3>طباعة وتجليد رسائل الدكتوراه</h3><p>تجهيز رسائل الدكتوراه بملفات PDF للطباعة وملفات Word للكعب والكليشة.</p></article>
                    <article class="card"><div class="card-icon">05</div><h3>إنشاء البحوث</h3><p>إرسال عنوان البحث وعدد الصفحات المطلوبة ومتابعة تسليم الملف داخل حسابك.</p></article>
                    <article class="card"><div class="card-icon">06</div><h3>طباعة المذكرات والكتب</h3><p>طباعة ملفات PDF والمذكرات والكتب بخيارات حجم الورق والنسخ والتغليف.</p></article>
                    <article class="card"><div class="card-icon">07</div><h3>تجليد الكتب كعب جلد طبيعي</h3><p>تجليد كتب وملازم بكعب جلد طبيعي وبمقاسات مناسبة للكتب الدراسية والأكاديمية.</p></article>
                    <article class="card"><div class="card-icon">08</div><h3>طباعة الملفات بالألوان</h3><p>طباعة ملونة بمقاسات متعددة وخيارات تغليف حراري حسب نوع الملف والاستخدام.</p></article>
                    <article class="card"><div class="card-icon">09</div><h3>رفع الملفات ومتابعة الطلب</h3><p>رفع الملفات إلكترونيًا، مراجعة تفاصيل الطلب، الفاتورة، وحالة التنفيذ من حسابك.</p></article>
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
                    <p>تجربة مرتبة وآمنة لإدارة طلباتك.</p>
                </div>
                <div class="features">
                    <div class="feature"><h3>سهولة الاستخدام</h3><p>واجهة واضحة من رفع الملف حتى الطلب.</p></div>
                    <div class="feature"><h3>سرعة التنفيذ</h3><p>تجهيز الطلبات ببيانات واضحة ومباشرة.</p></div>
                    <div class="feature"><h3>حماية الملفات</h3><p>ملفاتك تظهر داخل حسابك فقط.</p></div>
                    <div class="feature"><h3>متابعة الطلب</h3><p>تعرف على حالة الطلب والفواتير بسهولة.</p></div>
                </div>
            </div>
        </section>

        <section id="contact">
            <div class="container">
                <div class="contact">
                    <div>
                        <h2>تواصل معنا</h2>
                        <p>للاستفسارات أو المساعدة في الطلبات، يمكنك تسجيل الدخول ومتابعة طلبك من داخل حسابك.</p>
                    </div>
                    <a class="btn primary" href="{{ $loginUrl }}">الدخول لحسابك</a>
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
