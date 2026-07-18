@php
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
    $serviceFullNames = [
        'notes' => 'طباعة المذكرات وملفات ال PDF',
        'books' => 'طباعة وتجليد كتب كعب جلد طبيعي',
        'color_printing' => 'طباعة الملفات بالألوان',
        'thesis' => 'طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج',
        'phd' => 'طباعة وتجليد رسالة دكتوراه',
        'formatting' => 'تنسيق الرسائل الجامعية',
        'research' => 'إنشاء بحث',
        'stationery' => 'القرطاسية',
    ];
    $projectNames = [
        'thesis' => 'رسالة ماجستير',
        'supplementary' => 'بحث تكميلي',
        'graduation' => 'بحث تخرج',
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
    $noPrintServices = ['formatting', 'research', 'stationery'];
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
        'stationery' => 'إجمالي المنتجات',
        default => 'سعر التجليد',
    };
    $paymentMethod = [
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
        'mada' => 'Mada',
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        'card' => 'بطاقة بنكية',
    ][$order->payment_method] ?? ($order->payment_method ?: '-');
    $deliveryMethodNames = [
        'branch_pickup' => 'استلام من الفرع',
        'islamic_university_delivery' => 'توصيل داخل الجامعة الإسلامية',
        'madinah_delivery' => 'توصيل داخل المدينة المنورة',
        'redbox_delivery' => 'خارج المدينة المنورة عبر RedBox',
    ];
    $taxNumber = '٣١٤٤١٧١٦٩٦٠٠٠٠٣';
    $vatRate = 15;
    $vatAmount = round(((float) $order->grand_total * $vatRate) / (100 + $vatRate), 2);
@endphp

<div class="invoice-toolbar">
    <button class="action secondary" type="button" onclick="printInvoice('{{ $invoiceId }}')">تحميل PDF</button>
</div>

<section class="invoice-document" id="{{ $invoiceId }}" dir="rtl">
    <div class="invoice-head">
        <div class="invoice-brand">
            <div class="invoice-logo"><img src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق"></div>
            <div>
                <h2>شركة مسير المدينة</h2>
                <p>فاتورة ضريبية مبسطة</p>
            </div>
        </div>
        <div class="invoice-number">
            <span>فاتورة ضريبية مبسطة</span>
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
        <div><span>الرقم الضريبي</span><strong>{{ $taxNumber }}</strong></div>
        @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true))
            <div class="full"><span>الاستلام والتوصيل</span><strong>
                {{ $deliveryMethodNames[$order->delivery_method] ?? '-' }}
                @if ($order->delivery_method === 'islamic_university_delivery')
                    - وحدة {{ $order->delivery_unit }} / دور {{ $order->delivery_floor }} / غرفة {{ $order->delivery_room }}
                @elseif (in_array($order->delivery_method, ['madinah_delivery', 'redbox_delivery'], true))
                    - {{ $order->delivery_city }} / حي {{ $order->delivery_district }} / شارع {{ $order->delivery_street }}
                    @if ($order->delivery_map_url)
                        - {{ $order->delivery_map_url }}
                    @endif
                @endif
            </strong></div>
        @endif
        @if ($order->payment_reference)
            <div class="full"><span>رقم العملية</span><strong>{{ $order->payment_reference }}</strong></div>
        @endif
    </div>

    <div class="invoice-section-title">تفاصيل البنود</div>
    <div class="invoice-table-wrap">
        @if ($order->service_type === 'stationery')
            <table>
                <thead><tr><th>المنتج</th><th>الشركة</th><th>النوع</th><th>سعر الوحدة</th><th>الكمية</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    @foreach ($order->productItems as $item)
                        <tr>
                            <td data-label="المنتج">{{ $item->product_name }}</td>
                            <td data-label="الشركة">{{ $item->company_name }}</td>
                            <td data-label="النوع">{{ $item->product_type }}</td>
                            <td data-label="سعر الوحدة">{{ $item->unit_price }} ريال</td>
                            <td data-label="الكمية">{{ $item->quantity }}</td>
                            <td data-label="الإجمالي">{{ $item->total_price }} ريال</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
        <table>
            <thead>
                <tr>
                    <th>البند</th>
                    @if ($order->service_type === 'research')
                        <th>اسم الطالب</th>
                        <th>الدكتور أو الأستاذ</th>
                        <th>الجهة التعليمية</th>
                    @endif
                    <th>الصفحات</th>
                    @if ($order->service_type !== 'research')
                        <th>النسخ</th>
                    @endif
                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                        <th>نوع الطباعة</th>
                    @endif
                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true))
                        <th>حجم الصفحة</th>
                    @endif
                    @if (in_array($order->service_type, ['notes', 'books'], true))
                        <th>لون الورق</th>
                    @endif
                    @if ($order->service_type === 'thesis')
                        <th>مشروع الرسالة</th>
                    @endif
                    @if (in_array($order->service_type, ['thesis', 'phd'], true))
                        <th>الجامعة/المعهد</th>
                        <th>خيار CD</th>
                        <th>عدد CD</th>
                        <th>سعر CD</th>
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
                    @php($isAcademicWord = in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word')
                    <tr>
                        <td data-label="البند">{{ $file->original_name }}</td>
                        @if ($order->service_type === 'research')
                            <td data-label="اسم الطالب">{{ $file->research_student_name ?: '-' }}</td>
                            <td data-label="الدكتور أو الأستاذ">{{ $file->research_instructor_name ?: '-' }}</td>
                            <td data-label="الجهة التعليمية">{{ $file->university_name ?: '-' }}</td>
                        @endif
                        @if ($isAcademicWord)
                            <td colspan="20" data-label="الاستخدام">ملف Word للعرض فقط، وغير محتسب ضمن الطباعة أو التجليد أو الإجمالي.</td>
                        @else
                        <td data-label="الصفحات">{{ $file->pages }}</td>
                        @if ($order->service_type !== 'research')
                            <td data-label="النسخ">{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : $file->copies }}</td>
                        @endif
                        @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                            <td data-label="نوع الطباعة">{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : ($printSideNames[$file->print_sides] ?? 'وجهين') }}</td>
                        @endif
                        @if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true))
                            <td data-label="حجم الصفحة">{{ $pageSizeNames[$file->page_size] ?? 'A4' }}</td>
                        @endif
                        @if (in_array($order->service_type, ['notes', 'books'], true))
                            <td data-label="لون الورق">{{ $paperColorNames[$file->paper_color] ?? 'أبيض' }}</td>
                        @endif
                        @if ($order->service_type === 'thesis')
                            <td data-label="مشروع الرسالة">{{ $projectNames[$file->thesis_project_type] ?? '-' }}</td>
                        @endif
                        @if (in_array($order->service_type, ['thesis', 'phd'], true))
                            <td data-label="الجامعة/المعهد">{{ $file->university_name ?: '-' }}</td>
                            <td data-label="خيار CD">{{ ['none' => 'بدون CD', 'plain' => 'CD بدون طباعة', 'printed' => 'CD مع طباعة'][$file->cd_type ?: 'none'] ?? 'بدون CD' }}</td>
                            <td data-label="عدد CD">{{ $file->cd_type === 'none' ? 0 : $file->cd_copies }}</td>
                            <td data-label="سعر CD">{{ $file->cd_price }} ريال</td>
                        @endif
                        @if (! in_array($order->service_type, $noPrintServices, true))
                            <td data-label="{{ $bindingLabel }}">{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                            <td data-label="سعر الطباعة">{{ $file->print_price }} ريال</td>
                        @endif
                        <td data-label="{{ $bindingPriceLabel }}">{{ $file->binding_price }} ريال</td>
                        <td data-label="الإجمالي">{{ $file->total_price }} ريال</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
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
            @if (in_array($order->service_type, ['thesis', 'phd'], true))
                <div><span>سعر CD</span><strong>{{ $order->files->sum('cd_price') }} ريال</strong></div>
            @endif
            @if ($order->discount_amount > 0)
                <div><span>الخصم {{ $order->discount_code }}</span><strong>- {{ $order->discount_amount }} ريال</strong></div>
            @endif
            @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true))
                <div><span>رسوم التوصيل</span><strong>{{ $order->delivery_fee }} ريال</strong></div>
            @endif
            <div><span>الضريبة (١٥٪ شاملة)</span><strong>{{ number_format($vatAmount, 2, '.', '') }} ريال</strong></div>
            <div class="grand"><span>الإجمالي المستحق</span><strong>{{ $order->grand_total }} ريال</strong></div>
        </div>
    </div>

    <p class="invoice-note">الأسعار والإجمالي تشمل ضريبة القيمة المضافة بنسبة ١٥٪، ولا تضاف الضريبة مرة أخرى على المبلغ المستحق.</p>
</section>
