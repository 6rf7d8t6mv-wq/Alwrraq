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
                            'notes' => 'التغليف',
                            'formatting' => 'التنسيق',
                            'research' => 'إنشاء البحث',
                            default => 'التجليد',
                        };
                        $bindingPriceLabel = match ($order->service_type) {
                            'books' => 'سعر التجليد',
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
                            : [
                                'tape' => $order->service_type === 'notes' ? 'تغليف دبوس' : 'تجليد دبوس',
                                'wire' => $order->service_type === 'notes' ? 'تغليف سلك' : 'تجليد سلك',
                                'normal' => $order->service_type === 'notes' ? 'تغليف عادي' : 'تجليد عادي',
                                'none' => $order->service_type === 'notes' ? 'بدون تغليف' : 'بدون تجليد',
                            ];
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
                            @if (in_array($order->service_type, ['notes', 'books', 'thesis', 'phd'], true))
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

                        @if (! $isPaid && auth()->user()->hasAdminPermission('discounts_apply'))
                            <div class="panel order-detail-section" style="margin: 0 0 16px; background: #f8fafc;">
                                <h2 style="margin-bottom: 10px;">كود الخصم</h2>
                                <form method="post" action="{{ route('admin.orders.discount.apply', $order) }}">
                                    @csrf
                                    @method('patch')
                                    <div class="form-grid">
                                        <div>
                                            <label>كود الخصم</label>
                                            <input name="discount_code" value="{{ $order->discount_code }}" placeholder="مثال: STUDENT10" required>
                                        </div>
                                        <div>
                                            <label>قيمة الخصم بالريال</label>
                                            <input name="discount_amount" inputmode="numeric" value="{{ $order->discount_amount ?: '' }}" placeholder="مثال: 10" required>
                                        </div>
                                    </div>
                                    <button class="save" type="submit">تطبيق الخصم</button>
                                </form>
                                @if ($order->discount_amount > 0)
                                    <p class="muted" style="margin: 10px 0 0;">الإجمالي قبل الخصم: {{ $order->baseTotal() }} ريال، رسوم التوصيل: {{ $order->delivery_fee }} ريال، بعد الخصم والتوصيل: {{ $order->grand_total }} ريال.</p>
                                @endif
                            </div>
                        @endif

                        <div class="order-detail-section order-detail-table-wrap {{ $order->service_type === 'research' ? 'research' : '' }}">
                            <table>
                                <thead>
                                    <tr>
                                        <th>الملف</th>
                                        @if ($order->service_type !== 'research')
                                            <th>النوع</th>
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
                                        @if (in_array($order->service_type, ['notes', 'books', 'thesis', 'phd'], true))
                                            <th>نوع الطباعة</th>
                                        @endif
                                        @if (in_array($order->service_type, ['notes', 'books'], true))
                                            <th>حجم الصفحة</th>
                                        @endif
                                        @if (in_array($order->service_type, ['notes', 'books'], true))
                                            <th>لون الورق</th>
                                        @endif
                                        @if (! in_array($order->service_type, $noPrintServices, true))
                                            <th>{{ $bindingLabel }}</th>
                                        @endif
                                        @if (! in_array($order->service_type, $noPrintServices, true))
                                            <th>سعر الطباعة</th>
                                        @endif
                                        <th>{{ $bindingPriceLabel }}</th>
                                        <th>الإجمالي</th>
                                        @if ($order->service_type !== 'research')
                                            <th>تحميل</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->files as $file)
                                        <tr>
                                            <td>{{ $file->original_name }}</td>
                                            @if ($order->service_type !== 'research')
                                                <td>{{ strtoupper($file->file_type) }}</td>
                                            @endif
                                            @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                                <td>{{ $file->university_name ?: '-' }}</td>
                                                <td>{{ $coverColorNames[$file->cover_color] ?? '-' }}</td>
                                                <td>{{ $writingColorNames[$file->writing_color] ?? '-' }}</td>
                                            @endif
                                            <td>{{ $file->pages }}</td>
                                            @if ($order->service_type !== 'research')
                                                <td>{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : $file->copies }}</td>
                                            @endif
                                            @if (in_array($order->service_type, ['notes', 'books', 'thesis', 'phd'], true))
                                                <td>{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : (['one_side' => 'وجه واحد', 'two_sides' => 'وجهين'][$file->print_sides] ?? 'وجهين') }}</td>
                                            @endif
                                            @if (in_array($order->service_type, ['notes', 'books'], true))
                                                <td>{{ ['A4' => 'A4', 'A5' => 'A5', 'B5' => 'B5'][$file->page_size] ?? 'A4' }}</td>
                                            @endif
                                            @if (in_array($order->service_type, ['notes', 'books'], true))
                                                <td>{{ ['white' => 'أبيض', 'yellow' => 'أصفر'][$file->paper_color] ?? 'أبيض' }}</td>
                                            @endif
                                            @if (! in_array($order->service_type, $noPrintServices, true))
                                                <td>{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                                            @endif
                                            @if (! in_array($order->service_type, $noPrintServices, true))
                                                <td>{{ $file->print_price }} ريال</td>
                                            @endif
                                            <td>{{ $file->binding_price }} ريال</td>
                                            <td>{{ $file->total_price }} ريال</td>
                                            @if ($order->service_type !== 'research')
                                                <td>
                                                    @if (auth()->user()->hasAdminPermission('files_download'))
                                                        <a class="save small-button" href="{{ route('admin.files.download', $file) }}" data-complete-order-download>تنزيل الملف</a>
                                                    @else
                                                        <span class="muted">لا توجد صلاحية تحميل</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
