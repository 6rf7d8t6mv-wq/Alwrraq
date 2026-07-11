@extends('admin.layout')

@section('title', 'الرئيسية - لوحة المدير')

@section('content')
    <div class="page-title">
        <div>
            <h1>الرئيسية</h1>
            <p class="subtitle">نظرة سريعة على نشاط الطلبات والمستخدمين.</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat"><span>كل الطلبات</span><strong>{{ $stats['orders'] }}</strong></div>
        <div class="stat"><span>طلبات جديدة</span><strong>{{ $stats['new_orders'] }}</strong></div>
        <div class="stat"><span>العملاء</span><strong>{{ $stats['customers'] }}</strong></div>
        <div class="stat"><span>المدراء</span><strong>{{ $stats['admins'] }}</strong></div>
        <div class="stat"><span>إجمالي الطباعة</span><strong>{{ $stats['print_total'] }} ريال</strong></div>
        <div class="stat"><span>إجمالي التجليد/التغليف</span><strong>{{ $stats['binding_total'] }} ريال</strong></div>
        <div class="stat"><span>الإجمالي الكلي</span><strong>{{ $stats['grand_total'] }} ريال</strong></div>
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>آخر الطلبات</h2>
        @if ($orders->isEmpty())
            <div class="empty">لا توجد طلبات حتى الآن.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>الخدمة</th>
                        <th>الحالة</th>
                        <th>الدفع</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders->take(6) as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user->name }} - {{ $order->user->phone }}</td>
                            <td>{{ ['notes' => 'مذكرات', 'thesis' => 'ماجستير', 'phd' => 'دكتوراه'][$order->service_type] ?? $order->service_type }}</td>
                            <td>{{ $order->status }}</td>
                            <td>{{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}</td>
                            <td>{{ $order->grand_total }} ريال</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
