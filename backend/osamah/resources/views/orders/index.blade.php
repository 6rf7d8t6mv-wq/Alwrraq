<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>طلباتي</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(118px, 18vw, 220px); --page-gap: clamp(10px, 3vw, 28px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #ffffff; padding: clamp(14px, 2vw, 22px) clamp(8px, 1.5vw, 16px); position: fixed; top: 0; right: 0; z-index: 20; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 16px; }
        .brand { font-size: clamp(19px, 4vw, 22px); font-weight: 900; }
        .home-button { color: #0f172a; background: #ffffff; text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 9px; text-align: center; line-height: 1.6; }
        main { width: min(1040px, 100%); margin: clamp(16px, 4vw, 28px) auto; padding: 0 clamp(12px, 4vw, 20px); }
        .panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: clamp(16px, 4vw, 22px); box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
        .page-title { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 18px; }
        h1 { margin: 0 0 8px; font-size: clamp(24px, 6vw, 30px); }
        p { margin: 0; color: #64748b; line-height: 1.7; }
        table { width: 100%; border-collapse: collapse; overflow: hidden; border-radius: 10px; }
        th, td { text-align: right; padding: 13px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; vertical-align: middle; }
        th { background: #f8fafc; color: #334155; font-weight: 900; }
        .badge { display: inline-flex; align-items: center; justify-content: center; padding: 6px 10px; border-radius: 999px; font-weight: 900; font-size: 12px; white-space: nowrap; }
        .service-title { font-weight: 900; color: #0f172a; margin-bottom: 4px; }
        .service-detail { color: #64748b; font-size: 12px; line-height: 1.5; }
        .paid { background: #dcfce7; color: #166534; }
        .unpaid { background: #fef3c7; color: #92400e; }
        .done { background: #e0f2fe; color: #075985; }
        .open { background: #f1f5f9; color: #334155; }
        .cancelled { background: #fee2e2; color: #991b1b; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .action { display: inline-flex; color: #ffffff; background: #0f172a; text-decoration: none; font-weight: 900; padding: 9px 12px; border-radius: 8px; border: 0; cursor: pointer; font-family: inherit; font-size: 13px; }
        .action.secondary { background: #047857; }
        .action.ghost { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .action.danger { background: #b91c1c; }
        .inline-form { display: inline-flex; margin: 0; }
        .empty { text-align: center; color: #94a3b8; padding: 38px 16px; font-weight: 800; }
        .notice { margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; background: #ecfdf5; color: #047857; font-weight: 900; }
        .errors { margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-weight: 900; }
        .missing-info { margin: 12px 0 0; padding: 12px 14px; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; border-radius: 10px; font-weight: 900; line-height: 1.8; }
        .missing-info ul { margin: 8px 0 0; padding: 0 18px 0 0; }
        .modal-backdrop { position: fixed; inset: 0; display: none; align-items: flex-start; justify-content: center; padding: clamp(10px, 3vw, 24px) clamp(8px, 3vw, 20px); background: rgba(15, 23, 42, 0.58); z-index: 50; overflow-y: auto; }
        .modal-backdrop.active { display: flex; }
        .modal { width: min(1180px, 100%); max-height: calc(100vh - 20px); margin: auto 0; background: #ffffff; border-radius: 14px; box-shadow: 0 24px 80px rgba(15, 23, 42, 0.30); border: 1px solid rgba(226, 232, 240, 0.9); overflow: hidden; display: flex; flex-direction: column; }
        .modal-head { display: flex; justify-content: space-between; align-items: center; gap: 14px; padding: clamp(12px, 3vw, 16px) clamp(14px, 3vw, 18px); background: #0f172a; color: #ffffff; }
        .modal-title { font-size: clamp(16px, 4vw, 18px); font-weight: 900; }
        .modal-close { border: 1px solid #64748b; background: transparent; color: #ffffff; border-radius: 8px; padding: 7px 11px; cursor: pointer; font-weight: 900; }
        .modal-body { padding: clamp(12px, 3vw, 18px); overflow-y: auto; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
        .detail-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
        .detail-card.full { grid-column: 1 / -1; }
        .detail-card span { display: block; color: #64748b; font-size: 12px; font-weight: 900; margin-bottom: 6px; }
        .detail-card strong { color: #0f172a; font-size: 15px; line-height: 1.6; }
        .files-panel { width: 100%; margin-top: 16px; padding: 16px; box-sizing: border-box; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .files-title { margin: 0 0 12px; font-size: 19px; color: #0f172a; }
        .detail-table-wrap { width: 100%; overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; }
        .detail-table-wrap table { width: 100%; min-width: 720px; table-layout: auto; }
        .detail-table-wrap th,
        .detail-table-wrap td { padding: 11px 10px; white-space: normal; }
        .detail-table-wrap td:first-child { min-width: 180px; word-break: break-word; }
        .price-cell { color: #0f172a; font-weight: 900; white-space: nowrap; background: #f8fafc; }
        .totals-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .total-card { background: #0f172a; color: #ffffff; border-radius: 10px; padding: 16px; }
        .total-card span { display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 8px; }
        .total-card strong { font-size: 24px; }
        .modal-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        @media (max-width: 820px) {
            :root { --sidebar-width: 112px; --page-gap: 8px; }
            .header { padding: 14px 7px; }
            .page-title { align-items: flex-start; flex-direction: column; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .detail-grid, .totals-grid { grid-template-columns: 1fr; }
            .home-button, .action, .inline-form { width: 100%; justify-content: center; text-align: center; }
            .modal-actions { justify-content: stretch; }
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
            <div class="page-title">
                <div>
                    <h1>طلباتي</h1>
                    <p>تابع حالة طلباتك، الدفع، وهل اكتمل الطلب أو ما زال قيد التنفيذ.</p>
                </div>
            </div>

            @if ($orders->isEmpty())
                <div class="empty">لا توجد طلبات حتى الآن.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الخدمة</th>
                            <th>الملفات</th>
                            <th>حالة الدفع</th>
                            <th>حالة الطلب</th>
                            <th>الإجمالي</th>
                            <th>التاريخ</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $serviceNames = [
                                    'notes' => 'مذكرات',
                                    'thesis' => 'ماجستير',
                                    'phd' => 'دكتوراه',
                                ];
                                $projectNames = [
                                    'thesis' => 'رسالة ماجستير',
                                    'supplementary' => 'بحث تكميلي',
                                    'graduation' => 'بحث تخرج',
                                ];
                                $bindingNames = [
                                    'tape' => 'تغليف دبوس',
                                    'wire' => 'تغليف سلك',
                                    'normal' => 'تغليف عادي',
                                    'none' => 'بدون تغليف',
                                ];
                                $statusNames = [
                                    'new' => 'بانتظار الدفع',
                                    'reviewing' => 'قيد المراجعة',
                                    'priced' => 'تم التسعير',
                                    'processing' => 'قيد التنفيذ',
                                    'completed' => 'مكتمل',
                                    'cancelled' => 'ملغي',
                                ];
                                $isPaid = $order->payment_status === 'paid';
                                $isCompleted = $order->status === 'completed';
                                $isCancelled = $order->status === 'cancelled';
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
                                $serviceDetail = $order->service_type === 'thesis' && $projectTypes->isNotEmpty()
                                    ? $projectTypes->implode('، ')
                                    : 'اضغط عرض الطلب للتفاصيل';
                            @endphp
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>
                                    <div class="service-title">{{ $serviceNames[$order->service_type] ?? $order->service_type }}</div>
                                    <div class="service-detail">{{ $serviceDetail }}</div>
                                </td>
                                <td>{{ $order->files_count }}</td>
                                <td>
                                    <span class="badge {{ $isPaid ? 'paid' : 'unpaid' }}">
                                        {{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $isCompleted ? 'done' : ($isCancelled ? 'cancelled' : 'open') }}">
                                        {{ $statusNames[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td>{{ $order->grand_total }} ريال</td>
                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="actions">
                                        <button class="action ghost" type="button" onclick="openOrderModal('orderModal{{ $order->id }}')">عرض الطلب</button>
                                        @if (! $isPaid && $order->files_count > 0)
                                            <a class="action secondary" href="{{ route('cart.show', $order) }}">إكمال الدفع</a>
                                        @else
                                            <a class="action" href="{{ route('cart.show', $order) }}">السلة</a>
                                        @endif
                                        @if (! $isPaid)
                                            <form class="inline-form" method="post" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('هل تريد حذف هذا الطلب وجميع ملفاته؟')">
                                                @csrf
                                                @method('delete')
                                                <button class="action danger" type="submit">حذف</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @foreach ($orders as $order)
                    @php
                        $serviceNames = [
                            'notes' => 'مذكرات',
                            'thesis' => 'ماجستير',
                            'phd' => 'دكتوراه',
                        ];
                        $serviceFullNames = [
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
                    <div class="modal-backdrop" id="orderModal{{ $order->id }}" tabindex="-1" onclick="closeOrderModal(event, 'orderModal{{ $order->id }}')">
                        <div class="modal" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
                            <div class="modal-head">
                                <div class="modal-title">تفاصيل الطلب #{{ $order->id }}</div>
                                <button class="modal-close" type="button" onclick="closeOrderModal(null, 'orderModal{{ $order->id }}')">إغلاق</button>
                            </div>
                            <div class="modal-body">
                                <div class="detail-grid">
                                    <div class="detail-card full"><span>الخدمة</span><strong>{{ $serviceFullNames[$order->service_type] ?? $order->service_type }}</strong></div>
                                    @if ($projectTypes->isNotEmpty())
                                        <div class="detail-card full"><span>تفصيل مشروع الرسالة</span><strong>{{ $projectTypes->implode('، ') }}</strong></div>
                                    @endif
                                    <div class="detail-card"><span>حالة الطلب</span><strong>{{ $statusNames[$order->status] ?? $order->status }}</strong></div>
                                    <div class="detail-card"><span>الدفع</span><strong>{{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}</strong></div>
                                    <div class="detail-card"><span>تاريخ الطلب</span><strong>{{ $order->created_at->format('Y-m-d H:i') }}</strong></div>
                                </div>
                                @if ($missingRequirements->isNotEmpty())
                                    <div class="missing-info">
                                        معلومات مطلوبة قبل الدفع:
                                        <ul>
                                            @foreach ($missingRequirements as $requirement)
                                                <li>{{ $requirement }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="files-panel">
                                    <h2 class="files-title">الملفات والتفاصيل والأسعار</h2>
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

                                <div class="totals-grid">
                                    <div class="total-card"><span>سعر الطباعة</span><strong>{{ $order->print_total }} ريال</strong></div>
                                    <div class="total-card"><span>{{ $bindingPriceLabel }}</span><strong>{{ $order->binding_total }} ريال</strong></div>
                                    <div class="total-card"><span>الإجمالي</span><strong>{{ $order->grand_total }} ريال</strong></div>
                                </div>

                                <div class="modal-actions">
                                    @if ($order->payment_status !== 'paid' && $order->files_count > 0)
                                        <a class="action secondary" href="{{ route('cart.show', $order) }}">إكمال الدفع</a>
                                    @endif
                                    @if ($order->payment_status !== 'paid')
                                        <form class="inline-form" method="post" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('هل تريد حذف هذا الطلب وجميع ملفاته؟')">
                                            @csrf
                                            @method('delete')
                                            <button class="action danger" type="submit">حذف الطلب</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </section>
    </main>
    <script>
        function openOrderModal(id) {
            const modal = document.getElementById(id);
            modal?.classList.add('active');
            modal?.focus();
            document.body.style.overflow = 'hidden';
        }

        function closeOrderModal(event, id) {
            if (event && event.target.id !== id) return;
            document.getElementById(id)?.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop.active').forEach((modal) => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
            }
        });
    </script>
</body>
</html>
