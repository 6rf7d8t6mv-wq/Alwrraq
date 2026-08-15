<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="robots" content="noindex, nofollow, noarchive">
    @include('shared.tab-brand')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; display: grid; place-items: center; padding: clamp(14px, 4vw, 24px); }
        .auth-card { width: min(430px, 100%); background: #ffffff; border: 1px solid #e5e7eb; border-radius: clamp(12px, 3vw, 14px); padding: clamp(18px, 4vw, 26px); box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10); }
        .auth-top-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; align-items: stretch; margin-bottom: 28px; }
        .web-back { display: inline-flex; align-items: center; justify-content: center; gap: 5px; width: 100%; min-height: 36px; margin: 0; padding: 7px 9px; border-radius: 9px; background: linear-gradient(135deg, #0f4c81, #1d6fa5); color: #fff; text-decoration: none; font-size: 11px; font-weight: 900; border: 1px solid rgba(96, 165, 250, 0.35); box-shadow: 0 7px 16px rgba(15, 76, 129, 0.15); transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease; }
        .web-back:hover { background: linear-gradient(135deg, #123f68, #0f4c81); transform: translateY(-1px); box-shadow: 0 9px 20px rgba(15, 76, 129, 0.21); }
        .auth-top-actions .language-switcher-form { width: 100%; }
        .auth-top-actions .language-switcher-button { min-height: 36px; padding: 7px 9px; border-radius: 9px; gap: 5px; font-size: 11px; }
        .brand { margin-bottom: 22px; text-align: center; }
        .brand-logo { width: 92px; height: 92px; display: block; margin: 0 auto 12px; border-radius: 22px; object-fit: cover; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12); }
        h1 { margin: 0; font-size: clamp(24px, 7vw, 28px); }
        h2 { margin: 0 0 6px; font-size: clamp(20px, 5vw, 22px); }
        p { margin: 0; color: #64748b; line-height: 1.7; }
        label { display: block; margin: 14px 0 6px; font-weight: 700; font-size: 13px; color: #334155; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        button { width: 100%; margin-top: 18px; padding: 12px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .secondary-action { margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; }
        .switch-button { width: auto; margin-top: 10px; padding: 9px 13px; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .error { margin: 0 0 16px; padding: 12px; background: #fef2f2; color: #b91c1c; border-radius: 8px; }
        .input-note { margin: 7px 0 0; color: #64748b; font-size: 11px; font-weight: 800; line-height: 1.6; }
        .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
        .english-number-warning.active { display: block; }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .form-grid label { margin-top: 14px; }
        @media (max-width: 520px) {
            .auth-card { padding-inline: 14px; }
            .form-grid { gap: 8px; }
            .form-grid input { padding-inline: 9px; }
        }
    </style>
</head>
<body>
    @php($appMode = $appMode ?? false)
    <main class="page">
        <section class="auth-card">
            <div class="auth-top-actions">
                <a class="web-back" href="{{ $appMode ? route('home') : route('public.home') }}"><span aria-hidden="true">←</span><span>الصفحة الرئيسية</span></a>
                @include('shared.language-switcher')
            </div>
            <div class="brand">
                <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                <h1>الورّاق</h1>
                <p>خدمات النسخ والتصوير</p>
            </div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div id="loginPanel" class="auth-panel active">
                <h2>تسجيل الدخول</h2>

                <form method="post" action="{{ $appMode ? route('app.login.store') : route('login.store') }}">
                    @csrf
                    <label for="loginIdentifier">رقم الجوال أو البريد الإلكتروني</label>
                    <input id="loginIdentifier" name="login_identifier" value="{{ old('login_identifier') }}" autocomplete="username" required>

                    <label for="loginPassword">كلمة المرور</label>
                    <input id="loginPassword" name="password" type="password" autocomplete="current-password" required>
                    <p class="input-note">كلمة المرور تقبل الحروف الإنجليزية والأرقام والرموز فقط.</p>

                    <button type="submit">دخول</button>
                </form>

                <div class="secondary-action">
                    <p>ليس لديك حساب؟</p>
                    <button class="switch-button" type="button" onclick="showPanel('register')">إنشاء حساب جديد</button>
                </div>
            </div>

            <div id="registerPanel" class="auth-panel">
                <h2>إنشاء حساب</h2>

                <form method="post" action="{{ $appMode ? route('app.register.store') : route('register.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div>
                            <label for="firstName">الاسم الأول</label>
                            <input id="firstName" name="first_name" value="{{ old('first_name') }}" required>
                        </div>
                        <div>
                            <label for="secondName">الاسم الثاني</label>
                            <input id="secondName" name="second_name" value="{{ old('second_name') }}" required>
                        </div>
                    </div>

                    <label for="phone">رقم الجوال</label>
                    <input id="phone" name="phone" inputmode="numeric" autocomplete="tel" required>

                    <label for="password">كلمة المرور</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    <p class="input-note">كلمة المرور تقبل الحروف الإنجليزية والأرقام والرموز فقط.</p>

                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

                    <button type="submit">إنشاء الحساب</button>
                </form>

                <div class="secondary-action">
                    <p>لديك حساب بالفعل؟</p>
                    <button class="switch-button" type="button" onclick="showPanel('login')">العودة لتسجيل الدخول</button>
                </div>
            </div>
        </section>
    </main>

    <script>
        function showPanel(panel) {
            document.getElementById('loginPanel').classList.toggle('active', panel === 'login');
            document.getElementById('registerPanel').classList.toggle('active', panel === 'register');
        }

        if (window.location.hash === '#register') {
            showPanel('register');
        }

        function convertArabicDigits(value) {
            return value
                .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)))
                .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)));
        }

        function bindInputRule(input, pattern, message, sanitizer = null) {
            const showWarning = () => {
                if (sanitizer) {
                    const cursor = input.selectionStart;
                    const oldLength = input.value.length;
                    input.value = sanitizer(input.value);
                    const diff = oldLength - input.value.length;
                    if (document.activeElement === input && cursor !== null) {
                        input.setSelectionRange(Math.max(0, cursor - diff), Math.max(0, cursor - diff));
                    }
                }

                let warning = input.nextElementSibling;
                while (warning && warning.classList.contains('input-note')) {
                    warning = warning.nextElementSibling;
                }
                if (!warning || !warning.classList.contains('english-number-warning')) {
                    warning = document.createElement('div');
                    warning.className = 'english-number-warning';
                    let insertAfter = input.nextElementSibling;
                    while (insertAfter && insertAfter.classList.contains('input-note')) {
                        input.parentNode.insertBefore(warning, insertAfter.nextElementSibling);
                        insertAfter = null;
                    }
                    if (insertAfter !== null) {
                        input.insertAdjacentElement('afterend', warning);
                    }
                }

                const invalid = input.value !== '' && !pattern.test(input.value);
                warning.textContent = message;
                warning.classList.toggle('active', invalid);
                input.setCustomValidity(invalid ? message : '');
            };

            input.addEventListener('input', showWarning);
            showWarning();
        }

        const asciiPrintable = (value) => convertArabicDigits(value).replace(/[^\x21-\x7E]/g, '');
        const phoneOnly = (value) => convertArabicDigits(value).replace(/[^0-9]/g, '').slice(0, 10);
        document.querySelectorAll('input[name="login_identifier"]').forEach((input) => {
            bindInputRule(
                input,
                /^[\x21-\x7E]+$/,
                document.documentElement.lang === 'en'
                    ? 'Notice: Login accepts a phone number or email in English characters.'
                    : 'تنبيه: أدخل رقم الجوال أو البريد الإلكتروني بحروف وأرقام إنجليزية.',
                asciiPrintable
            );
        });

        document.querySelectorAll('#registerPanel input[name="phone"]').forEach((input) => {
            bindInputRule(
                input,
                /^05[0-9]{8}$/,
                document.documentElement.lang === 'en'
                    ? 'Notice: The phone number must start with 05 and contain exactly 10 digits.'
                    : 'تنبيه: رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام إنجليزية فقط.',
                phoneOnly
            );
        });

        document.querySelectorAll('input[name="password"], input[name="password_confirmation"]').forEach((input) => {
            bindInputRule(
                input,
                /^[\x21-\x7E]+$/,
                document.documentElement.lang === 'en'
                    ? 'Notice: The password accepts English letters, numbers, and symbols only.'
                    : 'تنبيه: كلمة المرور تقبل الحروف الإنجليزية والأرقام والرموز فقط.',
                asciiPrintable
            );
        });

    </script>
    @include('shared.language-tools')
    @include('shared.guest-live-updates')
</body>
</html>
