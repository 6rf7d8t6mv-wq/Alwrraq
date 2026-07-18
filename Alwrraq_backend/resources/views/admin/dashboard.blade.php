@extends('admin.layout')

@section('title', 'الرئيسية - لوحة النظام')

@section('content')
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
        $latestOrders = $orders->take(6);
        $paidPercent = $stats['orders'] > 0 ? round(($stats['paid_orders'] / $stats['orders']) * 100) : 0;
        $completedPercent = $stats['orders'] > 0 ? round(($stats['completed_orders'] / $stats['orders']) * 100) : 0;
    @endphp

    <style>
        .dashboard-hero { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; margin-bottom: 18px; }
        .dashboard-welcome { position: relative; overflow: hidden; min-height: 170px; padding: clamp(18px, 3vw, 26px); border-radius: 12px; background: #0f172a; color: #ffffff; box-shadow: 0 20px 42px rgba(15, 23, 42, 0.18); }
        .dashboard-welcome h1 { color: #ffffff; margin-bottom: 8px; }
        .dashboard-welcome p { max-width: 680px; color: #cbd5e1; line-height: 1.8; margin: 0; }
        .dashboard-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .dashboard-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 10px; border-radius: 999px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14); color: #f8fafc; font-size: 12px; font-weight: 900; }
        .dashboard-actions { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .dashboard-action { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-radius: 10px; background: #ffffff; border: 1px solid #e5e7eb; color: #0f172a; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); }
        .dashboard-action span { color: #64748b; font-size: 12px; font-weight: 800; }
        .dashboard-action strong { color: #0f172a; }
        .discount-panel { margin: -4px 0 18px; padding: 16px; border: 1px solid #fbcfe8; border-radius: 12px; background: #fdf2f8; box-shadow: 0 12px 28px rgba(190, 24, 93, 0.08); }
        .discount-panel h2 { margin: 0 0 12px; color: #831843; font-size: 20px; }
        .discount-form { display: grid; grid-template-columns: minmax(0, 1fr) minmax(120px, 0.35fr) auto; gap: 10px; align-items: end; }
        .discount-form label { display: block; color: #9d174d; font-size: 12px; font-weight: 900; margin-bottom: 6px; }
        .discount-form input { width: 100%; padding: 11px 12px; border: 1px solid #f9a8d4; border-radius: 9px; background: #ffffff; color: #0f172a; font-weight: 800; }
        .discount-submit { padding: 12px 18px; border: 0; border-radius: 9px; background: #db2777; color: #ffffff; font-weight: 900; cursor: pointer; }
        .discount-submit:hover { background: #be185d; }
        .discount-codes-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .discount-chip { display: inline-flex; align-items: center; gap: 8px; padding: 7px 8px 7px 10px; border-radius: 999px; background: #ffffff; border: 1px solid #f9a8d4; color: #831843; font-size: 12px; font-weight: 900; }
        .discount-delete-form { display: inline-flex; margin: 0; }
        .discount-delete { width: 24px; height: 24px; border: 0; border-radius: 999px; background: #fee2e2; color: #991b1b; font-size: 13px; font-weight: 900; line-height: 1; cursor: pointer; }
        .discount-delete:hover { background: #fecaca; }
        .dashboard-stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
        .dashboard-stat { position: relative; min-height: 112px; padding: 16px; border-radius: 12px; background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); overflow: hidden; }
        .dashboard-stat span { display: block; color: #64748b; font-size: 12px; font-weight: 900; margin-bottom: 8px; }
        .dashboard-stat strong { color: #0f172a; font-size: clamp(22px, 4vw, 30px); }
        .dashboard-stat small { display: block; margin-top: 8px; color: #94a3b8; font-weight: 800; }
        .dashboard-stat.primary { background: #0f172a; border-color: #0f172a; }
        .dashboard-stat.primary span,
        .dashboard-stat.primary small { color: #cbd5e1; }
        .dashboard-stat.primary strong { color: #ffffff; }
        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr); gap: 18px; align-items: start; }
        .dashboard-section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .dashboard-section-title h2 { margin: 0; }
        .dashboard-link { display: inline-flex; align-items: center; justify-content: center; padding: 8px 10px; border-radius: 8px; background: #f1f5f9; color: #0f172a; font-size: 12px; font-weight: 900; }
        .status-list { display: grid; gap: 10px; }
        .status-row { display: grid; grid-template-columns: 90px minmax(0, 1fr) 44px; gap: 10px; align-items: center; color: #334155; font-size: 13px; font-weight: 900; }
        .status-track { height: 9px; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
        .status-fill { height: 100%; border-radius: inherit; background: #0f4c81; }
        .status-fill.green { background: #16a34a; }
        .dashboard-table-wrap { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 10px; }
        .dashboard-table-wrap table { min-width: 720px; }
        .order-id { display: inline-flex; padding: 4px 8px; border-radius: 999px; background: #f1f5f9; color: #0f172a; font-weight: 900; }
        @media (max-width: 980px) {
            .dashboard-hero, .dashboard-grid { grid-template-columns: 1fr; }
            .dashboard-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .discount-form { grid-template-columns: 1fr; }
            .dashboard-table-wrap { border: 0; overflow: visible; background: transparent; }
            .dashboard-table-wrap table { min-width: 0; display: block; }
            .dashboard-table-wrap thead { display: none; }
            .dashboard-table-wrap tbody { display: grid; gap: 12px; }
            .dashboard-table-wrap tr { display: grid; gap: 8px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
            .dashboard-table-wrap td { display: grid; grid-template-columns: minmax(92px, 34%) minmax(0, 1fr); align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid #edf2f7; white-space: normal; overflow-wrap: anywhere; }
            .dashboard-table-wrap td:last-child { border-bottom: 0; }
            .dashboard-table-wrap td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 900; }
        }
        @media (max-width: 980px) {
            .dashboard-hero { gap: 7px; margin-bottom: 10px; }
            .dashboard-welcome { min-height: 0; padding: 10px; border-radius: 10px; }
            .dashboard-welcome h1 { margin: 0; font-size: 18px; }
            .dashboard-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; margin-top: 8px; }
            .dashboard-pill { min-width: 0; justify-content: center; padding: 5px 4px; border-radius: 7px; font-size: 8px; line-height: 1.2; white-space: nowrap; overflow: hidden; }
            .dashboard-actions { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
            .dashboard-action { min-width: 0; min-height: 42px; padding: 6px; border-radius: 8px; align-items: center; justify-content: center; gap: 2px; flex-direction: column; text-align: center; }
            .dashboard-action strong { font-size: 10px; line-height: 1.2; }
            .dashboard-action span { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; font-size: 7.5px; line-height: 1.2; }
            .dashboard-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-bottom: 10px; }
            .dashboard-stat { display: flex; align-items: center; justify-content: space-between; gap: 5px; min-width: 0; min-height: 42px; padding: 6px 7px; border-radius: 8px; }
            .dashboard-stat span { min-width: 0; margin: 0; font-size: 9px; line-height: 1.25; }
            .dashboard-stat strong { flex: 0 0 auto; font-size: 12px; line-height: 1.2; text-align: left; }
            .dashboard-stat small { display: none; }
            .discount-panel { margin: 0 0 10px; padding: 8px; border-radius: 9px; }
            .discount-panel h2 { margin: 0 0 6px; font-size: 13px; }
            .discount-form { grid-template-columns: minmax(0, 1fr) 68px auto; gap: 5px; align-items: end; }
            .discount-form label { margin-bottom: 3px; font-size: 8px; }
            .discount-form input { min-width: 0; padding: 5px 6px; border-radius: 6px; font-size: 16px; line-height: 1.1; }
            .discount-submit { min-height: 29px; padding: 5px 8px; border-radius: 6px; font-size: 9px; white-space: nowrap; }
            .discount-codes-list { gap: 4px; margin-top: 6px; }
            .discount-chip { gap: 4px; padding: 4px 5px; font-size: 8px; }
            .discount-delete { width: 18px; height: 18px; font-size: 10px; }
            .dashboard-grid { gap: 9px; }
            .dashboard-grid .panel { padding: 10px; }
            .dashboard-grid .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
            .dashboard-grid .stat { display: flex; align-items: center; justify-content: space-between; gap: 5px; min-width: 0; padding: 7px; border-radius: 8px; }
            .dashboard-grid .stat span { min-width: 0; margin: 0; font-size: 8.5px; line-height: 1.25; }
            .dashboard-grid .stat strong { flex: 0 0 auto; font-size: 11px; text-align: left; }
            .dashboard-table-wrap tbody { gap: 6px; }
            .dashboard-table-wrap tr { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; padding: 6px; border-radius: 9px; box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05); }
            .dashboard-table-wrap td,
            .dashboard-table-wrap td:last-child { display: flex; align-items: center; justify-content: space-between; gap: 3px; min-width: 0; min-height: 36px; padding: 5px 6px; border: 1px solid #edf2f7; border-radius: 7px; background: #f8fafc; font-size: 8px; line-height: 1.2; overflow: hidden; }
            .dashboard-table-wrap td::before { flex: 0 1 43%; min-width: 0; font-size: 7.5px; line-height: 1.2; white-space: nowrap; word-break: normal; }
            .dashboard-order-value { display: -webkit-box; flex: 0 1 57%; min-width: 0; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; text-align: left; word-break: normal; overflow-wrap: normal; }
            .dashboard-order-value .muted { font-size: 7px; line-height: 1.15; }
            .dashboard-order-value .badge { padding: 2px 4px; font-size: 7px; white-space: nowrap; }
            .dashboard-order-value .order-id { padding: 2px 4px; border-radius: 5px; font-size: 8px; }
            .status-row { grid-template-columns: 78px minmax(0, 1fr) 38px; }
            .dashboard-section-title { align-items: center; flex-direction: row; margin-bottom: 6px; }
            .dashboard-section-title h2 { font-size: 13px; }
            .dashboard-link { width: auto; padding: 5px 7px; border-radius: 6px; font-size: 8px; }
        }
    </style>

    <div class="dashboard-hero">
        <section class="dashboard-welcome">
            <h1>الرئيسية</h1>
            <div class="dashboard-meta">
                <span class="dashboard-pill">👤 {{ auth()->user()->name }}</span>
                <span class="dashboard-pill">🧾 {{ $stats['orders'] }} طلب</span>
                <span class="dashboard-pill">💳 {{ $stats['paid_orders'] }} مدفوع</span>
            </div>
        </section>

    </div>

    @if (auth()->user()->hasAdminPermission('discounts_apply'))
        <section class="discount-panel">
            <h2>كود الخصم</h2>
            <form class="discount-form" method="post" action="{{ route('admin.discount-codes.store') }}">
                @csrf
                <div>
                    <label>كود الخصم</label>
                    <input name="discount_code" placeholder="مثال: STUDENT10" required>
                </div>
                <div>
                    <label>القيمة</label>
                    <input name="discount_amount" inputmode="numeric" placeholder="مثال: 10" required>
                </div>
                <button class="discount-submit" type="submit">تطبيق الخصم</button>
            </form>
            @if ($discountCodes->isNotEmpty())
                <div class="discount-codes-list">
                    @foreach ($discountCodes as $discountCode)
                        <span class="discount-chip">
                            {{ $discountCode->code }} - {{ $discountCode->amount }} ريال
                            <form class="discount-delete-form" method="post" action="{{ route('admin.discount-codes.destroy', $discountCode) }}" onsubmit="return confirm('هل تريد حذف كود الخصم؟')">
                                @csrf
                                @method('delete')
                                <button class="discount-delete" type="submit" aria-label="حذف كود الخصم">×</button>
                            </form>
                        </span>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="dashboard-stat-grid">
        <div class="dashboard-stat primary"><span>كل الطلبات</span><strong>{{ $stats['orders'] }}</strong><small>إجمالي الطلبات المسجلة</small></div>
        <div class="dashboard-stat"><span>طلبات جديدة</span><strong>{{ $stats['new_orders'] }}</strong><small>لم يتم التعامل معها بعد</small></div>
        <div class="dashboard-stat"><span>قيد العمل</span><strong>{{ $stats['in_progress_orders'] }}</strong><small>تم فتحها ولم تكتمل</small></div>
        <div class="dashboard-stat"><span>مكتملة</span><strong>{{ $stats['completed_orders'] }}</strong><small>طلبات منتهية</small></div>
        <div class="dashboard-stat"><span>العملاء</span><strong>{{ $stats['customers'] }}</strong><small>حسابات العملاء</small></div>
        <div class="dashboard-stat"><span>المستخدمين</span><strong>{{ $stats['admins'] }}</strong><small>حسابات إدارية</small></div>
        <div class="dashboard-stat"><span>المدفوع</span><strong>{{ $stats['paid_orders'] }}</strong><small>طلبات تم دفعها</small></div>
        <div class="dashboard-stat"><span>الإجمالي الكلي</span><strong>{{ $stats['grand_total'] }} ريال</strong><small>إجمالي قيمة الطلبات</small></div>
    </section>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="dashboard-section-title">
                <h2>آخر الطلبات</h2>
                @if (auth()->user()->hasAdminPermission('orders_view'))
                    <a class="dashboard-link" href="{{ route('admin.orders') }}">عرض الكل</a>
                @endif
            </div>
            @if ($latestOrders->isEmpty())
                <div class="empty">لا توجد طلبات حتى الآن.</div>
            @else
                <div class="dashboard-table-wrap">
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
                            @foreach ($latestOrders as $order)
                                @php
                                    $isPaid = $order->payment_status === 'paid';
                                    $isEffectivelyCompleted = $isPaid && in_array($order->status, ['completed', 'finished'], true);
                                    $displayStatus = $isEffectivelyCompleted
                                        ? 'مكتمل'
                                        : (in_array($order->status, ['completed', 'finished'], true) ? 'بانتظار الدفع' : $order->status);
                                @endphp
                                <tr>
                                    <td data-label="رقم الطلب"><span class="dashboard-order-value"><span class="order-id">#{{ $order->id }}</span></span></td>
                                    <td data-label="العميل"><span class="dashboard-order-value">{{ $order->user->name }}<br><span class="muted">{{ $order->user->phone }}</span></span></td>
                                    <td data-label="الخدمة"><span class="dashboard-order-value">{{ $serviceNames[$order->service_type] ?? $order->service_type }}</span></td>
                                    <td data-label="الحالة"><span class="dashboard-order-value"><span class="badge">{{ $displayStatus }}</span></span></td>
                                    <td data-label="الدفع"><span class="dashboard-order-value"><span class="badge">{{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}</span></span></td>
                                    <td data-label="الإجمالي"><span class="dashboard-order-value"><strong>{{ $order->grand_total }} ريال</strong></span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel">
            <div class="dashboard-section-title">
                <h2>مؤشرات سريعة</h2>
            </div>
            <div class="status-list">
                <div class="status-row">
                    <span>المدفوع</span>
                    <div class="status-track"><div class="status-fill green" style="width: {{ $paidPercent }}%;"></div></div>
                    <strong>{{ $paidPercent }}%</strong>
                </div>
                <div class="status-row">
                    <span>المكتمل</span>
                    <div class="status-track"><div class="status-fill" style="width: {{ $completedPercent }}%;"></div></div>
                    <strong>{{ $completedPercent }}%</strong>
                </div>
            </div>
            <div class="stats" style="margin-top: 16px;">
                <div class="stat"><span>إجمالي الطباعة</span><strong>{{ $stats['print_total'] }} ريال</strong></div>
                @foreach ($serviceNames as $serviceType => $serviceName)
                    <div class="stat"><span>{{ $serviceName }}</span><strong>{{ $stats['service_totals'][$serviceType] ?? 0 }} ريال</strong></div>
                @endforeach
                <div class="stat"><span>غير مدفوع</span><strong>{{ $stats['unpaid_orders'] }}</strong></div>
            </div>
        </section>
    </div>
@endsection
