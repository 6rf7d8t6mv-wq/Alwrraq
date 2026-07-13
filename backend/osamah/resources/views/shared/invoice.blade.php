@php
    $serviceNames = [
        'notes' => 'مذكرات',
        'thesis' => 'ماجستير',
        'phd' => 'دكتوراه',
        'formatting' => 'تنسيق الرسائل الجامعية',
        'research' => 'إنشاء بحث',
    ];
    $serviceFullNames = [
        'notes' => 'طباعة وتغليف المذكرات',
        'thesis' => 'طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج',
        'phd' => 'طباعة وتجليد رسالة دكتوراه',
        'formatting' => 'تنسيق الرسائل الجامعية',
        'research' => 'إنشاء بحث',
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
    $noPrintServices = ['formatting', 'research'];
    $bindingLabel = $order->service_type === 'notes'
        ? 'التغليف'
        : ($order->service_type === 'formatting' ? 'التنسيق' : ($order->service_type === 'research' ? 'إنشاء البحث' : 'التجليد'));
    $bindingPriceLabel = $order->service_type === 'notes'
        ? 'سعر التغليف'
        : ($order->service_type === 'formatting' ? 'سعر التنسيق' : ($order->service_type === 'research' ? 'سعر إنشاء البحث' : 'سعر التجليد'));
    $paymentMethod = [
        'apple_pay' => 'Apple Pay',
        'card' => 'بطاقة بنكية',
    ][$order->payment_method] ?? ($order->payment_method ?: '-');
@endphp

<div class="invoice-toolbar">
    <button class="action secondary" type="button" onclick="printInvoice('{{ $invoiceId }}')">تحميل PDF</button>
</div>

<section class="invoice-document" id="{{ $invoiceId }}" dir="rtl">
    <div class="invoice-head">
        <div class="invoice-brand">
            <div class="invoice-logo">M</div>
            <div>
                <h2>Mr-Student</h2>
                <p>خدمات الطباعة والتجليد</p>
            </div>
        </div>
        <div class="invoice-number">
            <span>فاتورة</span>
            <strong>#{{ $order->id }}</strong>
            <small>{{ $order->payment_status === 'paid' ? 'مدفوعة' : 'غير مدفوعة' }}</small>
        </div>
    </div>

    <div class="invoice-section-title">بيانات الفاتورة</div>
    <div class="invoice-grid">
        <div><span>العميل</span><strong>{{ $order->user->name }}</strong></div>
        <div><span>رقم الجوال</span><strong>{{ $order->user->phone }}</strong></div>
        <div><span>الخدمة</span><strong>{{ $serviceFullNames[$order->service_type] ?? $order->service_type }}</strong></div>
        <div><span>تاريخ الطلب</span><strong data-local-datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('Y-m-d H:i') }}</strong></div>
        <div><span>حالة الدفع</span><strong>{{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}</strong></div>
        <div><span>طريقة الدفع</span><strong>{{ $paymentMethod }}</strong></div>
        @if ($order->payment_reference)
            <div class="full"><span>رقم العملية</span><strong>{{ $order->payment_reference }}</strong></div>
        @endif
    </div>

    <div class="invoice-section-title">تفاصيل البنود</div>
    <div class="invoice-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>البند</th>
                    <th>الصفحات</th>
                    @if ($order->service_type !== 'research')
                        <th>النسخ</th>
                    @endif
                    @if ($order->service_type === 'thesis')
                        <th>مشروع الرسالة</th>
                    @endif
                    @if (in_array($order->service_type, ['thesis', 'phd'], true))
                        <th>الجامعة/المعهد</th>
                    @endif
                    @if (! in_array($order->service_type, $noPrintServices, true))
                        <th>{{ $bindingLabel }}</th>
                        <th>سعر الطباعة</th>
                    @endif
                    <th>{{ $bindingPriceLabel }}</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->files as $file)
                    <tr>
                        <td data-label="البند">{{ $file->original_name }}</td>
                        <td data-label="الصفحات">{{ $file->pages }}</td>
                        @if ($order->service_type !== 'research')
                            <td data-label="النسخ">{{ $file->copies }}</td>
                        @endif
                        @if ($order->service_type === 'thesis')
                            <td data-label="مشروع الرسالة">{{ $projectNames[$file->thesis_project_type] ?? '-' }}</td>
                        @endif
                        @if (in_array($order->service_type, ['thesis', 'phd'], true))
                            <td data-label="الجامعة/المعهد">{{ $file->university_name ?: '-' }}</td>
                        @endif
                        @if (! in_array($order->service_type, $noPrintServices, true))
                            <td data-label="{{ $bindingLabel }}">{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                            <td data-label="سعر الطباعة">{{ $file->print_price }} ريال</td>
                        @endif
                        <td data-label="{{ $bindingPriceLabel }}">{{ $file->binding_price }} ريال</td>
                        <td data-label="الإجمالي">{{ $file->total_price }} ريال</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="invoice-summary">
        <div class="invoice-summary-note">
            <strong>ملخص الدفع</strong>
            <span>{{ $paymentMethod }}{{ $order->paid_at ? ' - ' . $order->paid_at->format('Y-m-d H:i') : '' }}</span>
        </div>
        <div class="invoice-totals">
            @if (! in_array($order->service_type, $noPrintServices, true))
                <div><span>سعر الطباعة</span><strong>{{ $order->print_total }} ريال</strong></div>
            @endif
            <div><span>{{ $bindingPriceLabel }}</span><strong>{{ $order->binding_total }} ريال</strong></div>
            <div class="grand"><span>الإجمالي المستحق</span><strong>{{ $order->grand_total }} ريال</strong></div>
        </div>
    </div>

    <p class="invoice-note">هذه الفاتورة صادرة إلكترونيًا من منصة Mr-Student.</p>
</section>
