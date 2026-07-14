@extends('admin.layout')

@section('title', 'الرئيسية - لوحة النظام')

@section('content')
    @php
        $serviceNames = [
            'notes' => 'مذكرات',
            'thesis' => 'ماجستير',
            'phd' => 'دكتوراه',
            'formatting' => 'تنسيق الرسائل الجامعية',
            'research' => 'إنشاء بحث',
        ];
        $latestOrders = $orders->take(6);
        $paidPercent = $stats['orders'] > 0 ? round(($stats['paid_orders'] / $stats['orders']) * 100) : 0;
        $completedPercent = $stats['orders'] > 0 ? round(($stats['completed_orders'] / $stats['orders']) * 100) : 0;
    @endphp

    <style>
        .dashboard-hero { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(260px, 0.7fr); gap: 16px; margin-bottom: 18px; }
        .dashboard-welcome { position: relative; overflow: hidden; min-height: 170px; padding: clamp(18px, 3vw, 26px); border-radius: 12px; background: #0f172a; color: #ffffff; box-shadow: 0 20px 42px rgba(15, 23, 42, 0.18); }
        .dashboard-welcome h1 { color: #ffffff; margin-bottom: 8px; }
        .dashboard-welcome p { max-width: 680px; color: #cbd5e1; line-height: 1.8; margin: 0; }
        .dashboard-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .dashboard-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 10px; border-radius: 999px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14); color: #f8fafc; font-size: 12px; font-weight: 900; }
        .dashboard-actions { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .dashboard-action { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-radius: 10px; background: #ffffff; border: 1px solid #e5e7eb; color: #0f172a; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); }
        .dashboard-action span { color: #64748b; font-size: 12px; font-weight: 800; }
        .dashboard-action strong { color: #0f172a; }
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
        }
        @media (max-width: 560px) {
            .dashboard-stat-grid { grid-template-columns: 1fr; }
            .status-row { grid-template-columns: 78px minmax(0, 1fr) 38px; }
        }
    </style>

    <div class="dashboard-hero">
        <section class="dashboard-welcome">
            <h1>الرئيسية</h1>
            <p>لوحة متابعة مختصرة لنشاط النظام، الطلبات الجديدة، المدفوعات، والإيرادات. نفس الصفحة تظهر للمدير والمستخدمين بنفس الهوية والترتيب.</p>
            <div class="dashboard-meta">
                <span class="dashboard-pill">👤 {{ auth()->user()->name }}</span>
                <span class="dashboard-pill">🧾 {{ $stats['orders'] }} طلب</span>
                <span class="dashboard-pill">💳 {{ $stats['paid_orders'] }} مدفوع</span>
            </div>
        </section>

        <section class="dashboard-actions">
            @if (auth()->user()->hasAdminPermission('orders_view'))
                <a class="dashboard-action" href="{{ route('admin.orders') }}">
                    <strong>الطلبات</strong>
                    <span>متابعة الطلبات والملفات</span>
                </a>
            @endif
            @if (auth()->user()->hasAnyAdminPermission(['users_view', 'users_create', 'users_update', 'users_delete', 'users_permissions_manage']))
                <a class="dashboard-action" href="{{ route('admin.users') }}">
                    <strong>المستخدمين</strong>
                    <span>إدارة الموظفين والصلاحيات</span>
                </a>
            @endif
            @if (auth()->user()->hasAnyAdminPermission(['customers_view', 'customers_create', 'customers_update', 'customers_delete']))
                <a class="dashboard-action" href="{{ route('admin.customers') }}">
                    <strong>العملاء</strong>
                    <span>إدارة حسابات العملاء</span>
                </a>
            @endif
        </section>
    </div>

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
                                    <td><span class="order-id">#{{ $order->id }}</span></td>
                                    <td>{{ $order->user->name }}<br><span class="muted">{{ $order->user->phone }}</span></td>
                                    <td>{{ $serviceNames[$order->service_type] ?? $order->service_type }}</td>
                                    <td><span class="badge">{{ $displayStatus }}</span></td>
                                    <td><span class="badge">{{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}</span></td>
                                    <td><strong>{{ $order->grand_total }} ريال</strong></td>
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
                <div class="stat"><span>إجمالي التجليد/التغليف/التنسيق</span><strong>{{ $stats['binding_total'] }} ريال</strong></div>
                <div class="stat"><span>غير مدفوع</span><strong>{{ $stats['unpaid_orders'] }}</strong></div>
            </div>
        </section>
    </div>
@endsection
