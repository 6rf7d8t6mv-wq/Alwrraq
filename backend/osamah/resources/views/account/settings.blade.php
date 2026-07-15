<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إعدادات الحساب</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #ffffff; padding: clamp(16px, 2vw, 24px) clamp(12px, 1.6vw, 18px); position: fixed; top: 0; right: 0; z-index: 20; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 0; }
        .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; margin-bottom: 4px; }
        .header-actions { display: flex; flex-direction: column; align-items: stretch; gap: clamp(8px, 1.2vw, 12px); color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin-top: 24px; }
        .header-user { display: block; color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin: 0 0 12px; line-height: 1.6; }
        .home-button { display: flex; align-items: center; gap: 8px; width: 100%; color: #f8fafc; background: rgba(255, 255, 255, 0.06); text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 10px; border: 1px solid transparent; text-align: right; line-height: 1.5; }
        .home-button:hover { background: #1e293b; border-color: #334155; }
        .settings-button { display: flex; align-items: center; gap: 8px; width: 100%; color: #ffffff; background: #0f4c81; text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(96, 165, 250, 0.35); text-align: right; line-height: 1.5; }
        .settings-button:hover { background: #1d6fa5; border-color: #60a5fa; }
        .header-form { margin: 0; }
        .logout-button { width: 100%; color: #ffffff; background: #b91c1c; border: 1px solid rgba(248, 113, 113, 0.5); font-weight: 800; padding: 10px 12px; border-radius: 10px; text-align: center; line-height: 1.5; cursor: pointer; margin-top: 0; }
        .logout-button:hover { background: #dc2626; border-color: #f87171; }
        main { width: min(900px, 100%); margin: clamp(16px, 4vw, 28px) auto; padding: 0 clamp(12px, 4vw, 20px); }
        .panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: clamp(16px, 4vw, 24px); box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
        h1 { margin: 0 0 8px; font-size: clamp(24px, 6vw, 28px); }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.7; }
        .notice, .errors { margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; }
        .notice { background: #ecfdf5; color: #047857; }
        .errors { background: #fef2f2; color: #b91c1c; }
        .section { border-top: 1px solid #e5e7eb; padding-top: 22px; margin-top: 22px; }
        .section:first-of-type { border-top: 0; padding-top: 0; margin-top: 0; }
        .section-header { margin-bottom: 14px; }
        .section-title { margin: 0; font-size: 20px; font-weight: 900; color: #0f172a; }
        .details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .detail { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 13px 14px; }
        .detail.full { grid-column: 1 / -1; }
        .detail-label { color: #64748b; font-size: 12px; font-weight: 800; margin-bottom: 6px; }
        .detail-value { color: #111827; font-weight: 800; line-height: 1.7; min-height: 24px; }
        .empty { color: #94a3b8; }
        .section-actions { margin-top: 14px; display: flex; justify-content: flex-end; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display: block; color: #334155; font-weight: 800; font-size: 13px; margin-bottom: 6px; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        input[readonly] { background: #e2e8f0; color: #475569; }
        .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
        .english-number-warning.active { display: block; }
        .full { grid-column: 1 / -1; }
        button { margin-top: 18px; padding: 12px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .section-actions button { margin-top: 0; padding: 10px 14px; }
        .secondary { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .danger-button { background: #b91c1c; color: #ffffff; }
        .danger-button:hover { background: #dc2626; }
        .edit-panel { display: none; margin-top: 16px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; }
        .edit-panel.active { display: block; }
        @media (max-width: 720px) {
            :root { --sidebar-width: 132px; --page-gap: 10px; }
            .header { padding: 14px 8px; box-shadow: -8px 0 24px rgba(15, 23, 42, 0.14); }
            .form-grid, .details-grid { grid-template-columns: 1fr; }
            .section-actions { justify-content: stretch; }
            .home-button, .settings-button, .logout-button, button { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="brand">Mr-Student</div>
            <div class="header-actions">
                <span class="header-user">👤 {{ auth()->user()->name }}</span>
                <a class="home-button" href="{{ route('home') }}">🏠 الصفحة الرئيسية</a>
                <a class="home-button" href="{{ route('orders.index') }}">🧾 طلباتي</a>
                <form class="header-form" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">🚪 خروج</button>
                </form>
            </div>
        </div>
    </header>

    <main>
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif

        @php
            $user = auth()->user();
            $nameParts = preg_split('/\s+/', trim($user->name), 2);
            $firstName = $nameParts[0] ?? '';
            $secondName = $nameParts[1] ?? '';
            $hasAddress = filled($user->city) && filled($user->district) && filled($user->street) && filled($user->postal_code);
        @endphp

        <section class="panel">
            <h1>إعدادات الحساب</h1>
            <p>راجع بياناتك وعدّلها عند الحاجة.</p>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">بياناتي</h2>
                </div>

                <div class="details-grid">
                    <div class="detail">
                        <div class="detail-label">الاسم الأول</div>
                        <div class="detail-value">{{ $firstName }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">الاسم الثاني</div>
                        <div class="detail-value {{ $secondName ? '' : 'empty' }}">{{ $secondName ?: 'لم تتم الإضافة بعد' }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">رقم الجوال</div>
                        <div class="detail-value">{{ $user->phone }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">البريد الإلكتروني</div>
                        <div class="detail-value {{ $user->email ? '' : 'empty' }}">{{ $user->email ?: 'لم تتم الإضافة بعد' }}</div>
                    </div>
                </div>
                <div class="section-actions">
                    <button class="secondary" type="button" onclick="togglePanel('profilePanel')">تعديل بياناتي</button>
                </div>

                <div id="profilePanel" class="edit-panel {{ $errors->has('first_name') || $errors->has('second_name') || $errors->has('phone') || $errors->has('email') ? 'active' : '' }}">
                    <form method="post" action="{{ route('account.profile.update') }}">
                        @csrf
                        @method('patch')
                        <div class="form-grid">
                            <div>
                                <label>الاسم الأول</label>
                                <input name="first_name" value="{{ old('first_name', $firstName) }}" required>
                            </div>
                            <div>
                                <label>الاسم الثاني</label>
                                <input name="second_name" value="{{ old('second_name', $secondName) }}" required>
                            </div>
                            <div>
                                <label>رقم الجوال</label>
                                <input name="phone" inputmode="numeric" value="{{ old('phone', $user->phone) }}" required>
                            </div>
                            <div>
                                <label>البريد الإلكتروني</label>
                                <input name="email" type="email" inputmode="email" value="{{ old('email', $user->email) }}" placeholder="اختياري">
                            </div>
                        </div>
                        <button type="submit">حفظ بياناتي</button>
                    </form>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">عنواني</h2>
                </div>

                <div class="details-grid">
                    <div class="detail">
                        <div class="detail-label">الدولة</div>
                        <div class="detail-value">المملكة العربية السعودية</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">المدينة أو المحافظة</div>
                        <div class="detail-value {{ $user->city ? '' : 'empty' }}">{{ $user->city ?: 'لم تتم الإضافة بعد' }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">الحي</div>
                        <div class="detail-value {{ $user->district ? '' : 'empty' }}">{{ $user->district ?: 'لم تتم الإضافة بعد' }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">الشارع</div>
                        <div class="detail-value {{ $user->street ? '' : 'empty' }}">{{ $user->street ?: 'لم تتم الإضافة بعد' }}</div>
                    </div>
                    <div class="detail full">
                        <div class="detail-label">الرمز البريدي</div>
                        <div class="detail-value {{ $user->postal_code ? '' : 'empty' }}">{{ $user->postal_code ?: 'لم تتم الإضافة بعد' }}</div>
                    </div>
                </div>
                <div class="section-actions">
                    <button class="secondary" type="button" onclick="togglePanel('addressPanel')">{{ $hasAddress ? 'تعديل عنواني' : 'إضافة عنواني' }}</button>
                </div>

                <div id="addressPanel" class="edit-panel {{ $errors->has('city') || $errors->has('district') || $errors->has('street') || $errors->has('postal_code') ? 'active' : '' }}">
                    <form method="post" action="{{ route('account.address.update') }}">
                        @csrf
                        @method('patch')
                        <div class="form-grid">
                            <div>
                                <label>الدولة</label>
                                <input value="المملكة العربية السعودية" readonly>
                            </div>
                            <div>
                                <label>المدينة أو المحافظة</label>
                                <input name="city" list="saudiCities" value="{{ old('city', $user->city) }}" placeholder="اكتب للبحث ثم اختر المدينة" required>
                                <datalist id="saudiCities">
                                    @foreach ($saudiCities as $city)
                                        <option value="{{ $city }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label>اسم الحي</label>
                                <input name="district" value="{{ old('district', $user->district) }}" required>
                            </div>
                            <div>
                                <label>الشارع</label>
                                <input name="street" value="{{ old('street', $user->street) }}" required>
                            </div>
                            <div>
                                <label>الرمز البريدي</label>
                                <input name="postal_code" inputmode="numeric" value="{{ old('postal_code', $user->postal_code) }}" required>
                            </div>
                        </div>
                        <button type="submit">حفظ العنوان</button>
                    </form>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">كلمة المرور</h2>
                </div>
                <div class="details-grid">
                    <div class="detail full">
                        <div class="detail-label">كلمة المرور</div>
                        <div class="detail-value">يمكنك تغيير كلمة المرور عند الحاجة.</div>
                    </div>
                </div>
                <div class="section-actions">
                    <button class="secondary" type="button" onclick="togglePanel('passwordPanel')">تغيير كلمة المرور</button>
                </div>

                <div id="passwordPanel" class="edit-panel {{ $errors->has('current_password') || $errors->has('password') ? 'active' : '' }}">
                    <h2 class="section-title">تغيير كلمة المرور</h2>
                    <p>اكتب كلمة المرور القديمة ثم كلمة المرور الجديدة.</p>

                    <form method="post" action="{{ route('account.password.update') }}">
                        @csrf
                        @method('patch')
                        <div class="form-grid">
                            <div class="full">
                                <label>كلمة المرور القديمة</label>
                                <input name="current_password" type="password" required>
                            </div>
                            <div>
                                <label>كلمة المرور الجديدة</label>
                                <input name="password" type="password" required>
                            </div>
                            <div>
                                <label>تأكيد كلمة المرور الجديدة</label>
                                <input name="password_confirmation" type="password" required>
                            </div>
                        </div>
                        <button type="submit">حفظ كلمة المرور</button>
                    </form>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">حذف الحساب</h2>
                </div>
                <div class="details-grid">
                    <div class="detail full">
                        <div class="detail-label">حذف حسابي</div>
                        <div class="detail-value">يمكنك حذف حسابك نهائيًا عند الحاجة.</div>
                    </div>
                </div>
                <div class="section-actions">
                    <form method="post" action="{{ route('account.profile.destroy') }}" onsubmit="return confirm('هل أنت متأكد من حذف حسابك نهائيًا؟');">
                        @csrf
                        @method('delete')
                        <button class="danger-button" type="submit">حذف حسابي</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        function togglePanel(id) {
            document.getElementById(id).classList.toggle('active');
        }

        function bindInputRule(input, pattern, message) {
            const showWarning = () => {
                let warning = input.nextElementSibling;
                if (!warning || !warning.classList.contains('english-number-warning')) {
                    warning = document.createElement('div');
                    warning.className = 'english-number-warning';
                    input.insertAdjacentElement('afterend', warning);
                }

                const invalid = input.value !== '' && !pattern.test(input.value);
                warning.textContent = message;
                warning.classList.toggle('active', invalid);
                input.setCustomValidity(invalid ? message : '');
            };

            input.addEventListener('input', showWarning);
            showWarning();
        }

        document.querySelectorAll('input[name="phone"]').forEach((input) => {
            bindInputRule(input, /^05[0-9]{8}$/, 'تنبيه: رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام إنجليزية فقط.');
        });

        document.querySelectorAll('input[name="email"]').forEach((input) => {
            bindInputRule(input, /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/, 'تنبيه: اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com.');
        });

        document.querySelectorAll('input[name="postal_code"]').forEach((input) => {
            bindInputRule(input, /^[0-9]+$/, 'تنبيه: لا يقبل هذا الحقل إلا الأرقام الإنجليزية فقط 0-9.');
        });

        document.querySelectorAll('input[name="password"], input[name="password_confirmation"]').forEach((input) => {
            bindInputRule(input, /^[A-Za-z0-9]+$/, 'تنبيه: كلمة المرور تقبل حروف وأرقام إنجليزية فقط.');
        });
    </script>
</body>
</html>
