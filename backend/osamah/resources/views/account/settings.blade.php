<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إعدادات الحساب</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(118px, 18vw, 220px); --page-gap: clamp(10px, 3vw, 28px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #ffffff; padding: clamp(14px, 2vw, 22px) clamp(8px, 1.5vw, 16px); position: fixed; top: 0; right: 0; z-index: 20; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 16px; }
        .brand { font-size: clamp(19px, 4vw, 22px); font-weight: 800; }
        .home-button { color: #0f172a; background: #ffffff; text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 9px; border: 1px solid rgba(255, 255, 255, 0.35); text-align: center; line-height: 1.6; }
        .home-button:hover { background: #e2e8f0; }
        main { width: min(900px, 100%); margin: clamp(16px, 4vw, 28px) auto; padding: 0 clamp(12px, 4vw, 20px); }
        .panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: clamp(16px, 4vw, 24px); box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
        h1 { margin: 0 0 8px; font-size: clamp(24px, 6vw, 28px); }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.7; }
        .notice, .errors { margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; }
        .notice { background: #ecfdf5; color: #047857; }
        .errors { background: #fef2f2; color: #b91c1c; }
        .section { border-top: 1px solid #e5e7eb; padding-top: 22px; margin-top: 22px; }
        .section:first-of-type { border-top: 0; padding-top: 0; margin-top: 0; }
        .section-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .section-title { margin: 0; font-size: 20px; font-weight: 900; color: #0f172a; }
        .details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .detail { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 13px 14px; }
        .detail.full { grid-column: 1 / -1; }
        .detail-label { color: #64748b; font-size: 12px; font-weight: 800; margin-bottom: 6px; }
        .detail-value { color: #111827; font-weight: 800; line-height: 1.7; min-height: 24px; }
        .empty { color: #94a3b8; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display: block; color: #334155; font-weight: 800; font-size: 13px; margin-bottom: 6px; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        input[readonly] { background: #e2e8f0; color: #475569; }
        .full { grid-column: 1 / -1; }
        button { margin-top: 18px; padding: 12px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .section-header button { margin-top: 0; padding: 10px 14px; }
        .secondary { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .edit-panel { display: none; margin-top: 16px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; }
        .edit-panel.active { display: block; }
        @media (max-width: 720px) {
            :root { --sidebar-width: 112px; --page-gap: 8px; }
            .header { padding: 14px 7px; }
            .form-grid, .details-grid { grid-template-columns: 1fr; }
            .section-header { align-items: flex-start; flex-direction: column; }
            .home-button, button { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="brand">Mr-Student</div>
            <a class="home-button" href="{{ route('home') }}">العودة للصفحة الرئيسية</a>
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
            $hasAddress = filled($user->city) && filled($user->district) && filled($user->street) && filled($user->postal_code);
        @endphp

        <section class="panel">
            <h1>إعدادات الحساب</h1>
            <p>راجع بياناتك وعدّلها عند الحاجة.</p>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">بياناتي</h2>
                    <button class="secondary" type="button" onclick="togglePanel('profilePanel')">تعديل بياناتي</button>
                </div>

                <div class="details-grid">
                    <div class="detail">
                        <div class="detail-label">اسم المستخدم</div>
                        <div class="detail-value">{{ $user->name }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">رقم الجوال</div>
                        <div class="detail-value">{{ $user->phone }}</div>
                    </div>
                </div>

                <div id="profilePanel" class="edit-panel {{ $errors->has('name') || $errors->has('phone') ? 'active' : '' }}">
                    <form method="post" action="{{ route('account.profile.update') }}">
                        @csrf
                        @method('patch')
                        <div class="form-grid">
                            <div>
                                <label>الاسم</label>
                                <input name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div>
                                <label>رقم الجوال</label>
                                <input name="phone" value="{{ old('phone', $user->phone) }}" required>
                            </div>
                        </div>
                        <button type="submit">حفظ بياناتي</button>
                    </form>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">عنواني</h2>
                    <button class="secondary" type="button" onclick="togglePanel('addressPanel')">{{ $hasAddress ? 'تعديل عنواني' : 'إضافة عنواني' }}</button>
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
                                <input name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" required>
                            </div>
                        </div>
                        <button type="submit">حفظ العنوان</button>
                    </form>
                </div>
            </div>

            <div class="section">
                <button class="secondary" type="button" onclick="togglePanel('passwordPanel')">تغيير كلمة المرور</button>

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
        </section>
    </main>

    <script>
        function togglePanel(id) {
            document.getElementById(id).classList.toggle('active');
        }
    </script>
</body>
</html>
