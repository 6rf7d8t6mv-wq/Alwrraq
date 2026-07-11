<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الدخول - Mr-Student</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; display: grid; place-items: center; padding: clamp(14px, 4vw, 24px); }
        .auth-card { width: min(430px, 100%); background: #ffffff; border: 1px solid #e5e7eb; border-radius: clamp(12px, 3vw, 14px); padding: clamp(18px, 4vw, 26px); box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10); }
        .brand { margin-bottom: 22px; text-align: center; }
        h1 { margin: 0; font-size: clamp(24px, 7vw, 28px); }
        h2 { margin: 0 0 6px; font-size: clamp(20px, 5vw, 22px); }
        p { margin: 0; color: #64748b; line-height: 1.7; }
        label { display: block; margin: 14px 0 6px; font-weight: 700; font-size: 13px; color: #334155; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        button { width: 100%; margin-top: 18px; padding: 12px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .secondary-action { margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; }
        .switch-button { width: auto; margin-top: 10px; padding: 9px 13px; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .error { margin: 0 0 16px; padding: 12px; background: #fef2f2; color: #b91c1c; border-radius: 8px; }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }
    </style>
</head>
<body>
    <main class="page">
        <section class="auth-card">
            <div class="brand">
                <h1>Mr-Student</h1>
                <p>خدمات الطباعة والتجليد</p>
            </div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div id="loginPanel" class="auth-panel active">
                <h2>تسجيل الدخول</h2>
                <p>ادخل رقم جوالك وكلمة المرور للمتابعة.</p>

                <form method="post" action="{{ route('login.store') }}">
                    @csrf
                    <label for="loginPhone">رقم الجوال</label>
                    <input id="loginPhone" name="phone" value="{{ old('phone') }}" required>

                    <label for="loginPassword">كلمة المرور</label>
                    <input id="loginPassword" name="password" type="password" required>

                    <button type="submit">دخول</button>
                </form>

                <div class="secondary-action">
                    <p>ليس لديك حساب؟</p>
                    <button class="switch-button" type="button" onclick="showPanel('register')">إنشاء حساب جديد</button>
                </div>
            </div>

            <div id="registerPanel" class="auth-panel">
                <h2>إنشاء حساب</h2>
                <p>أنشئ حسابك لإرسال ملفات الطباعة ومتابعة طلبك.</p>

                <form method="post" action="{{ route('register.store') }}">
                    @csrf
                    <label for="name">الاسم</label>
                    <input id="name" name="name" required>

                    <label for="phone">رقم الجوال</label>
                    <input id="phone" name="phone" required>

                    <label for="password">كلمة المرور</label>
                    <input id="password" name="password" type="password" required>

                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>

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
    </script>
</body>
</html>
