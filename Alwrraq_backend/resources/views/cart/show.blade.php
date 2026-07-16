<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .mobile-menu-toggle { display: none; align-items: center; justify-content: center; gap: 7px; border: 1px solid rgba(148, 163, 184, 0.28); border-radius: 10px; background: rgba(255, 255, 255, 0.08); color: #ffffff; padding: 9px 11px; font-weight: 900; font-family: inherit; cursor: pointer; }
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
        .delivery-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .delivery-option { display: flex; align-items: flex-start; gap: 8px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-weight: 800; line-height: 1.5; }
        .delivery-option small { display: block; color: #64748b; font-weight: 700; margin-top: 3px; }
        .delivery-fields { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
        .delivery-fields.address-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .delivery-note { margin-top: 10px; padding: 10px 12px; border-radius: 10px; background: #ecfdf5; color: #047857; font-weight: 900; line-height: 1.6; }
        .discount-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: end; }
        .discount-form label { display: block; color: #9d174d; font-size: 13px; font-weight: 900; margin-bottom: 6px; }
        .discount-form input { width: 100%; padding: 12px; border: 1px solid #f9a8d4; border-radius: 9px; background: #ffffff; color: #111827; font-size: 16px; font-weight: 800; }
        .discount-button { padding: 12px 16px; border: 0; border-radius: 9px; background: #db2777; color: #ffffff; font-weight: 900; cursor: pointer; }
        .discount-button:hover { background: #be185d; }
        .payment-summary { display: grid; gap: 12px; margin-top: 18px; }
        .summary-item { padding: 14px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; }
        .summary-item span { display: block; margin-bottom: 7px; color: #64748b; font-size: 13px; font-weight: 900; }
        .summary-item strong { display: block; color: #0f172a; font-size: 18px; font-weight: 900; line-height: 1.7; }
        .summary-item.total-before strong { color: #92400e; }
        .summary-item.total-after { background: #0f172a; border-color: #0f172a; color: #ffffff; }
        .summary-item.total-after span { color: #cbd5e1; }
        .summary-item.total-after strong { color: #ffffff; font-size: 24px; }
        .summary-files { display: grid; gap: 7px; margin: 0; padding: 0; list-style: none; }
        .summary-files li { padding: 9px 10px; border: 1px solid #e2e8f0; border-radius: 9px; background: #ffffff; color: #0f172a; font-size: 14px; font-weight: 900; line-height: 1.7; word-break: break-word; }
        @media (max-width: 820px) {
            :root { --sidebar-width: 0px; --page-gap: 10px; }
            body { padding: 0; }
            .header { position: sticky; top: 0; width: 100%; min-height: 0; max-height: none; padding: 12px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16); }
            .header-inner { height: auto; display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 10px; }
            .mobile-menu-toggle { display: inline-flex; }
            .header-actions { grid-column: 1 / -1; margin-top: 0; display: none; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .header.menu-open .header-actions { display: grid; }
            .header-user { grid-column: 1 / -1; margin: 0; }
            main { width: calc(100% - 20px); margin: 14px auto 24px; padding: 0; }
            .payment-options { grid-template-columns: 1fr; align-items: stretch; }
            .meta, .totals, .form-grid, .delivery-options, .delivery-fields, .delivery-fields.address-fields, .discount-form { grid-template-columns: 1fr; }
            .meta-card.full { grid-column: auto; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .home-button, .settings-button, .logout-button, .submit-card, .apple-pay, .close-to-orders { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="brand">الورّاق</div>
            <button class="mobile-menu-toggle" type="button" onclick="toggleMobileHeader(this, event)" aria-expanded="false">☰ القائمة</button>
            <div class="header-actions">
                <span class="header-user">👤 {{ auth()->user()->name }}</span>
                <a class="home-button" href="{{ route('home') }}">🏠 الصفحة الرئيسية</a>
                <a class="home-button" href="{{ route('orders.index') }}">🧾 طلباتي</a>
                <a class="settings-button" href="{{ route('account.settings') }}">⚙️ إعداداتي</a>
                <form class="header-form" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">🚪 خروج</button>
                </form>
                @include('shared.language-switcher')
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
                    'notes' => 'طباعة المذكرات وملفات ال PDF',
                    'books' => 'طباعة وتجليد كتب كعب جلد طبيعي',
                    'color_printing' => 'طباعة الملفات بالألوان',
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
                $printSideNames = [
                    'one_side' => 'وجه واحد',
                    'two_sides' => 'وجهين',
                ];
                $pageSizeNames = [
                    'A4' => 'A4',
                    'A3' => 'A3',
                    'A5' => 'A5',
                    'B5' => 'B5',
                ];
                $paperColorNames = [
                    'white' => 'أبيض',
                    'yellow' => 'أصفر',
                ];
                $bindingNames = $order->service_type === 'books'
                    ? [
                        'tape' => 'تجليد كعب جلد طبيعي',
                        'wire' => 'تجليد كعب جلد طبيعي',
                        'normal' => 'تجليد كعب جلد طبيعي',
                        'none' => 'تجليد كعب جلد طبيعي',
                    ]
                    : ($order->service_type === 'color_printing'
                        ? [
                            'tape' => 'تغليف دبوس',
                            'wire' => 'تغليف سلك',
                            'normal' => 'تغليف عادي',
                            'thermal' => 'تغليف حراري',
                            'none' => 'بدون تغليف',
                        ]
                    : [
                        'tape' => $order->service_type === 'notes' ? 'تغليف دبوس' : 'تجليد دبوس',
                        'wire' => $order->service_type === 'notes' ? 'تغليف سلك' : 'تجليد سلك',
                        'normal' => $order->service_type === 'notes' ? 'تغليف عادي' : 'تجليد عادي',
                        'none' => $order->service_type === 'notes' ? 'بدون تغليف' : 'بدون تجليد',
                    ]);
                $bindingLabel = match ($order->service_type) {
                    'books' => 'التجليد',
                    'color_printing' => 'التغليف',
                    'notes' => 'التغليف',
                    'formatting' => 'التنسيق',
                    'research' => 'إنشاء البحث',
                    default => 'التجليد',
                };
                $bindingPriceLabel = match ($order->service_type) {
                    'books' => 'سعر التجليد',
                    'color_printing' => 'سعر التغليف',
                    'notes' => 'سعر التغليف',
                    'formatting' => 'سعر التنسيق',
                    'research' => 'سعر إنشاء البحث',
                    default => 'سعر التجليد',
                };
                $statusNames = [
                    'new' => 'بانتظار الدفع',
                    'reviewing' => 'قيد المراجعة',
                    'priced' => 'تم التسعير',
                    'processing' => 'قيد التنفيذ',
                    'completed' => 'مكتمل',
                    'cancelled' => 'ملغي',
                ];
                $deliveryMethodNames = [
                    'branch_pickup' => 'استلام من الفرع',
                    'islamic_university_delivery' => 'توصيل داخل الجامعة الإسلامية',
                    'madinah_delivery' => 'توصيل داخل المدينة المنورة',
                    'redbox_delivery' => 'خارج المدينة المنورة عبر RedBox',
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
                if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true) && $order->files->contains(fn ($file) => blank($file->binding_type))) {
                    $missingRequirements->push('اختيار نوع التغليف لكل ملف.');
                }
                $academicPdfFiles = in_array($order->service_type, ['thesis', 'phd'], true)
                    ? $order->files->where('file_type', 'pdf')
                    : collect();
                if (in_array($order->service_type, ['thesis', 'phd'], true) && $academicPdfFiles->isEmpty()) {
                    $missingRequirements->push('رفع ملف PDF.');
                }
                if (in_array($order->service_type, ['thesis', 'phd'], true) && $academicPdfFiles->contains(fn ($file) => blank($file->cover_color) || blank($file->writing_color))) {
                    $missingRequirements->push('اختيار لون الرسالة ولون الكتابة لكل ملف PDF.');
                }
                if ($order->service_type === 'thesis' && $academicPdfFiles->contains(fn ($file) => blank($file->thesis_project_type))) {
                    $missingRequirements->push('اختيار نوع مشروع الرسالة لكل ملف PDF.');
                }
                $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                $createdAtText = $dayNames[$order->created_at->dayOfWeek] . ' - ' . $order->created_at->format('Y-m-d H:i');
                $isPaid = $order->payment_status === 'paid';
                $isCompleted = $isPaid && in_array($order->status, ['completed', 'finished'], true);
                $displayStatus = $isCompleted
                    ? 'مكتمل'
                    : (in_array($order->status, ['completed', 'finished'], true) ? 'بانتظار الدفع' : ($statusNames[$order->status] ?? $order->status));
                $totalBeforeDiscount = $order->baseTotal() + (float) $order->delivery_fee;
                $totalAfterDiscount = (float) $order->grand_total;
            @endphp
            <div class="payment-summary">
                <div class="summary-item"><span>رقم الطلب</span><strong>#{{ $order->id }}</strong></div>
                <div class="summary-item"><span>تاريخ إنشاء الطلب</span><strong data-local-datetime="{{ $order->created_at->toIso8601String() }}">{{ $createdAtText }}</strong></div>
                <div class="summary-item">
                    <span>اسم الملف</span>
                    <ul class="summary-files">
                        @foreach ($order->files as $file)
                            <li>{{ $file->original_name }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="summary-item total-before"><span>المبلغ الإجمالي قبل الخصم</span><strong>{{ $totalBeforeDiscount }} ريال</strong></div>
                <div class="summary-item total-after"><span>المبلغ الإجمالي بعد الخصم</span><strong>{{ $totalAfterDiscount }} ريال</strong></div>
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
        function toggleMobileHeader(button, event) {
            event?.stopPropagation();
            const header = button.closest('.header');
            const isOpen = header.classList.toggle('menu-open');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        document.addEventListener('click', (event) => {
            const header = document.querySelector('.header.menu-open');
            if (!header || header.contains(event.target)) return;

            header.classList.remove('menu-open');
            header.querySelector('.mobile-menu-toggle')?.setAttribute('aria-expanded', 'false');
        });

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
    @include('shared.chat-widget')
    @include('shared.language-tools')
</body>
</html>
