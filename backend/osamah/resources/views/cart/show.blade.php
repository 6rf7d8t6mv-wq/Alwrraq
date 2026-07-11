<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>السلة والدفع</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(118px, 18vw, 220px); --page-gap: clamp(10px, 3vw, 28px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #ffffff; padding: clamp(14px, 2vw, 22px) clamp(8px, 1.5vw, 16px); position: fixed; top: 0; right: 0; z-index: 20; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 16px; }
        .brand { font-size: clamp(19px, 4vw, 22px); font-weight: 900; }
        .home-button { color: #0f172a; background: #ffffff; text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 9px; text-align: center; line-height: 1.6; }
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
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .full { grid-column: 1 / -1; }
        .submit-card { width: 100%; margin-top: 16px; padding: 13px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 900; cursor: pointer; }
        .paid { background: #ecfdf5; color: #047857; padding: 14px; border-radius: 10px; font-weight: 900; }
        .missing-info { margin-top: 16px; padding: 14px 16px; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; border-radius: 10px; font-weight: 900; line-height: 1.8; }
        .missing-info ul { margin: 8px 0 0; padding: 0 18px 0 0; }
        @media (max-width: 820px) {
            :root { --sidebar-width: 112px; --page-gap: 8px; }
            .header { padding: 14px 7px; }
            .payment-options { grid-template-columns: 1fr; align-items: stretch; }
            .meta, .totals, .form-grid { grid-template-columns: 1fr; }
            .meta-card.full { grid-column: auto; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .home-button, .submit-card, .apple-pay { width: 100%; text-align: center; }
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

        <section class="panel">
            <h1>السلة والدفع</h1>
            <p>راجع الطلب قبل الدفع. لا يوجد دفع عند الاستلام أو كاش.</p>
            @php
                $serviceNames = [
                    'notes' => 'طباعة وتغليف المذكرات',
                    'thesis' => 'طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج',
                    'phd' => 'طباعة وتجليد رسالة دكتوراه',
                ];
                $projectNames = [
                    'thesis' => 'رسالة ماجستير',
                    'supplementary' => 'بحث تكميلي',
                    'graduation' => 'بحث تخرج',
                ];
                $bindingNames = [
                    'tape' => $order->service_type === 'notes' ? 'تغليف دبوس' : 'تجليد دبوس',
                    'wire' => $order->service_type === 'notes' ? 'تغليف سلك' : 'تجليد سلك',
                    'normal' => $order->service_type === 'notes' ? 'تغليف عادي' : 'تجليد عادي',
                    'none' => $order->service_type === 'notes' ? 'بدون تغليف' : 'بدون تجليد',
                ];
                $bindingLabel = $order->service_type === 'notes' ? 'التغليف' : 'التجليد';
                $bindingPriceLabel = $order->service_type === 'notes' ? 'سعر التغليف' : 'سعر التجليد';
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
                if (in_array($order->service_type, ['thesis', 'phd'], true) && $order->files->contains(fn ($file) => blank($file->university_name))) {
                    $missingRequirements->push('اختيار الجامعة أو المعهد لكل ملف.');
                }
                if ($order->service_type === 'thesis' && $order->files->contains(fn ($file) => $file->file_type === 'pdf' && blank($file->thesis_project_type))) {
                    $missingRequirements->push('اختيار نوع مشروع الرسالة لكل ملف PDF.');
                }
            @endphp
            <div class="meta">
                <div class="meta-card"><span>رقم الطلب</span><strong>#{{ $order->id }}</strong></div>
                <div class="meta-card"><span>حالة الطلب</span><strong>{{ $statusNames[$order->status] ?? $order->status }}</strong></div>
                <div class="meta-card"><span>الدفع</span><strong>{{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}</strong></div>
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
                                <th>النوع</th>
                                @if ($order->service_type === 'thesis')
                                    <th>مشروع الرسالة</th>
                                @endif
                                @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                    <th>الجامعة/المعهد</th>
                                @endif
                                <th>الصفحات</th>
                                <th>النسخ</th>
                                <th>{{ $bindingLabel }}</th>
                                <th>سعر الطباعة</th>
                                <th>{{ $bindingPriceLabel }}</th>
                                <th>إجمالي الملف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->files as $file)
                                <tr>
                                    <td>{{ $file->original_name }}</td>
                                    <td>{{ strtoupper($file->file_type) }}</td>
                                    @if ($order->service_type === 'thesis')
                                        <td>{{ $projectNames[$file->thesis_project_type] ?? '-' }}</td>
                                    @endif
                                    @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                        <td>{{ $file->university_name ?: '-' }}</td>
                                    @endif
                                    <td>{{ $file->pages }}</td>
                                    <td>{{ $file->copies }}</td>
                                    <td>{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                                    <td class="price-cell">{{ $file->print_price }} ريال</td>
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
                <div class="total-card"><span>سعر الطباعة</span><strong>{{ $order->print_total }} ريال</strong></div>
                <div class="total-card"><span>{{ $bindingPriceLabel }}</span><strong>{{ $order->binding_total }} ريال</strong></div>
                <div class="total-card"><span>الإجمالي</span><strong>{{ $order->grand_total }} ريال</strong></div>
            </div>
        </section>

        <section class="panel">
            <h2>الدفع</h2>
            @if ($order->payment_status === 'paid')
                <div class="paid">تم الدفع بنجاح. رقم العملية: {{ $order->payment_reference }}</div>
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
                                    <input name="card_expiry" placeholder="MM/YY" autocomplete="cc-exp" required>
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
</body>
</html>
