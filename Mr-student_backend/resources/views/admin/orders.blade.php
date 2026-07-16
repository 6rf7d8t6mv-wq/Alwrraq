@extends('admin.layout')

@section('title', 'الطلبات - لوحة المدير')

@section('content')
    <div class="page-title">
        <div>
            <h1>الطلبات</h1>
            <p class="subtitle">متابعة طلبات العملاء والملفات المرفوعة والأسعار.</p>
        </div>
    </div>

    <div class="order-filter-bar">
        <a class="order-filter-button red {{ $statusFilter === 'new' ? 'active' : '' }}" href="{{ route('admin.orders', array_filter(['status_filter' => 'new', 'search' => $search])) }}">الطلبات الجديدة</a>
        <a class="order-filter-button yellow {{ $statusFilter === 'in_progress' ? 'active' : '' }}" href="{{ route('admin.orders', array_filter(['status_filter' => 'in_progress', 'search' => $search])) }}">الطلبات قيد العمل</a>
        <a class="order-filter-button green {{ $statusFilter === 'completed' ? 'active' : '' }}" href="{{ route('admin.orders', array_filter(['status_filter' => 'completed', 'search' => $search])) }}">إجمالي الطلبات المكتملة</a>
    </div>

    <div class="panel">
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
        $noPrintServices = ['formatting', 'research'];
        $serviceNames = [
            'notes' => 'مذكرات',
            'books' => 'كتب',
            'color_printing' => 'طباعة الملفات بالألوان',
            'thesis' => 'ماجستير',
            'phd' => 'دكتوراه',
            'formatting' => 'تنسيق الرسائل الجامعية',
            'research' => 'إنشاء بحث',
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

        <div class="order">
            <div class="order-head">
                <div><span class="label">العميل</span>{{ $customer->name }} - {{ $customer->phone }}</div>
                <div><span class="label">عدد الطلبات</span>{{ $customerOrders->count() }}</div>
                <div><span class="label">آخر طلب</span><span data-local-datetime="{{ $latestOrder->created_at->toIso8601String() }}">{{ $createdAtText }}</span></div>
                <div><span class="label">نوع الخدمة</span>{{ $servicesText }}</div>
                <div><span class="label">حالة الدفع</span>{{ $paymentSummary }}</div>
                <div><span class="label">المبلغ</span>{{ $customerOrders->sum('grand_total') }} ريال</div>
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
                            <div><span class="label">الدفع</span><span class="badge">{{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}</span>{{ $order->payment_method ? ' - ' . (['apple_pay' => 'Apple Pay', 'card' => 'بطاقة'][$order->payment_method] ?? $order->payment_method) : '' }}</div>
                            @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                                <div><span class="label">التوصيل</span>
                                    {{ $deliveryMethodNames[$order->delivery_method] ?? '-' }}
                                    @if ($order->delivery_method === 'islamic_university_delivery')
                                        <br><span class="muted">وحدة {{ $order->delivery_unit }} / دور {{ $order->delivery_floor }} / غرفة {{ $order->delivery_room }}</span>
                                    @elseif (in_array($order->delivery_method, ['madinah_delivery', 'redbox_delivery'], true))
                                        <br><span class="muted">{{ $order->delivery_city }} / حي {{ $order->delivery_district }} / شارع {{ $order->delivery_street }}</span>
                                        @if ($order->delivery_map_url)
                                            <br><a class="muted" href="{{ $order->delivery_map_url }}" target="_blank" rel="noopener">رابط الموقع</a>
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
                                            <button class="invoice-admin-button" type="button" onclick="openAdminModal('فاتورة الطلب #{{ $order->id }}', 'invoice-admin-{{ $order->id }}')">الفاتورة</button>
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
                            @foreach ($order->files as $file)
                                <div class="order-file-card">
                                    <div class="order-file-field file-name">
                                        <span>الملف</span>
                                        <strong>{{ $file->original_name }}</strong>
                                    </div>
                                    @if ($order->service_type !== 'research')
                                        <div class="order-file-field">
                                            <span>النوع</span>
                                            <strong>{{ strtoupper($file->file_type) }}</strong>
                                        </div>
                                    @endif
                                    @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                        <div class="order-file-field">
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
                                    @if (! in_array($order->service_type, $noPrintServices, true))
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
                                    @if ($order->service_type !== 'research')
                                        <div class="order-file-field actions-field">
                                            <span>الملف</span>
                                            @if (auth()->user()->hasAdminPermission('files_download'))
                                                <div class="file-action-buttons">
                                                    <a class="file-action-button view" href="{{ route('admin.files.view', $file) }}" target="_blank" rel="noopener">عرض الملف</a>
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

                        @if (in_array($order->service_type, $noPrintServices, true))
                            <div class="panel order-detail-section" style="margin: 0; background: #f8fafc;">
                                <h2 style="margin-bottom: 10px;">ملفات التسليم للعميل</h2>
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
                                                        <a class="ghost" href="{{ route('admin.delivered-files.download', ['deliveredFile' => $deliveredFile, 'view' => 1]) }}" target="_blank" rel="noopener">عرض</a>
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
                                    <p class="muted" style="margin: 0 0 10px;">لم يتم إرفاق ملف التسليم بعد. لن يظهر زر التحميل للعميل إلا بعد رفع الملف.</p>
                                @endif
                                @if (auth()->user()->hasAdminPermission('delivered_files_upload'))
                                    <form method="post" action="{{ route('admin.orders.delivered-file.upload', $order) }}" enctype="multipart/form-data">
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
@endsection
