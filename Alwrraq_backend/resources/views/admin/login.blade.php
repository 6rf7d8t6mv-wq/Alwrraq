<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    @include('shared.tab-brand')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; display: grid; place-items: center; padding: clamp(14px, 4vw, 24px); }
        .auth-card { width: min(430px, 100%); background: #ffffff; border: 1px solid #e5e7eb; border-radius: clamp(12px, 3vw, 14px); padding: clamp(18px, 4vw, 26px); box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10); }
        .brand { margin-bottom: 22px; text-align: center; }
        .brand-logo { width: 92px; height: 92px; display: block; margin: 0 auto 12px; border-radius: 22px; object-fit: cover; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12); }
        h1 { margin: 0; font-size: clamp(24px, 7vw, 28px); }
        h2 { margin: 0 0 6px; font-size: clamp(20px, 5vw, 22px); }
        p { margin: 0; color: #64748b; line-height: 1.7; }
        label { display: block; margin: 14px 0 6px; font-weight: 700; font-size: 13px; color: #334155; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        button { width: 100%; margin-top: 18px; padding: 12px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .error { margin: 0 0 16px; padding: 12px; background: #fef2f2; color: #b91c1c; border-radius: 8px; }
        .input-note { margin: 7px 0 0; color: #64748b; font-size: 11px; font-weight: 800; line-height: 1.6; }
        .web-back { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-bottom: 16px; padding: 11px 14px; border-radius: 12px; background: linear-gradient(135deg, #0f4c81, #1d6fa5); color: #fff; text-decoration: none; font-size: 14px; font-weight: 900; border: 1px solid rgba(96, 165, 250, 0.35); box-shadow: 0 12px 24px rgba(15, 76, 129, 0.18); transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease; }
        .web-back:hover { background: linear-gradient(135deg, #123f68, #0f4c81); transform: translateY(-1px); box-shadow: 0 14px 28px rgba(15, 76, 129, 0.24); }
    </style>
</head>
<body>
    <main class="page">
        <section class="auth-card">
            <a class="web-back" href="{{ route('public.home') }}"><span aria-hidden="true">←</span><span>الصفحة الرئيسية</span></a>

            <div class="brand">
                <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                <h1>الورّاق</h1>
                <p>دخول الإدارة</p>
            </div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <h2>تسجيل دخول المدير</h2>
            <p>هذه الصفحة مخصصة لحسابات الإدارة فقط.</p>

            <form method="post" action="{{ route('admin.login.store') }}">
                @csrf
                <label for="loginIdentifier">رقم الجوال أو البريد الإلكتروني</label>
                <input id="loginIdentifier" name="login_identifier" value="{{ old('login_identifier') }}" autocomplete="username" required>
                <p class="input-note">حسابات العملاء تستخدم صفحة تسجيل الدخول العامة.</p>

                <label for="loginPassword">كلمة المرور</label>
                <input id="loginPassword" name="password" type="password" autocomplete="current-password" required>

                <button type="submit">دخول الإدارة</button>
            </form>
        </section>
    </main>
    @include('shared.guest-live-updates')
</body>
</html>
