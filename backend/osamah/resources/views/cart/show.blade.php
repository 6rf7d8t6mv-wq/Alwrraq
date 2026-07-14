<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>السلة والدفع</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #ffffff; padding: clamp(16px, 2vw, 24px) clamp(12px, 1.6vw, 18px); position: fixed; top: 0; right: 0; z-index: 20; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 0; }
        .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; margin-bottom: 4px; }
        .header-actions { display: flex; flex-direction: column; align-items: stretch; gap: clamp(8px, 1.2vw, 12px); color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin-top: 24px; }
        .header-actions a { color: #f8fafc; text-decoration: none; }
        .header-user { display: block; color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin: 0 0 12px; line-height: 1.6; }
        .home-button { display: flex; align-items: center; gap: 8px; width: 100%; color: #f8fafc; background: rgba(255, 255, 255, 0.06); text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 10px; border: 1px solid transparent; text-align: right; line-height: 1.5; }
        .home-button:hover { background: #1e293b; border-color: #334155; }
        .settings-button { display: flex; align-items: center; gap: 8px; width: 100%; color: #ffffff; background: #0f4c81; text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(96, 165, 250, 0.35); text-align: right; line-height: 1.5; }
        .settings-button:hover { background: #1d6fa5; border-color: #60a5fa; }
        .header-form { margin: 0; }
        .logout-button { width: 100%; color: #ffffff; background: #b91c1c; border: 1px solid rgba(248, 113, 113, 0.5); font-weight: 800; padding: 10px 12px; border-radius: 10px; text-align: center; line-height: 1.5; cursor: pointer; }
        .logout-button:hover { background: #dc2626; border-color: #f87171; }
        main { width: min(1180px, 100%); margin: clamp(16px, 4vw, 28px) auto; padding: 0 clamp(12px, 4vw, 20px); }
        .notice, .errors { margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; font-weight: 800; }
        .notice { background: #ecfdf5; color: #047857; }
        .errors { background: #fef2f2; color: #b91c1c; }
        .panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: clamp(16px, 4vw, 22px); box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); margin-bottom: 18px; }
        h1 { margin: 0 0 8px; font-size: clamp(24px, 6vw, 30px); }
        h2 { margin: 0 0 16px; font-size: clamp(19px, 4.5vw, 22px); color: #0f172a; }
        p { margin: 0 0 18px; color: #64748b; line-height: 1.7; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 18px; }
        .meta-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
        .meta-card.full { grid-column: 1 / -1; }
        .meta-card span { display: block; color: #64748b; font-size: 12px; font-weight: 800; margin-bottom: 6px; }
        .meta-card strong { font-size: 16px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; overflow: hidden; border-radius: 10px; }
        th, td { text-align: right; padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        th { background: #f8fafc; color: #334155; font-weight: 900; }
        .files-panel { width: 100%; padding: 16px; box-sizing: border-box; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .detail-table-wrap { width: 100%; overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; }
        .detail-table-wrap table { width: 100%; min-width: 720px; table-layout: auto; }
        .detail-table-wrap th,
        .detail-table-wrap td { padding: 11px 10px; white-space: normal; }
        .detail-table-wrap td:first-child { min-width: 180px; word-break: break-word; }
        .price-cell { color: #0f172a; font-weight: 900; white-space: nowrap; background: #f8fafc; }
        .totals { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .total-card { background: #0f172a; color: #ffffff; border-radius: 10px; padding: 16px; }
        .total-card span { display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 8px; }
        .total-card strong { font-size: 24px; }
        .payment-options { display: grid; grid-template-columns: 1fr 1.4fr; gap: 16px; align-items: start; }
        .pay-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #f8fafc; }
        .apple-pay { width: 100%; background: #000000; color: #ffffff; border: 0; border-radius: 9px; padding: 14px; font-size: 17px; font-weight: 900; cursor: pointer; }
        label { display: block; color: #334155; font-weight: 800; font-size: 13px; margin: 12px 0 6px; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
        .english-number-warning.active { display: block; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .full { grid-column: 1 / -1; }
        .submit-card { width: 100%; margin-top: 16px; padding: 13px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 900; cursor: pointer; }
        .paid { background: #ecfdf5; color: #047857; padding: 14px; border-radius: 10px; font-weight: 900; }
        .close-to-orders { display: inline-flex; align-items: center; justify-content: center; margin-top: 14px; min-width: 150px; padding: 11px 16px; border-radius: 9px; background: #0f172a; color: #ffffff; text-decoration: none; font-weight: 900; }
        .close-to-orders:hover { background: #1e293b; }
        .missing-info { margin-top: 16px; padding: 14px 16px; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; border-radius: 10px; font-weight: 900; line-height: 1.8; }
        .missing-info ul { margin: 8px 0 0; padding: 0 18px 0 0; }
        @media (max-width: 820px) {
            :root { --sidebar-width: 132px; --page-gap: 10px; }
            .header { padding: 14px 8px; box-shadow: -8px 0 24px rgba(15, 23, 42, 0.14); }
            .payment-options { grid-template-columns: 1fr; align-items: stretch; }
            .meta, .totals, .form-grid { grid-template-columns: 1fr; }
            .meta-card.full { grid-column: auto; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .home-button, .settings-button, .logout-button, .submit-card, .apple-pay, .close-to-orders { width: 100%; text-align: center; }
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
                <a class="settings-button" href="{{ route('account.settings') }}">⚙️ إعداداتي</a>
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

        <section class="panel">
            <h1>السلة والدفع</h1>
            <p>راجع الطلب قبل الدفع. لا يوجد دفع عند الاستلام أو كاش.</p>
            @php
                $serviceNames = [
                    'notes' => 'طباعة وتغليف المذكرات',
                    'thesis' => 'طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج',
                    'phd' => 'طباعة وتجليد رسالة دكتوراه',
                    'formatting' => 'تنسيق الرسائل الجامعية',
                    'research' => 'إنشاء بحث',
                ];
                $noPrintServices = ['formatting', 'research'];
                $projectNames = [
                    'thesis' => 'رسالة ماجستير',
                    'supplementary' => 'بحث تكميلي',
                    'graduation' => 'بحث تخرج',
                ];
                $coverColorNames = [
                    'black' => 'أسود',
                    'light_blue' => 'أزرق فاتح',
                    'navy' => 'أزرق كحلي',
                    'dark_green' => 'الأخضر الداكن',
                    'light_green' => 'الأخضر الفاتح',
                    'burgundy' => 'العنابي',
                    'beige' => 'البيج',
                    'white' => 'الأبيض',
                ];
                $writingColorNames = [
                    'gold' => 'كتابة باللون الذهبي',
                    'black' => 'كتابة باللون الأسود',
                ];
                $bindingNames = [
                    'tape' => $order->service_type === 'notes' ? 'تغليف دبوس' : 'تجليد دبوس',
                    'wire' => $order->service_type === 'notes' ? 'تغليف سلك' : 'تجليد سلك',
                    'normal' => $order->service_type === 'notes' ? 'تغليف عادي' : 'تجليد عادي',
                    'none' => $order->service_type === 'notes' ? 'بدون تغليف' : 'بدون تجليد',
                ];
                $bindingLabel = $order->service_type === 'notes'
                    ? 'التغليف'
                    : ($order->service_type === 'formatting' ? 'التنسيق' : ($order->service_type === 'research' ? 'إنشاء البحث' : 'التجليد'));
                $bindingPriceLabel = $order->service_type === 'notes'
                    ? 'سعر التغليف'
                    : ($order->service_type === 'formatting' ? 'سعر التنسيق' : ($order->service_type === 'research' ? 'سعر إنشاء البحث' : 'سعر التجليد'));
                $statusNames = [
                    'new' => 'بانتظار الدفع',
                    'reviewing' => 'قيد المراجعة',
                    'priced' => 'تم التسعير',
                    'processing' => 'قيد التنفيذ',
                    'completed' => 'مكتمل',
                    'cancelled' => 'ملغي',
                ];
                $projectTypes = $order->service_type === 'thesis'
                    ? $order->files
                        ->pluck('thesis_project_type')
                        ->filter()
                        ->unique()
                        ->map(fn ($type) => $projectNames[$type] ?? $type)
                        ->values()
                    : collect();
                $missingRequirements = collect();
                if ($order->service_type === 'notes' && $order->files->contains(fn ($file) => blank($file->binding_type))) {
                    $missingRequirements->push('اختيار نوع التغليف لكل ملف.');
                }
                if (in_array($order->service_type, ['thesis', 'phd'], true) && $order->files->contains(fn ($file) => blank($file->cover_color) || blank($file->writing_color))) {
                    $missingRequirements->push('اختيار لون الرسالة ولون الكتابة لكل ملف.');
                }
                if ($order->service_type === 'thesis' && $order->files->contains(fn ($file) => $file->file_type === 'pdf' && blank($file->thesis_project_type))) {
                    $missingRequirements->push('اختيار نوع مشروع الرسالة لكل ملف PDF.');
                }
                $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                $createdAtText = $dayNames[$order->created_at->dayOfWeek] . ' - ' . $order->created_at->format('Y-m-d H:i');
                $isPaid = $order->payment_status === 'paid';
                $isCompleted = $isPaid && in_array($order->status, ['completed', 'finished'], true);
                $displayStatus = $isCompleted
                    ? 'مكتمل'
                    : (in_array($order->status, ['completed', 'finished'], true) ? 'بانتظار الدفع' : ($statusNames[$order->status] ?? $order->status));
            @endphp
            <div class="meta">
                <div class="meta-card"><span>رقم الطلب</span><strong>#{{ $order->id }}</strong></div>
                <div class="meta-card"><span>تاريخ إنشاء الطلب</span><strong data-local-datetime="{{ $order->created_at->toIso8601String() }}">{{ $createdAtText }}</strong></div>
                <div class="meta-card"><span>حالة الطلب</span><strong>{{ $displayStatus }}</strong></div>
                <div class="meta-card"><span>الدفع</span><strong>{{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}</strong></div>
                <div class="meta-card"><span>عدد الملفات</span><strong>{{ $order->files->count() }}</strong></div>
                <div class="meta-card full"><span>الخدمة</span><strong>{{ $serviceNames[$order->service_type] ?? $order->service_type }}</strong></div>
                @if ($projectTypes->isNotEmpty())
                    <div class="meta-card full"><span>تفصيل مشروع الرسالة</span><strong>{{ $projectTypes->implode('، ') }}</strong></div>
                @endif
            </div>
            @if ($missingRequirements->isNotEmpty())
                <div class="missing-info">
                    لا يمكن اعتماد الطلب قبل إكمال المعلومات التالية:
                    <ul>
                        @foreach ($missingRequirements as $requirement)
                            <li>{{ $requirement }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        <section class="panel">
            <div class="files-panel">
                <h2>الملفات والتفاصيل والأسعار</h2>
                <div class="detail-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>الملف</th>
                                @if ($order->service_type !== 'research')
                                    <th>النوع</th>
                                @endif
                                @if ($order->service_type === 'thesis')
                                    <th>مشروع الرسالة</th>
                                @endif
                                @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                    <th>الجامعة/المعهد</th>
                                    <th>لون الرسالة</th>
                                    <th>لون الكتابة</th>
                                @endif
                                <th>الصفحات</th>
                                @if ($order->service_type !== 'research')
                                    <th>النسخ</th>
                                @endif
                                @if (! in_array($order->service_type, $noPrintServices, true))
                                    <th>{{ $bindingLabel }}</th>
                                @endif
                                @if (! in_array($order->service_type, $noPrintServices, true))
                                    <th>سعر الطباعة</th>
                                @endif
                                <th>{{ $bindingPriceLabel }}</th>
                                <th>إجمالي الملف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->files as $file)
                                <tr>
                                    <td>{{ $file->original_name }}</td>
                                    @if ($order->service_type !== 'research')
                                        <td>{{ strtoupper($file->file_type) }}</td>
                                    @endif
                                    @if ($order->service_type === 'thesis')
                                        <td>{{ $projectNames[$file->thesis_project_type] ?? '-' }}</td>
                                    @endif
                                @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                    <td>{{ $file->university_name ?: '-' }}</td>
                                    <td>{{ $coverColorNames[$file->cover_color] ?? '-' }}</td>
                                    <td>{{ $writingColorNames[$file->writing_color] ?? '-' }}</td>
                                @endif
                                    <td>{{ $file->pages }}</td>
                                    @if ($order->service_type !== 'research')
                                        <td>{{ $file->copies }}</td>
                                    @endif
                                    @if (! in_array($order->service_type, $noPrintServices, true))
                                        <td>{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                                    @endif
                                    @if (! in_array($order->service_type, $noPrintServices, true))
                                        <td class="price-cell">{{ $file->print_price }} ريال</td>
                                    @endif
                                    <td class="price-cell">{{ $file->binding_price }} ريال</td>
                                    <td class="price-cell">{{ $file->total_price }} ريال</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>الإجمالي</h2>
            <div class="totals">
                @if (! in_array($order->service_type, $noPrintServices, true))
                    <div class="total-card"><span>سعر الطباعة</span><strong>{{ $order->print_total }} ريال</strong></div>
                @endif
                <div class="total-card"><span>{{ $bindingPriceLabel }}</span><strong>{{ $order->binding_total }} ريال</strong></div>
                @if ($order->discount_amount > 0)
                    <div class="total-card"><span>الخصم {{ $order->discount_code }}</span><strong>- {{ $order->discount_amount }} ريال</strong></div>
                @endif
                <div class="total-card"><span>الإجمالي</span><strong>{{ $order->grand_total }} ريال</strong></div>
            </div>
        </section>

        <section class="panel">
            <h2>الدفع</h2>
            @if ($order->payment_status === 'paid')
                <div class="paid">تم الدفع بنجاح. رقم العملية: {{ $order->payment_reference }}</div>
                <a class="close-to-orders" href="{{ route('orders.index') }}" target="_top">إغلاق</a>
            @elseif ($missingRequirements->isNotEmpty())
                <div class="missing-info">أكمل المعلومات المطلوبة من صفحة الخدمة قبل الدفع. الطلب محفوظ وسيظهر في صفحة طلباتي.</div>
            @else
                <div class="payment-options">
                    <div class="pay-card">
                        <h2>Apple Pay</h2>
                        <p>اعتماد الطلب والدفع عبر Apple Pay.</p>
                        <form method="post" action="{{ route('cart.pay', $order) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="apple_pay">
                            <button class="apple-pay" type="submit">Apple Pay</button>
                        </form>
                    </div>

                    <div class="pay-card">
                        <h2>بطاقة بنكية</h2>
                        <form method="post" action="{{ route('cart.pay', $order) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="card">
                            <div class="form-grid">
                                <div class="full">
                                    <label>اسم حامل البطاقة</label>
                                    <input name="card_name" autocomplete="cc-name" required>
                                </div>
                                <div class="full">
                                    <label>رقم البطاقة</label>
                                    <input name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="0000 0000 0000 0000" required>
                                </div>
                                <div>
                                    <label>تاريخ الانتهاء</label>
                                    <input name="card_expiry" inputmode="numeric" placeholder="MM/YY" autocomplete="cc-exp" required>
                                </div>
                                <div>
                                    <label>CVV</label>
                                    <input name="card_cvc" inputmode="numeric" autocomplete="cc-csc" required>
                                </div>
                            </div>
                            <button class="submit-card" type="submit">دفع واعتماد الطلب</button>
                        </form>
                    </div>
                </div>
            @endif
        </section>
    </main>
    <script>
        document.querySelectorAll('[data-local-datetime]').forEach((element) => {
            const date = new Date(element.dataset.localDatetime);
            if (Number.isNaN(date.getTime())) return;

            element.textContent = new Intl.DateTimeFormat('ar-SA-u-ca-gregory', {
                weekday: 'long',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            }).format(date).replace('،', ' -');
        });

        const paymentRules = [
            { selector: 'input[name="card_number"]', pattern: /^[0-9 ]+$/, message: 'تنبيه: رقم البطاقة يقبل الأرقام الإنجليزية والمسافات فقط.' },
            { selector: 'input[name="card_expiry"]', pattern: /^(0[1-9]|1[0-2])\/[0-9]{2}$/, message: 'تنبيه: اكتب تاريخ الانتهاء بالأرقام الإنجليزية بصيغة MM/YY.' },
            { selector: 'input[name="card_cvc"]', pattern: /^[0-9]+$/, message: 'تنبيه: لا يقبل هذا الحقل إلا الأرقام الإنجليزية فقط 0-9.' },
        ];

        document.querySelectorAll(paymentRules.map((rule) => rule.selector).join(', ')).forEach((input) => {
            const showWarning = () => {
                const rule = paymentRules.find((item) => input.matches(item.selector));
                if (!rule) return;

                let warning = input.nextElementSibling;
                if (!warning || !warning.classList.contains('english-number-warning')) {
                    warning = document.createElement('div');
                    warning.className = 'english-number-warning';
                    input.insertAdjacentElement('afterend', warning);
                }

                const invalid = input.value !== '' && !rule.pattern.test(input.value);
                warning.textContent = rule.message;
                warning.classList.toggle('active', invalid);
                input.setCustomValidity(invalid ? rule.message : '');
            };

            input.addEventListener('input', showWarning);
            showWarning();
        });
    </script>
</body>
</html>
