@extends('admin.layout')

@section('title', 'الطلبات - لوحة المدير')

@section('content')
    <div class="page-title">
        <div>
            <h1>الطلبات</h1>
            <p class="subtitle">متابعة طلبات العملاء والملفات المرفوعة والأسعار.</p>
        </div>
    </div>

    <div class="panel">
        <form class="search-form auto-search-form" method="get" action="{{ route('admin.orders') }}">
            <div style="flex: 1;">
                <label>ابحث برقم الطلب أو رقم الجوال أو اسم العميل</label>
                <input name="search" value="{{ $search }}" placeholder="مثال: 12 أو 0500000000 أو محمد">
            </div>
            @if ($search !== '')
                <a class="ghost" href="{{ route('admin.orders') }}">مسح</a>
            @endif
        </form>
    </div>

    @forelse ($orders as $order)
        @php
            $bindingLabel = $order->service_type === 'notes' ? 'التغليف' : 'التجليد';
            $bindingPriceLabel = $order->service_type === 'notes' ? 'سعر التغليف' : 'سعر التجليد';
            $bindingNames = [
                'tape' => $order->service_type === 'notes' ? 'تغليف دبوس' : 'تجليد دبوس',
                'wire' => $order->service_type === 'notes' ? 'تغليف سلك' : 'تجليد سلك',
                'normal' => $order->service_type === 'notes' ? 'تغليف عادي' : 'تجليد عادي',
                'none' => $order->service_type === 'notes' ? 'بدون تغليف' : 'بدون تجليد',
            ];
        @endphp
        <div class="order">
            <div class="order-head">
                <div><span class="label">رقم الطلب</span>#{{ $order->id }}</div>
                <div><span class="label">العميل</span>{{ $order->user->name }} - {{ $order->user->phone }}</div>
                <div><span class="label">الخدمة</span>{{ ['notes' => 'مذكرات', 'thesis' => 'ماجستير', 'phd' => 'دكتوراه'][$order->service_type] ?? $order->service_type }}</div>
                <div><span class="label">الحالة</span>{{ $order->status }}</div>
                <div><span class="label">الدفع</span>{{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}{{ $order->payment_method ? ' - ' . (['apple_pay' => 'Apple Pay', 'card' => 'بطاقة'][$order->payment_method] ?? $order->payment_method) : '' }}</div>
                <div><span class="label">الإجمالي</span>طباعة {{ $order->print_total }} | {{ $bindingLabel }} {{ $order->binding_total }} | الكل {{ $order->grand_total }} ريال</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>الملف</th>
                        <th>النوع</th>
                        @if (in_array($order->service_type, ['thesis', 'phd'], true))
                            <th>الجامعة/المعهد</th>
                        @endif
                        <th>الصفحات</th>
                        <th>النسخ</th>
                        <th>{{ $bindingLabel }}</th>
                        <th>سعر الطباعة</th>
                        <th>{{ $bindingPriceLabel }}</th>
                        <th>الإجمالي</th>
                        <th>تحميل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->files as $file)
                        <tr>
                            <td>{{ $file->original_name }}</td>
                            <td>{{ strtoupper($file->file_type) }}</td>
                            @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                <td>{{ $file->university_name ?: '-' }}</td>
                            @endif
                            <td>{{ $file->pages }}</td>
                            <td>{{ $file->copies }}</td>
                            <td>{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                            <td>{{ $file->print_price }} ريال</td>
                            <td>{{ $file->binding_price }} ريال</td>
                            <td>{{ $file->total_price }} ريال</td>
                            <td><a href="{{ route('admin.files.download', $file) }}">تنزيل</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="panel empty">لا توجد طلبات حتى الآن.</div>
    @endforelse
@endsection
