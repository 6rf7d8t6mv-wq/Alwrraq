@extends('admin.layout')

@section('title', 'الطلبات - لوحة المدير')

@section('content')
    <style>
        .orders-page-title { margin-bottom: 9px; }
        .order-filter-bar { margin-bottom: 9px; }
        .orders-search-panel { margin-bottom: 9px; padding: 11px 13px; }
        .orders-customer-card { margin-bottom: 9px; }
        .orders-customer-card .order-head { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 7px; padding: 9px; }
        .orders-customer-card .order-head > div { display: flex; align-items: center; justify-content: space-between; gap: 5px; min-width: 0; min-height: 42px; padding: 7px 8px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; font-size: 10px; line-height: 1.25; }
        .orders-customer-card .order-head .label { flex: 0 0 auto; min-width: 0; margin: 0; font-size: 8.5px; line-height: 1.2; white-space: nowrap; word-break: normal; }
        .order-summary-value { display: -webkit-box; flex: 1 1 auto; min-width: 0; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; text-align: left; white-space: normal; word-break: normal; overflow-wrap: normal; }
        .orders-customer-card .summary-action { align-items: center; justify-content: center; }
        .orders-customer-card .summary-action .small-button { width: auto; min-width: 62px; min-height: 28px; margin: 0; padding: 5px 7px; font-size: 9px; }
        #adminModalBody .panel[data-order-id] { padding: 9px; border: 1px solid #dbe5f1; border-inline-start: 4px solid #2563eb; border-radius: 10px; box-shadow: 0 5px 14px rgba(37, 99, 235, 0.07); }
        #adminModalBody .panel[data-order-id] > .order-head { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; padding: 6px; border: 0; border-radius: 9px; background: #f8fafc; }
        #adminModalBody .panel[data-order-id] > .order-head > div { min-width: 0; min-height: 42px; padding: 6px 7px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; font-size: 9px; line-height: 1.3; word-break: normal; overflow-wrap: normal; }
        #adminModalBody .panel[data-order-id] > .order-head .label { margin-bottom: 3px; font-size: 8px; line-height: 1.2; }
        #adminModalBody .panel[data-order-id] .badge { padding: 2px 5px; font-size: 8px; }
        #adminModalBody .panel[data-order-id] .compact-actions { gap: 4px; }
        #adminModalBody .panel[data-order-id] .compact-actions button { width: auto; min-width: 50px; margin: 0; padding: 4px 6px; font-size: 8px; }
        #adminModalBody .panel[data-order-id] .order-files-cards { display: grid; gap: 7px; margin-bottom: 7px; }
        #adminModalBody .panel[data-order-id] .order-file-card { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 5px; padding: 6px; border-radius: 9px; }
        #adminModalBody .panel[data-order-id] .order-file-field,
        #adminModalBody .panel[data-order-id] .order-file-field:nth-child(3n) { display: flex; align-items: center; justify-content: space-between; gap: 4px; min-width: 0; min-height: 38px; padding: 5px 6px; border: 1px solid #edf2f7; border-radius: 7px; background: #f8fafc; }
        #adminModalBody .panel[data-order-id] .order-file-field.file-name,
        #adminModalBody .panel[data-order-id] .order-file-field.actions-field { grid-column: 1 / -1; }
        #adminModalBody .panel[data-order-id] .order-file-field span { flex: 0 1 43%; min-width: 0; margin: 0; font-size: 7.5px; line-height: 1.2; word-break: normal; overflow-wrap: normal; }
        #adminModalBody .panel[data-order-id] .order-file-field strong { display: -webkit-box; flex: 0 1 57%; min-width: 0; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; font-size: 8.5px; line-height: 1.25; text-align: left; word-break: normal; overflow-wrap: normal; }
        #adminModalBody .panel[data-order-id] .order-file-field.file-name strong { font-size: 9px; }
        #adminModalBody .panel[data-order-id] .order-file-field.file-name:not(.product-name-field) strong { overflow-wrap: anywhere; }
        #adminModalBody .panel[data-order-id] .product-order-card { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        #adminModalBody .panel[data-order-id] .order-file-field.product-name-field { grid-column: 1 / -1; align-items: center; justify-content: flex-start; min-height: 42px; }
        #adminModalBody .panel[data-order-id] .product-name-field > span { flex: 0 0 auto; white-space: nowrap; }
        #adminModalBody .panel[data-order-id] .ordered-product-main { display: flex; flex: 1 1 auto; align-items: center; justify-content: flex-start; gap: 5px; min-width: 0; }
        #adminModalBody .panel[data-order-id] .ordered-product-image { flex: 0 0 34px; width: 34px; height: 34px; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 6px; background: #ffffff; }
        #adminModalBody .panel[data-order-id] .ordered-product-image-placeholder { display: grid; place-items: center; color: #94a3b8; font-size: 15px; }
        #adminModalBody .panel[data-order-id] .product-name-field .ordered-product-main strong { flex: 1 1 auto; text-align: right; white-space: nowrap; word-break: normal; overflow-wrap: normal; text-overflow: ellipsis; }
        #adminModalBody .panel[data-order-id] .product-total-field { grid-column: span 2; }
        #adminModalBody .panel[data-order-id] .academic-university-field { grid-column: span 2; }
        #adminModalBody .panel[data-order-id] .academic-university-field span { flex: 0 0 auto; white-space: nowrap; }
        #adminModalBody .panel[data-order-id] .academic-university-field strong { flex: 1 1 auto; text-align: right; word-break: normal; overflow-wrap: normal; }
        #adminModalBody .panel[data-order-id] .file-action-buttons { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; min-width: 0; width: min(210px, 100%); }
        #adminModalBody .panel[data-order-id] .file-action-button { min-height: 27px; padding: 4px 6px; border-radius: 6px; font-size: 8px; }
        #adminModalBody .panel[data-order-id] .delivered-files-section { margin: 0; padding: 7px; border-radius: 9px; background: #f8fafc; }
        #adminModalBody .panel[data-order-id] .delivered-files-section h2 { margin: 0 0 6px; font-size: 11px; line-height: 1.3; }
        #adminModalBody .panel[data-order-id] .delivered-files-list { gap: 5px; margin: 5px 0 7px; }
        #adminModalBody .panel[data-order-id] .delivered-file-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 6px; padding: 5px 6px; border-radius: 7px; }
        #adminModalBody .panel[data-order-id] .delivered-file-name { font-size: 8.5px; line-height: 1.25; }
        #adminModalBody .panel[data-order-id] .delivered-file-item .muted { font-size: 7px; line-height: 1.2; }
        #adminModalBody .panel[data-order-id] .delivered-file-actions { width: auto; gap: 3px; flex-wrap: nowrap; }
        #adminModalBody .panel[data-order-id] .delivered-file-actions .ghost,
        #adminModalBody .panel[data-order-id] .delivered-file-actions .save,
        #adminModalBody .panel[data-order-id] .delivered-file-actions .danger { min-width: 42px; min-height: 25px; margin: 0; padding: 4px 5px; border-radius: 6px; font-size: 7.5px; }
        #adminModalBody .panel[data-order-id] .delivered-file-actions form { margin: 0; }
        #adminModalBody .panel[data-order-id] .delivered-files-empty { margin: 0 0 6px; font-size: 8px; line-height: 1.35; }
        #adminModalBody .panel[data-order-id] .delivered-upload-form { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 5px; margin: 0; }
        #adminModalBody .panel[data-order-id] .delivered-upload-form label { margin: 0; font-size: 8px; white-space: nowrap; }
        #adminModalBody .panel[data-order-id] .delivered-upload-form input { width: 100%; min-width: 0; min-height: 28px; padding: 3px 4px; font-size: 7px; }
        #adminModalBody .panel[data-order-id] .delivered-upload-form .save { min-width: 58px; min-height: 28px; margin: 0; padding: 4px 6px; font-size: 8px; }
        @media (max-width: 980px) {
            .orders-page-title { margin-bottom: 6px; }
            .orders-page-title h1 { font-size: 20px; }
            .order-filter-bar { margin-bottom: 7px; }
            .orders-search-panel { margin-bottom: 7px; padding: 8px; }
            .orders-customer-card { margin-bottom: 7px; }
            .orders-customer-card .order-head { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; padding: 6px; }
            .orders-customer-card .order-head > div { min-height: 38px; padding: 5px 6px; border-radius: 7px; font-size: 8.5px; }
            .orders-customer-card .order-head .label { font-size: 7.5px; }
            .orders-customer-card .summary-action .small-button { min-width: 50px; min-height: 25px; padding: 4px 6px; font-size: 8px; }
            #adminModalBody .panel[data-order-id] { padding: 6px; }
            #adminModalBody .panel[data-order-id] > .order-head { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; padding: 5px; }
            #adminModalBody .panel[data-order-id] > .order-head > div { min-height: 38px; padding: 5px; font-size: 8px; }
            #adminModalBody .panel[data-order-id] > .order-head .label { font-size: 7px; }
            #adminModalBody .panel[data-order-id] .order-file-card { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 4px; padding: 5px; }
            #adminModalBody .panel[data-order-id] .order-file-field,
            #adminModalBody .panel[data-order-id] .order-file-field:nth-child(3n) { min-height: 35px; padding: 4px 5px; }
            #adminModalBody .panel[data-order-id] .order-file-field span { font-size: 7px; }
            #adminModalBody .panel[data-order-id] .order-file-field strong { font-size: 8px; }
            #adminModalBody .panel[data-order-id] .product-order-card { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            #adminModalBody .panel[data-order-id] .ordered-product-main { gap: 4px; }
            #adminModalBody .panel[data-order-id] .ordered-product-image { flex-basis: 28px; width: 28px; height: 28px; border-radius: 5px; }
            #adminModalBody .panel[data-order-id] .product-name-field .ordered-product-main strong { font-size: 7.5px; line-height: 1.15; }
        }
        @media (max-width: 560px) {
            .orders-page-title { margin-bottom: 4px; }
            .orders-page-title h1 { font-size: 17px; }
            .order-filter-bar { gap: 3px; margin-bottom: 5px; }
            .order-filter-button { min-height: 36px; padding: 4px 2px; border-width: 1px; border-radius: 7px; font-size: 8px; line-height: 1.2; }
            .orders-search-panel { margin-bottom: 5px; padding: 5px; }
            .orders-search-panel .search-form { gap: 4px; }
            .orders-search-panel label { margin-bottom: 3px; font-size: 8px; }
            .orders-search-panel input { padding: 6px; font-size: 16px; }
            .orders-customer-card { margin-bottom: 5px; }
            .orders-customer-card .order-head { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3px; padding: 4px; }
            .orders-customer-card .order-head > div { min-height: 34px; gap: 3px; padding: 4px; border-radius: 6px; font-size: 7.5px; }
            .orders-customer-card .order-head .label { font-size: 6.5px; }
            .orders-customer-card .summary-action .small-button { min-width: 45px; min-height: 23px; padding: 3px 4px; font-size: 7px; }
            #adminModalBody .panel[data-order-id] { padding: 4px; border-inline-start-width: 3px; }
            #adminModalBody .panel[data-order-id] > .order-head { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3px; padding: 3px; }
            #adminModalBody .panel[data-order-id] > .order-head > div { min-height: 34px; padding: 4px; border-radius: 6px; font-size: 7px; }
            #adminModalBody .panel[data-order-id] > .order-head .label { font-size: 6px; }
            #adminModalBody .panel[data-order-id] .order-files-cards { gap: 4px; margin-bottom: 4px; }
            #adminModalBody .panel[data-order-id] .order-file-card,
            #adminModalBody .panel[data-order-id] .product-order-card { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3px; padding: 4px; }
            #adminModalBody .panel[data-order-id] .order-file-field,
            #adminModalBody .panel[data-order-id] .order-file-field:nth-child(3n) { min-height: 32px; gap: 3px; padding: 3px 4px; border-radius: 6px; }
            #adminModalBody .panel[data-order-id] .order-file-field span { font-size: 6.3px; }
            #adminModalBody .panel[data-order-id] .order-file-field strong { font-size: 7.3px; }
            #adminModalBody .panel[data-order-id] .ordered-product-image { flex-basis: 25px; width: 25px; height: 25px; }
            #adminModalBody .panel[data-order-id] .product-name-field .ordered-product-main strong { font-size: 7px; }
            #adminModalBody .panel[data-order-id] .delivered-upload-form { grid-template-columns: minmax(0, 1fr) auto; }
            #adminModalBody .panel[data-order-id] .delivered-upload-form label { grid-column: 1 / -1; }
        }
    </style>

    <div class="page-title orders-page-title">
        <div>
            <h1>الطلبات</h1>
        </div>
    </div>

    <div class="order-filter-bar">
        <a class="order-filter-button red {{ $statusFilter === 'new' ? 'active' : '' }}" href="{{ route('admin.orders', array_filter(['status_filter' => 'new', 'search' => $search])) }}">الطلبات الجديدة</a>
        <a class="order-filter-button yellow {{ $statusFilter === 'in_progress' ? 'active' : '' }}" href="{{ route('admin.orders', array_filter(['status_filter' => 'in_progress', 'search' => $search])) }}">الطلبات قيد العمل</a>
        <a class="order-filter-button green {{ $statusFilter === 'completed' ? 'active' : '' }}" href="{{ route('admin.orders', array_filter(['status_filter' => 'completed', 'search' => $search])) }}">إجمالي الطلبات المكتملة</a>
    </div>

    <div class="panel orders-search-panel">
        <form class="search-form auto-search-form" method="get" action="{{ route('admin.orders') }}">
            @if ($statusFilter !== '')
                <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
            @endif
            <div style="flex: 1;">
                <label>ابحث برقم الطلب أو رقم الجوال أو اسم العميل</label>
                <input name="search" value="{{ $search }}" placeholder="مثال: 12 أو 0500000000 أو محمد">
            </div>
            @if ($search !== '')
                <a class="ghost" href="{{ route('admin.orders') }}">مسح</a>
            @endif
        </form>
    </div>

    @php
        $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $noPrintServices = ['formatting', 'research', 'stationery'];
        $serviceNames = [
            'notes' => 'مذكرات',
            'books' => 'كتب',
            'color_printing' => 'طباعة الملفات بالألوان',
            'thesis' => 'ماجستير',
            'phd' => 'دكتوراه',
            'formatting' => 'تنسيق الرسائل الجامعية',
            'research' => 'إنشاء بحث',
            'stationery' => 'القرطاسية',
        ];
        $customerGroups = $orders->groupBy('user_id');
    @endphp

    @forelse ($customerGroups as $customerOrders)
        @php
            $customer = $customerOrders->first()->user;
            $latestOrder = $customerOrders->first();
            $customerKey = 'customerOrders' . $customer->id;
            $createdAtText = $dayNames[$latestOrder->created_at->dayOfWeek] . ' - ' . $latestOrder->created_at->format('Y-m-d H:i');
            $servicesText = $customerOrders
                ->pluck('service_type')
                ->unique()
                ->map(fn ($service) => $serviceNames[$service] ?? $service)
                ->implode('، ');
            $paymentSummary = 'مدفوع ' . $customerOrders->where('payment_status', 'paid')->count()
                . ' / غير مدفوع ' . $customerOrders->where('payment_status', '!=', 'paid')->count();
        @endphp

        <div class="order orders-customer-card">
            <div class="order-head">
                <div><span class="label">العميل</span><span class="order-summary-value">{{ $customer->name }} - {{ $customer->phone }}</span></div>
                <div><span class="label">عدد الطلبات</span><span class="order-summary-value">{{ $customerOrders->count() }}</span></div>
                <div><span class="label">آخر طلب</span><span class="order-summary-value" data-local-datetime="{{ $latestOrder->created_at->toIso8601String() }}">{{ $createdAtText }}</span></div>
                <div><span class="label">نوع الخدمة</span><span class="order-summary-value">{{ $servicesText }}</span></div>
                <div><span class="label">حالة الدفع</span><span class="order-summary-value">{{ $paymentSummary }}</span></div>
                <div><span class="label">المبلغ</span><span class="order-summary-value">{{ $customerOrders->sum('grand_total') }} ريال</span></div>
                <div class="summary-action">
                    <button class="save small-button" type="button" onclick="openAdminModal('طلبات {{ $customer->name }}', '{{ $customerKey }}')">عرض الطلب</button>
                </div>
            </div>

            <template id="{{ $customerKey }}">
                @foreach ($customerOrders as $order)
                    @php
                        $bindingLabel = match ($order->service_type) {
                            'books' => 'التجليد',
                            'color_printing' => 'التغليف',
                            'notes' => 'التغليف',
                            'formatting' => 'التنسيق',
                            'research' => 'إنشاء البحث',
                            'stationery' => 'المنتجات',
                            default => 'التجليد',
                        };
                        $bindingPriceLabel = match ($order->service_type) {
                            'books' => 'سعر التجليد',
                            'color_printing' => 'سعر التغليف',
                            'notes' => 'سعر التغليف',
                            'formatting' => 'سعر التنسيق',
                            'research' => 'سعر إنشاء البحث',
                            'stationery' => 'إجمالي المنتجات',
                            default => 'سعر التجليد',
                        };
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
                        $deliveryMethodNames = [
                            'branch_pickup' => 'استلام من الفرع',
                            'islamic_university_delivery' => 'توصيل داخل الجامعة الإسلامية',
                            'madinah_delivery' => 'توصيل داخل المدينة المنورة',
                            'redbox_delivery' => 'خارج المدينة المنورة عبر RedBox',
                        ];
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
                        $isPaid = $order->payment_status === 'paid';
                        $isEffectivelyCompleted = $isPaid && in_array($order->status, ['completed', 'finished'], true);
                        $displayStatus = $isEffectivelyCompleted
                            ? 'مكتمل'
                            : (in_array($order->status, ['completed', 'finished'], true) ? 'بانتظار الدفع' : $order->status);
                        $orderDotColor = $isEffectivelyCompleted
                            ? 'green'
                            : (blank($order->admin_opened_at) ? 'red' : 'yellow');
                        $orderCreatedAtText = $dayNames[$order->created_at->dayOfWeek] . ' - ' . $order->created_at->format('Y-m-d H:i');
                    @endphp

                    <div class="panel order-detail-section" data-order-id="{{ $order->id }}" data-order-paid="{{ $isPaid ? '1' : '0' }}" data-open-order-url="{{ route('admin.orders.open', $order) }}" style="margin-bottom: 16px;">
                        <div class="order-head order-detail-section">
                            <div><span class="label">رقم الطلب</span><span class="tiny-status-dot {{ $orderDotColor }}" data-order-status-dot></span>#{{ $order->id }}</div>
                            <div><span class="label">العميل</span>{{ $order->user->name }} - {{ $order->user->phone }}</div>
                            <div><span class="label">تاريخ إنشاء الطلب</span><span data-local-datetime="{{ $order->created_at->toIso8601String() }}">{{ $orderCreatedAtText }}</span></div>
                            <div><span class="label">الخدمة</span>{{ $serviceNames[$order->service_type] ?? $order->service_type }}</div>
                            <div><span class="label">الحالة</span><span class="badge">{{ $displayStatus }}</span></div>
                            <div><span class="label">الدفع</span><span class="badge">{{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}</span>{{ $order->payment_method ? ' - ' . (['apple_pay' => 'Apple Pay', 'google_pay' => 'Google Pay', 'mada' => 'Mada', 'visa' => 'Visa', 'mastercard' => 'Mastercard', 'card' => 'بطاقة'][$order->payment_method] ?? $order->payment_method) : '' }}</div>
                            @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true))
                                <div><span class="label">التوصيل</span>
                                    {{ $deliveryMethodNames[$order->delivery_method] ?? '-' }}
                                    @if ($order->delivery_method === 'islamic_university_delivery')
                                        <br><span class="muted">وحدة {{ $order->delivery_unit }} / دور {{ $order->delivery_floor }} / غرفة {{ $order->delivery_room }}</span>
                                    @elseif (in_array($order->delivery_method, ['madinah_delivery', 'redbox_delivery'], true))
                                        <br><span class="muted">{{ $order->delivery_city }} / حي {{ $order->delivery_district }} / شارع {{ $order->delivery_street }}</span>
                                        @if ($order->delivery_map_url)
                                            <br><a class="muted" href="{{ $order->delivery_map_url }}">رابط الموقع</a>
                                        @endif
                                    @endif
                                </div>
                            @endif
                            <div><span class="label">الإجمالي</span>
                                @if (in_array($order->service_type, $noPrintServices, true))
                                    {{ $bindingLabel }} {{ $order->binding_total }} | الكل {{ $order->grand_total }} ريال
                                @else
                                    طباعة {{ $order->print_total }} | {{ $bindingLabel }} {{ $order->binding_total }} | توصيل {{ $order->delivery_fee }} | الكل {{ $order->grand_total }} ريال
                                @endif
                                @if ($order->discount_amount > 0)
                                    <br><span class="muted">خصم {{ $order->discount_code }}: {{ $order->discount_amount }} ريال</span>
                                @endif
                            </div>
                            @if (($order->payment_status === 'paid' && auth()->user()->hasAdminPermission('invoices_view')) || auth()->user()->hasAdminPermission('orders_delete'))
                                <div>
                                    <span class="label">الإجراءات</span>
                                    <div class="compact-actions">
                                        @if ($order->payment_status === 'paid' && auth()->user()->hasAdminPermission('invoices_view'))
                                            <button class="invoice-admin-button" type="button" onclick="openAdminModal('فاتورة ضريبية مبسطة #{{ $order->id }}', 'invoice-admin-{{ $order->id }}')">الفاتورة</button>
                                        @endif
                                        @if (auth()->user()->hasAdminPermission('orders_delete'))
                                            <form method="post" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('حذف هذا الطلب وجميع ملفاته؟')">
                                                @csrf
                                                @method('delete')
                                                <button class="danger small-button" type="submit">حذف الطلب</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="order-detail-section order-files-cards {{ $order->service_type === 'research' ? 'research' : '' }}">
                            @foreach ($order->productItems as $item)
                                <div class="order-file-card product-order-card">
                                    <div class="order-file-field file-name product-name-field">
                                        <span>المنتج</span>
                                        <div class="ordered-product-main">
                                            @if ($item->image_path)
                                                <img class="ordered-product-image" src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->product_name }}">
                                            @else
                                                <span class="ordered-product-image ordered-product-image-placeholder" aria-hidden="true">🛍️</span>
                                            @endif
                                            <strong>{{ $item->product_name }}</strong>
                                        </div>
                                    </div>
                                    <div class="order-file-field"><span>الشركة</span><strong>{{ $item->company_name }}</strong></div>
                                    <div class="order-file-field"><span>النوع</span><strong>{{ $item->product_type }}</strong></div>
                                    <div class="order-file-field price"><span>سعر الوحدة</span><strong>{{ $item->unit_price }} ريال</strong></div>
                                    <div class="order-file-field"><span>الكمية</span><strong>{{ $item->quantity }}</strong></div>
                                    <div class="order-file-field price total product-total-field"><span>الإجمالي</span><strong>{{ $item->total_price }} ريال</strong></div>
                                </div>
                            @endforeach
                            @foreach ($order->files as $file)
                                @php($isAcademicWord = in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word')
                                <div class="order-file-card">
                                    <div class="order-file-field file-name">
                                        <span>{{ $order->service_type === 'research' ? 'عنوان البحث المطلوب' : 'الملف' }}</span>
                                        <strong>{{ $order->service_type === 'research' ? ($file->research_title ?: $file->original_name) : $file->original_name }}</strong>
                                    </div>
                                    @if ($order->service_type === 'research')
                                        <div class="order-file-field">
                                            <span>اسم الطالب</span>
                                            <strong>{{ $file->research_student_name ?: '-' }}</strong>
                                        </div>
                                        <div class="order-file-field">
                                            <span>الدكتور أو الأستاذ</span>
                                            <strong>{{ $file->research_instructor_name ?: '-' }}</strong>
                                        </div>
                                        <div class="order-file-field">
                                            <span>الجامعة أو المدرسة أو المعهد</span>
                                            <strong>{{ $file->university_name ?: '-' }}</strong>
                                        </div>
                                    @endif
                                    @if ($order->service_type !== 'research')
                                        <div class="order-file-field">
                                            <span>النوع</span>
                                            <strong>{{ strtoupper($file->file_type) }}</strong>
                                        </div>
                                    @endif
                                    @if ($isAcademicWord)
                                        <div class="order-file-field">
                                            <span>الاستخدام</span>
                                            <strong>ملف Word للعرض فقط، وغير محتسب ضمن الطباعة أو التجليد أو التسعير.</strong>
                                        </div>
                                    @else
                                    @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                        @if ($order->service_type === 'thesis')
                                        <div class="order-file-field">
                                            <span>مشروع الرسالة</span>
                                            <strong>{{ $projectNames[$file->thesis_project_type] ?? '-' }}</strong>
                                        </div>
                                        @endif
                                        <div class="order-file-field academic-university-field">
                                            <span>الجامعة/المعهد</span>
                                            <strong>{{ $file->university_name ?: '-' }}</strong>
                                        </div>
                                        <div class="order-file-field">
                                            <span>لون الرسالة</span>
                                            <strong>{{ $coverColorNames[$file->cover_color] ?? '-' }}</strong>
                                        </div>
                                        <div class="order-file-field">
                                            <span>لون الكتابة</span>
                                            <strong>{{ $writingColorNames[$file->writing_color] ?? '-' }}</strong>
                                        </div>
                                        <div class="order-file-field">
                                            <span>خيار CD</span>
                                            <strong>{{ ['none' => 'بدون CD', 'plain' => 'CD بدون طباعة', 'printed' => 'CD مع طباعة'][$file->cd_type ?: 'none'] ?? 'بدون CD' }}</strong>
                                        </div>
                                        <div class="order-file-field">
                                            <span>عدد CD</span>
                                            <strong>{{ $file->cd_type === 'none' ? 0 : $file->cd_copies }}</strong>
                                        </div>
                                        <div class="order-file-field price">
                                            <span>سعر CD</span>
                                            <strong>{{ $file->cd_price }} ريال</strong>
                                        </div>
                                    @endif
                                    <div class="order-file-field">
                                        <span>الصفحات</span>
                                        <strong>{{ $file->pages }}</strong>
                                    </div>
                                    @if ($order->service_type !== 'research')
                                        <div class="order-file-field">
                                            <span>النسخ</span>
                                            <strong>{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : $file->copies }}</strong>
                                        </div>
                                    @endif
                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                                        <div class="order-file-field">
                                            <span>نوع الطباعة</span>
                                            <strong>{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : (['one_side' => 'وجه واحد', 'two_sides' => 'وجهين'][$file->print_sides] ?? 'وجهين') }}</strong>
                                        </div>
                                    @endif
                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true))
                                        <div class="order-file-field">
                                            <span>حجم الصفحة</span>
                                            <strong>{{ ['A4' => 'A4', 'A3' => 'A3', 'A5' => 'A5', 'B5' => 'B5'][$file->page_size] ?? 'A4' }}</strong>
                                        </div>
                                    @endif
                                    @if (in_array($order->service_type, ['notes', 'books'], true))
                                        <div class="order-file-field">
                                            <span>لون الورق</span>
                                            <strong>{{ ['white' => 'أبيض', 'yellow' => 'أصفر'][$file->paper_color] ?? 'أبيض' }}</strong>
                                        </div>
                                    @endif
                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true) && filled($file->binding_type))
                                        <div class="order-file-field">
                                            <span>{{ $bindingLabel }}</span>
                                            <strong>{{ $bindingNames[$file->binding_type] ?? '-' }}</strong>
                                        </div>
                                    @endif
                                    @if (! in_array($order->service_type, $noPrintServices, true))
                                        <div class="order-file-field price">
                                            <span>سعر الطباعة</span>
                                            <strong>{{ $file->print_price }} ريال</strong>
                                        </div>
                                    @endif
                                    <div class="order-file-field price">
                                        <span>{{ $bindingPriceLabel }}</span>
                                        <strong>{{ $file->binding_price }} ريال</strong>
                                    </div>
                                    <div class="order-file-field price total">
                                        <span>الإجمالي</span>
                                        <strong>{{ $file->total_price }} ريال</strong>
                                    </div>
                                    @endif
                                    @if ($order->service_type !== 'research')
                                        <div class="order-file-field actions-field">
                                            <span>الملف</span>
                                            @if (auth()->user()->hasAdminPermission('files_download'))
                                                <div class="file-action-buttons">
                                                    <a class="file-action-button view" href="{{ route('admin.files.view', $file) }}">عرض الملف</a>
                                                    <a class="file-action-button download" href="{{ route('admin.files.download', $file) }}" data-complete-order-download>تحميل الملف</a>
                                                </div>
                                            @else
                                                <strong class="muted">لا توجد صلاحية تحميل</strong>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if (in_array($order->service_type, ['formatting', 'research'], true))
                            <div class="panel order-detail-section delivered-files-section">
                                <h2>ملفات التسليم للعميل</h2>
                                @if ($order->deliveredFiles->isNotEmpty())
                                    <div class="delivered-files-list">
                                        @foreach ($order->deliveredFiles as $deliveredFile)
                                            <div class="delivered-file-item">
                                                <div>
                                                    <div class="delivered-file-name">{{ $deliveredFile->original_name }}</div>
                                                    <div class="muted" data-local-datetime="{{ $deliveredFile->created_at->toIso8601String() }}">{{ $deliveredFile->created_at->format('Y-m-d H:i') }}</div>
                                                </div>
                                                <div class="delivered-file-actions">
                                                    @if (auth()->user()->hasAdminPermission('delivered_files_download'))
                                                        <a class="ghost" href="{{ route('admin.delivered-files.download', ['deliveredFile' => $deliveredFile, 'view' => 1]) }}">عرض</a>
                                                        <a class="save small-button" href="{{ route('admin.delivered-files.download', $deliveredFile) }}">تحميل</a>
                                                    @endif
                                                    @if (auth()->user()->hasAdminPermission('delivered_files_delete'))
                                                        <form method="post" action="{{ route('admin.delivered-files.destroy', $deliveredFile) }}" onsubmit="return confirm('حذف ملف التسليم هذا؟')">
                                                            @csrf
                                                            @method('delete')
                                                            <button class="danger small-button" type="submit">حذف</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="muted delivered-files-empty">لم يتم إرفاق ملف التسليم بعد. لن يظهر زر التحميل للعميل إلا بعد رفع الملف.</p>
                                @endif
                                @if (auth()->user()->hasAdminPermission('delivered_files_upload'))
                                    <form class="delivered-upload-form" method="post" action="{{ route('admin.orders.delivered-file.upload', $order) }}" enctype="multipart/form-data">
                                        @csrf
                                        <label>إضافة ملف تسليم جديد</label>
                                        <input type="file" name="delivered_file" required>
                                        <button class="save" type="submit">حفظ ملف التسليم</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </template>
        </div>
    @empty
        <div class="panel empty">لا توجد طلبات حتى الآن.</div>
    @endforelse

    @foreach ($orders as $order)
        @if ($order->payment_status === 'paid' && auth()->user()->hasAdminPermission('invoices_view'))
            <template id="invoice-admin-{{ $order->id }}">
                @include('shared.invoice', ['order' => $order, 'invoiceId' => 'adminInvoice' . $order->id])
            </template>
        @endif
    @endforeach

    @php($returnOrder = $orders->firstWhere('id', (int) request('open_order')))
    @if ($returnOrder)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                openAdminModal(@json('طلبات '.$returnOrder->user->name), @json('customerOrders'.$returnOrder->user_id));
                requestAnimationFrame(() => {
                    document.querySelector('#adminModalBody [data-order-id="{{ $returnOrder->id }}"]')
                        ?.scrollIntoView({ block: 'start' });
                });
            });
        </script>
    @endif
@endsection
