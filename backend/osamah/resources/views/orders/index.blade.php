<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>طلباتي</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #ffffff; padding: clamp(16px, 2vw, 24px) clamp(12px, 1.6vw, 18px); position: fixed; top: 0; right: 0; z-index: 20; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 0; }
        .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; margin-bottom: 4px; }
        .header-actions { display: flex; flex-direction: column; align-items: stretch; gap: clamp(8px, 1.2vw, 12px); color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin-top: 24px; }
        .header-actions a { color: #f8fafc; text-decoration: none; }
        .header-user { display: block; color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin: 0 0 12px; line-height: 1.6; }
        .home-button { display: flex; align-items: center; gap: 8px; width: 100%; color: #f8fafc; background: rgba(255, 255, 255, 0.06); text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 10px; border: 1px solid transparent; text-align: right; line-height: 1.5; }
        .home-button:hover { background: #1e293b; border-color: #334155; }
        .settings-button { display: flex; align-items: center; gap: 8px; width: 100%; color: #ffffff; background: #0f4c81; text-decoration: none; font-weight: 800; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(96, 165, 250, 0.35); text-align: right; line-height: 1.5; }
        .settings-button:hover { background: #1d6fa5; border-color: #60a5fa; }
        .header-form { margin: 0; }
        .logout-button { width: 100%; color: #ffffff; background: #b91c1c; border: 1px solid rgba(248, 113, 113, 0.5); font-weight: 800; padding: 10px 12px; border-radius: 10px; text-align: center; line-height: 1.5; cursor: pointer; }
        .logout-button:hover { background: #dc2626; border-color: #f87171; }
        .mobile-menu-toggle { display: none; align-items: center; justify-content: center; gap: 7px; border: 1px solid rgba(148, 163, 184, 0.28); border-radius: 10px; background: rgba(255, 255, 255, 0.08); color: #ffffff; padding: 9px 11px; font-weight: 900; font-family: inherit; cursor: pointer; }
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
        .uploaded-file-name { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-width: 0; }
        .uploaded-file-name span { min-width: 0; overflow-wrap: anywhere; word-break: normal; font-weight: 900; }
        .uploaded-file-view { flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; padding: 7px 10px; border-radius: 8px; background: #2563eb; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .uploaded-file-view:hover { background: #1d4ed8; }
        .paid { background: #dcfce7; color: #166534; }
        .unpaid { background: #fef3c7; color: #92400e; }
        .done { background: #e0f2fe; color: #075985; }
        .open { background: #f1f5f9; color: #334155; }
        .cancelled { background: #fee2e2; color: #991b1b; }
        .actions { display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 8px; min-width: 0; }
        .actions > .action,
        .actions > .inline-form { flex: 0 0 auto; width: 128px; }
        .action { display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 40px; color: #ffffff; background: #0f172a; text-decoration: none; font-weight: 900; padding: 9px 12px; border-radius: 8px; border: 0; cursor: pointer; font-family: inherit; font-size: 13px; text-align: center; line-height: 1.4; }
        .notice-action { position: relative; }
        .order-notice-dot { position: absolute; top: -4px; left: -4px; width: 8px; height: 8px; border-radius: 999px; background: #dc2626; box-shadow: 0 0 0 2px #ffffff; }
        .action.secondary { background: #047857; }
        .action.ghost { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .action.danger { background: #b91c1c; }
        .action.invoice-button { background: #0f4c81; color: #ffffff; border: 1px solid #2563eb; }
        .action.invoice-button:hover { background: #1d6fa5; }
        .inline-form { display: flex; margin: 0; width: 100%; }
        .order-discount-box { margin-top: 10px; padding: 12px; border-radius: 12px; border: 1px solid #fbcfe8; background: #fdf2f8; }
        .order-discount-title { margin: 0 0 8px; color: #831843; font-size: 13px; font-weight: 900; }
        .order-discount-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; align-items: center; }
        .order-discount-form input { width: 100%; padding: 10px 11px; border: 1px solid #f9a8d4; border-radius: 9px; background: #ffffff; color: #111827; font-size: 13px; font-weight: 800; }
        .order-discount-form button { min-height: 39px; padding: 10px 13px; border: 0; border-radius: 9px; background: #db2777; color: #ffffff; font-size: 13px; font-weight: 900; cursor: pointer; }
        .order-discount-form button:hover { background: #be185d; }
        .order-discount-status { margin-top: 7px; color: #047857; font-size: 12px; font-weight: 900; }
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
        .orders-table { display: block; border-radius: 0; overflow: visible; }
        .orders-table thead { display: none; }
        .orders-table tbody { display: grid; gap: 14px; }
        .orders-table tr { display: grid; grid-template-columns: 1fr; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07); }
        .orders-table td { display: grid; grid-template-columns: minmax(110px, 32%) minmax(0, 1fr); align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid #edf2f7; white-space: normal; overflow-wrap: anywhere; }
        .orders-table td:last-child { border-bottom: 0; background: #f8fafc; }
        .orders-table td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 900; }
        .orders-table .actions { width: min(240px, 100%); }
        .orders-table .actions > .action,
        .orders-table .actions > .inline-form { width: 100%; }
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
        .modal-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(128px, 1fr)); gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .delivered-files-list { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
        .delivered-file-item { display: flex; flex-direction: column; align-items: stretch; gap: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; }
        .delivered-file-name { color: #0f172a; font-weight: 900; line-height: 1.6; word-break: break-word; }
        .delivered-file-buttons { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .invoice-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .invoice-toolbar .action { padding: 12px 18px; min-width: 142px; justify-content: center; border-radius: 10px; font-size: 15px; }
        .invoice-document { color: #111827; background: #ffffff; border: 1px solid #dbe3ef; border-radius: 14px; padding: clamp(14px, 3vw, 24px); box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08); }
        .invoice-head { display: flex; justify-content: space-between; gap: 16px; align-items: stretch; padding: 18px; border-radius: 12px; background: #0f172a; color: #ffffff; margin-bottom: 18px; }
        .invoice-brand { display: flex; align-items: center; gap: 12px; }
        .invoice-logo { width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; background: #ffffff; color: #0f172a; font-size: 24px; font-weight: 900; }
        .invoice-head h2 { margin: 0; font-size: 28px; color: #ffffff; }
        .invoice-head p { margin: 4px 0 0; color: #cbd5e1; }
        .invoice-number { min-width: 140px; text-align: center; padding: 12px; border: 1px solid rgba(255,255,255,0.18); border-radius: 12px; background: rgba(255,255,255,0.08); }
        .invoice-number span, .invoice-grid span, .invoice-totals span { display: block; color: #64748b; font-size: 12px; font-weight: 900; margin-bottom: 5px; }
        .invoice-number span { color: #cbd5e1; }
        .invoice-number strong { display: block; font-size: 26px; color: #ffffff; }
        .invoice-number small { display: inline-flex; margin-top: 7px; padding: 4px 9px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: 12px; font-weight: 900; }
        .invoice-section-title { margin: 16px 0 10px; color: #0f172a; font-size: 15px; font-weight: 900; }
        .invoice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
        .invoice-grid div, .invoice-totals div { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 11px; }
        .invoice-grid strong { line-height: 1.6; }
        .invoice-grid .full { grid-column: 1 / -1; }
        .invoice-table-wrap { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 10px; }
        .invoice-table-wrap table { min-width: 760px; border-radius: 0; }
        .invoice-table-wrap th { background: #eef2f7; color: #0f172a; }
        .invoice-table-wrap td:last-child { font-weight: 900; color: #0f172a; background: #f8fafc; }
        .invoice-summary { display: grid; grid-template-columns: minmax(220px, 0.8fr) minmax(0, 1.2fr); gap: 12px; align-items: stretch; margin-top: 16px; }
        .invoice-summary-note { display: flex; flex-direction: column; justify-content: center; gap: 7px; padding: 14px; border: 1px solid #dbe3ef; border-radius: 12px; background: #f8fafc; }
        .invoice-summary-note strong { color: #0f172a; }
        .invoice-summary-note span { color: #64748b; font-size: 13px; line-height: 1.6; }
        .invoice-totals { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .invoice-totals .grand { background: #0f172a; color: #ffffff; }
        .invoice-totals .grand span { color: #cbd5e1; }
        .invoice-totals .grand strong { font-size: 22px; }
        .invoice-note { margin-top: 16px; color: #64748b; font-size: 12px; text-align: center; }
        @media (max-width: 1100px) {
            .detail-table-wrap { border: 0; background: transparent; overflow: visible; }
            .detail-table-wrap table { min-width: 0; display: block; }
            .detail-table-wrap thead { display: none; }
            .detail-table-wrap tbody { display: grid; gap: 12px; }
            .detail-table-wrap tr { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
            .detail-table-wrap td { display: grid; grid-template-columns: minmax(92px, 34%) minmax(0, 1fr); align-items: center; gap: 8px; min-width: 0; padding: 9px 10px; border: 1px solid #edf2f7; border-radius: 9px; background: #f8fafc; white-space: normal; overflow-wrap: anywhere; word-break: normal; }
            .detail-table-wrap td:first-child { min-width: 0; grid-column: 1 / -1; }
            .detail-table-wrap td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 900; line-height: 1.5; }
            .detail-table-wrap .price-cell { white-space: normal; }
            .detail-table-wrap .inline-form,
            .detail-table-wrap .action { width: 100%; }
        }
        @media (max-width: 820px) {
            :root { --sidebar-width: 0px; --page-gap: 10px; }
            body { padding: 0; }
            .header { position: sticky; top: 0; width: 100%; min-height: 0; max-height: none; padding: 12px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16); }
            .header-inner { height: auto; display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 10px; }
            .mobile-menu-toggle { display: inline-flex; }
            .header-actions { grid-column: 1 / -1; margin-top: 0; display: none; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .header.menu-open .header-actions { display: grid; }
            .header-user { grid-column: 1 / -1; margin: 0; }
            main { width: calc(100% - 20px); margin: 14px auto 24px; padding: 0; }
            .page-title { align-items: flex-start; flex-direction: column; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .detail-grid, .totals-grid { grid-template-columns: 1fr; }
            .home-button, .settings-button, .logout-button, .action, .inline-form { width: 100%; justify-content: center; text-align: center; }
            .actions { display: flex; flex-direction: column; justify-content: stretch; }
            .actions > .action,
            .actions > .inline-form { width: 100%; }
            .modal-actions { grid-template-columns: repeat(auto-fit, minmax(112px, 1fr)); }
            .invoice-head, .invoice-grid, .invoice-totals, .invoice-summary { grid-template-columns: 1fr; }
            .invoice-head { flex-direction: column; }
            .invoice-table-wrap { border: 0; overflow: visible; }
            .invoice-table-wrap table { min-width: 0; display: block; }
            .invoice-table-wrap thead { display: none; }
            .invoice-table-wrap tbody { display: grid; gap: 10px; }
            .invoice-table-wrap tr { display: grid; gap: 9px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; }
            .invoice-table-wrap td { display: grid; grid-template-columns: minmax(100px, 38%) minmax(0, 1fr); align-items: start; gap: 8px; padding: 0; border-bottom: 0; white-space: normal; word-break: break-word; }
            .invoice-table-wrap td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 900; }
            .invoice-table-wrap td:last-child { padding: 9px; border-radius: 8px; }
        }
        @media (max-width: 640px) {
            .files-panel { padding: 12px; }
            .detail-table-wrap tr { grid-template-columns: 1fr; }
            .detail-table-wrap td { grid-template-columns: minmax(88px, 36%) minmax(0, 1fr); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="brand">Mr-Student</div>
            <button class="mobile-menu-toggle" type="button" onclick="toggleMobileHeader(this, event)" aria-expanded="false">☰ القائمة</button>
            <div class="header-actions">
                <span class="header-user">👤 {{ auth()->user()->name }}</span>
                <a class="home-button" href="{{ route('home') }}">🏠 الصفحة الرئيسية</a>
                <a class="settings-button" href="{{ route('account.settings') }}">⚙️ إعداداتي</a>
                <form class="header-form" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">🚪 خروج</button>
                </form>
            </div>
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
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الخدمة</th>
                            <th>الملفات</th>
                            <th>حالة الدفع</th>
                            <th>حالة الطلب</th>
                            <th>الإجمالي</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $serviceNames = [
                                    'notes' => 'مذكرات',
                                    'books' => 'كتب',
                                    'color_printing' => 'طباعة الملفات بالألوان',
                                    'thesis' => 'ماجستير',
                                    'phd' => 'دكتوراه',
                                    'formatting' => 'تنسيق الرسائل الجامعية',
                                    'research' => 'إنشاء بحث',
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
                        $isCompleted = $isPaid && $order->status === 'completed';
                        $isCancelled = $order->status === 'cancelled';
                        $displayStatus = $isCompleted
                            ? 'مكتمل'
                            : (in_array($order->status, ['completed', 'finished'], true) ? 'بانتظار الدفع' : ($statusNames[$order->status] ?? $order->status));
                        $hasDeliveredFile = in_array($order->service_type, ['formatting', 'research'], true) && $order->deliveredFiles->isNotEmpty();
                        $hasUndownloadedDeliveredFiles = $hasDeliveredFile && $order->deliveredFiles->contains(fn ($file) => blank($file->customer_downloaded_at));
                                $projectTypes = $order->service_type === 'thesis'
                                    ? $order->files
                                        ->pluck('thesis_project_type')
                                        ->filter()
                                        ->unique()
                                        ->map(fn ($type) => $projectNames[$type] ?? $type)
                                        ->values()
                                    : collect();
                                $missingRequirements = collect();
                                if (in_array($order->service_type, ['notes', 'books'], true) && $order->files->contains(fn ($file) => blank($file->binding_type))) {
                                    $missingRequirements->push('اختيار نوع التغليف لكل ملف.');
                                }
                                if ($order->service_type === 'color_printing' && $order->files->contains(fn ($file) => blank($file->binding_type))) {
                                    $missingRequirements->push('اختيار نوع التغليف لكل ملف.');
                                }
                                if (in_array($order->service_type, ['thesis', 'phd'], true) && $order->files->contains(fn ($file) => blank($file->cover_color) || blank($file->writing_color))) {
                                    $missingRequirements->push('اختيار لون الرسالة ولون الكتابة لكل ملف.');
                                }
                                if ($order->service_type === 'thesis' && $order->files->contains(fn ($file) => $file->file_type === 'pdf' && blank($file->thesis_project_type))) {
                                    $missingRequirements->push('اختيار نوع مشروع الرسالة لكل ملف PDF.');
                                }
                                $serviceDetail = $order->service_type === 'thesis' && $projectTypes->isNotEmpty()
                                    ? $projectTypes->implode('، ')
                                    : 'اضغط عرض الطلب للتفاصيل';
                                $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                                $createdAtText = $dayNames[$order->created_at->dayOfWeek] . ' - ' . $order->created_at->format('Y-m-d H:i');
                            @endphp
                            <tr>
                                <td data-label="رقم الطلب">#{{ $order->id }}</td>
                                <td data-label="الخدمة">
                                    <div class="service-title">{{ $serviceNames[$order->service_type] ?? $order->service_type }}</div>
                                    <div class="service-detail">{{ $serviceDetail }}</div>
                                </td>
                                <td data-label="الملفات">{{ $order->files_count }}</td>
                                <td data-label="حالة الدفع">
                                    <span class="badge {{ $isPaid ? 'paid' : 'unpaid' }}">
                                        {{ $isPaid ? 'مدفوع' : 'غير مدفوع' }}
                                    </span>
                                </td>
                                <td data-label="حالة الطلب">
                                    <span class="badge {{ $isCompleted ? 'done' : ($isCancelled ? 'cancelled' : 'open') }}">
                                        {{ $displayStatus }}
                                    </span>
                                </td>
                                <td data-label="الإجمالي">{{ $order->grand_total }} ريال</td>
                                <td data-label="تاريخ الإنشاء" data-local-datetime="{{ $order->created_at->toIso8601String() }}">{{ $createdAtText }}</td>
                                <td data-label="الإجراء">
                                    @if (! $isPaid)
                                        <div class="order-discount-box">
                                            <div class="order-discount-title">كود الخصم قبل الدفع</div>
                                            <form class="order-discount-form" method="post" action="{{ route('cart.discount.apply', $order) }}">
                                                @csrf
                                                @method('patch')
                                                <input name="discount_code" value="{{ $order->discount_code }}" placeholder="اكتب كود الخصم" required>
                                                <button type="submit">تطبيق</button>
                                            </form>
                                            @if ($order->discount_amount > 0)
                                                <div class="order-discount-status">تم تطبيق {{ $order->discount_code }}: خصم {{ $order->discount_amount }} ريال</div>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="actions">
                                        <button class="action ghost" type="button" onclick="openOrderModal('orderModal{{ $order->id }}')">عرض الطلب</button>
                                        @if ($hasDeliveredFile)
                                            <button class="action secondary notice-action" type="button" data-delivered-files-button="{{ $order->id }}" onclick="openOrderModal('deliveredFilesModal{{ $order->id }}')">
                                                الملفات المستلمة
                                                @if ($hasUndownloadedDeliveredFiles)
                                                    <span class="order-notice-dot" data-delivered-files-dot="{{ $order->id }}" aria-label="ملفات لم يتم تحميلها"></span>
                                                @endif
                                            </button>
                                        @endif
                                        @if ($isPaid)
                                            <button class="action invoice-button" type="button" onclick="openOrderModal('invoiceModal{{ $order->id }}')">الفاتورة</button>
                                        @endif
                                        @if (! $isPaid && $order->files_count > 0)
                                            <a class="action secondary" href="{{ route('cart.show', $order) }}">إكمال الدفع</a>
                                        @elseif (! $isPaid)
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
                            'books' => 'كتب',
                            'color_printing' => 'طباعة الملفات بالألوان',
                            'thesis' => 'ماجستير',
                            'phd' => 'دكتوراه',
                            'formatting' => 'تنسيق الرسائل الجامعية',
                            'research' => 'إنشاء بحث',
                        ];
                        $serviceFullNames = [
                            'notes' => 'طباعة المذكرات وملفات ال PDF',
                            'books' => 'طباعة وتجليد كتب كعب جلد طبيعي',
                            'color_printing' => 'طباعة الملفات بالألوان',
                            'thesis' => 'طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج',
                            'phd' => 'طباعة وتجليد رسالة دكتوراه',
                            'formatting' => 'تنسيق الرسائل الجامعية',
                            'research' => 'إنشاء بحث',
                        ];
                        $noPrintServices = ['formatting', 'research'];
                        $hasDeliveredFile = in_array($order->service_type, $noPrintServices, true) && $order->deliveredFiles->isNotEmpty();
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
                        $deliveryMethodNames = [
                            'branch_pickup' => 'استلام من الفرع',
                            'islamic_university_delivery' => 'توصيل داخل الجامعة الإسلامية',
                            'madinah_delivery' => 'توصيل داخل المدينة المنورة',
                            'redbox_delivery' => 'خارج المدينة المنورة عبر RedBox',
                        ];
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
                        if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true) && $order->files->contains(fn ($file) => blank($file->binding_type))) {
                            $missingRequirements->push('اختيار نوع التغليف لكل ملف.');
                        }
                        if (in_array($order->service_type, ['thesis', 'phd'], true) && $order->files->contains(fn ($file) => blank($file->cover_color) || blank($file->writing_color))) {
                            $missingRequirements->push('اختيار لون الرسالة ولون الكتابة لكل ملف.');
                        }
                        if ($order->service_type === 'thesis' && $order->files->contains(fn ($file) => $file->file_type === 'pdf' && blank($file->thesis_project_type))) {
                            $missingRequirements->push('اختيار نوع مشروع الرسالة لكل ملف PDF.');
                        }
                        $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                        $createdAtText = $dayNames[$order->created_at->dayOfWeek] . ' - ' . $order->created_at->format('Y-m-d H:i');
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
                                    <div class="detail-card"><span>حالة الطلب</span><strong>{{ $displayStatus }}</strong></div>
                                    <div class="detail-card"><span>الدفع</span><strong>{{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}</strong></div>
                                    <div class="detail-card"><span>تاريخ إنشاء الطلب</span><strong data-local-datetime="{{ $order->created_at->toIso8601String() }}">{{ $createdAtText }}</strong></div>
                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                                        <div class="detail-card full"><span>الاستلام والتوصيل</span><strong>
                                            {{ $deliveryMethodNames[$order->delivery_method] ?? '-' }}
                                            @if ($order->delivery_method === 'islamic_university_delivery')
                                                - وحدة {{ $order->delivery_unit }} / دور {{ $order->delivery_floor }} / غرفة {{ $order->delivery_room }}
                                            @elseif (in_array($order->delivery_method, ['madinah_delivery', 'redbox_delivery'], true))
                                                - {{ $order->delivery_city }} / حي {{ $order->delivery_district }} / شارع {{ $order->delivery_street }}
                                                @if ($order->delivery_map_url)
                                                    - <a href="{{ $order->delivery_map_url }}" target="_blank" rel="noopener">رابط الموقع</a>
                                                @endif
                                            @endif
                                        </strong></div>
                                    @endif
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
                                                    @if ($order->service_type !== 'research')
                                                        <th>النوع</th>
                                                    @endif
                                                    @if ($order->service_type === 'thesis')
                                                        <th>مشروع الرسالة</th>
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
                                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                                                        <th>نوع الطباعة</th>
                                                    @endif
                                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true))
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
                                                    <th>إجمالي الملف</th>
                                                    @if ($order->payment_status !== 'paid')
                                                        <th>حذف</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                    @foreach ($order->files as $file)
                                                    <tr>
                                                        <td data-label="الملف">
                                                            <div class="uploaded-file-name">
                                                                <span>{{ $file->original_name }}</span>
                                                                <a class="uploaded-file-view" href="{{ route('orders.file.view', ['order' => $order, 'file' => $file]) }}" target="_blank" rel="noopener">عرض الملف</a>
                                                            </div>
                                                        </td>
                                                        @if ($order->service_type !== 'research')
                                                            <td data-label="النوع">{{ strtoupper($file->file_type) }}</td>
                                                        @endif
                                                        @if ($order->service_type === 'thesis')
                                                            <td data-label="مشروع الرسالة">{{ $projectNames[$file->thesis_project_type] ?? '-' }}</td>
                                                        @endif
                            @if (in_array($order->service_type, ['thesis', 'phd'], true))
                                <td data-label="الجامعة/المعهد">{{ $file->university_name ?: '-' }}</td>
                                <td data-label="لون الرسالة">{{ $coverColorNames[$file->cover_color] ?? '-' }}</td>
                                <td data-label="لون الكتابة">{{ $writingColorNames[$file->writing_color] ?? '-' }}</td>
                            @endif
                                                        <td data-label="الصفحات">{{ $file->pages }}</td>
                                                        @if ($order->service_type !== 'research')
                                                            <td data-label="النسخ">{{ $file->copies }}</td>
                                                        @endif
                                                        @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                                                            <td data-label="نوع الطباعة">{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : (['one_side' => 'وجه واحد', 'two_sides' => 'وجهين'][$file->print_sides] ?? 'وجهين') }}</td>
                                                        @endif
                                                        @if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true))
                                                            <td data-label="حجم الصفحة">{{ ['A4' => 'A4', 'A3' => 'A3', 'A5' => 'A5', 'B5' => 'B5'][$file->page_size] ?? 'A4' }}</td>
                                                        @endif
                                                        @if (in_array($order->service_type, ['notes', 'books'], true))
                                                            <td data-label="لون الورق">{{ ['white' => 'أبيض', 'yellow' => 'أصفر'][$file->paper_color] ?? 'أبيض' }}</td>
                                                        @endif
                                                        @if (! in_array($order->service_type, $noPrintServices, true))
                                                            <td data-label="{{ $bindingLabel }}">{{ $bindingNames[$file->binding_type] ?? '-' }}</td>
                                                        @endif
                                                        @if (! in_array($order->service_type, $noPrintServices, true))
                                                            <td class="price-cell" data-label="سعر الطباعة">{{ $file->print_price }} ريال</td>
                                                        @endif
                                                        <td class="price-cell" data-label="{{ $bindingPriceLabel }}">{{ $file->binding_price }} ريال</td>
                                                        <td class="price-cell" data-label="إجمالي الملف">{{ $file->total_price }} ريال</td>
                                                        @if ($order->payment_status !== 'paid')
                                                            <td data-label="حذف">
                                                                <form class="inline-form" method="post" action="{{ url('/order-files/' . $file->id) }}" onsubmit="return confirm('هل تريد حذف هذا الملف من الطلب؟')">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <button class="action danger" type="submit">حذف</button>
                                                                </form>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="totals-grid">
                                    @if (! in_array($order->service_type, $noPrintServices, true))
                                        <div class="total-card"><span>سعر الطباعة</span><strong>{{ $order->print_total }} ريال</strong></div>
                                    @endif
                                    <div class="total-card"><span>{{ $bindingPriceLabel }}</span><strong>{{ $order->binding_total }} ريال</strong></div>
                                    @if ($order->discount_amount > 0)
                                        <div class="total-card"><span>الخصم {{ $order->discount_code }}</span><strong>- {{ $order->discount_amount }} ريال</strong></div>
                                    @endif
                                    @if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true))
                                        <div class="total-card"><span>رسوم التوصيل</span><strong>{{ $order->delivery_fee }} ريال</strong></div>
                                    @endif
                                    <div class="total-card"><span>الإجمالي</span><strong>{{ $order->grand_total }} ريال</strong></div>
                                </div>

                                <div class="modal-actions">
                                    @if ($order->payment_status !== 'paid' && $order->files_count > 0)
                                        <a class="action secondary" href="{{ route('cart.show', $order) }}">إكمال الدفع</a>
                                    @endif
                                    @if ($order->payment_status === 'paid')
                                        <button class="action invoice-button" type="button" onclick="closeOrderModal(null, 'orderModal{{ $order->id }}'); openOrderModal('invoiceModal{{ $order->id }}')">الفاتورة</button>
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

                    @if ($order->payment_status === 'paid')
                        <div class="modal-backdrop" id="invoiceModal{{ $order->id }}" tabindex="-1" onclick="closeOrderModal(event, 'invoiceModal{{ $order->id }}')">
                            <div class="modal" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
                                <div class="modal-head">
                                    <div class="modal-title">فاتورة الطلب #{{ $order->id }}</div>
                                    <button class="modal-close" type="button" onclick="closeOrderModal(null, 'invoiceModal{{ $order->id }}')">إغلاق</button>
                                </div>
                                <div class="modal-body">
                                    @include('shared.invoice', ['order' => $order, 'invoiceId' => 'customerInvoice' . $order->id])
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (in_array($order->service_type, $noPrintServices, true))
                        <div class="modal-backdrop" id="deliveredFilesModal{{ $order->id }}" tabindex="-1" onclick="closeOrderModal(event, 'deliveredFilesModal{{ $order->id }}')">
                            <div class="modal" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
                                <div class="modal-head">
                                    <div class="modal-title">الملفات المستلمة للطلب #{{ $order->id }}</div>
                                    <button class="modal-close" type="button" onclick="closeOrderModal(null, 'deliveredFilesModal{{ $order->id }}')">إغلاق</button>
                                </div>
                                <div class="modal-body">
                                    @if ($hasDeliveredFile)
                                        <div class="delivered-files-list">
                                            @foreach ($order->deliveredFiles as $deliveredFile)
                                                <div class="delivered-file-item">
                                                    <div>
                                                        <div class="delivered-file-name">{{ $deliveredFile->original_name }}</div>
                                                        <div class="service-detail" data-local-datetime="{{ $deliveredFile->created_at->toIso8601String() }}">{{ $deliveredFile->created_at->format('Y-m-d H:i') }}</div>
                                                    </div>
                                                    <div class="delivered-file-buttons">
                                                        <a class="action ghost" href="{{ route('orders.delivered-file', ['order' => $order, 'deliveredFile' => $deliveredFile, 'view' => 1]) }}" target="_blank" rel="noopener">عرض</a>
                                                        <a class="action secondary notice-action" href="{{ route('orders.delivered-file', [$order, $deliveredFile]) }}" data-delivered-file-download data-order-id="{{ $order->id }}" data-delivered-file-id="{{ $deliveredFile->id }}">
                                                            تحميل
                                                            @if (blank($deliveredFile->customer_downloaded_at))
                                                                <span class="order-notice-dot" data-delivered-file-dot="{{ $deliveredFile->id }}" aria-label="ملف لم يتم تحميله"></span>
                                                            @endif
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="empty">لم يتم إرفاق ملفات مستلمة بعد.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </section>
    </main>
    <script>
        function toggleMobileHeader(button, event) {
            event?.stopPropagation();
            const header = button.closest('.header');
            const isOpen = header.classList.toggle('menu-open');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        document.addEventListener('click', (event) => {
            const header = document.querySelector('.header.menu-open');
            if (!header || header.contains(event.target)) return;

            header.classList.remove('menu-open');
            header.querySelector('.mobile-menu-toggle')?.setAttribute('aria-expanded', 'false');
        });

        function localizeDateTimes(root = document) {
            const formatter = new Intl.DateTimeFormat('ar-SA-u-ca-gregory', {
                weekday: 'long',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            });

            root.querySelectorAll('[data-local-datetime]').forEach((element) => {
                const date = new Date(element.dataset.localDatetime);
                if (Number.isNaN(date.getTime())) return;

                element.textContent = formatter.format(date).replace('،', ' -');
            });
        }

        localizeDateTimes();

        function openOrderModal(id) {
            const modal = document.getElementById(id);
            modal?.classList.add('active');
            localizeDateTimes(modal);
            modal?.focus();
            document.body.style.overflow = 'hidden';
        }

        function closeOrderModal(event, id) {
            if (event && event.target.id !== id) return;
            document.getElementById(id)?.classList.remove('active');
            document.body.style.overflow = '';
        }

        function printInvoice(invoiceId) {
            const invoice = document.getElementById(invoiceId);
            if (!invoice) return;

            const printWindow = window.open('', '_blank');
            if (!printWindow) return;

            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="ar" dir="rtl">
                <head>
                    <meta charset="utf-8">
                    <title>فاتورة</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; direction: rtl; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #e5e7eb; padding: 9px; text-align: right; font-size: 12px; }
                        th { background: #f8fafc; }
                        .invoice-document { border: 0; padding: 0; }
                        .invoice-head { display: flex; justify-content: space-between; gap: 16px; border-bottom: 3px solid #0f172a; margin-bottom: 16px; padding-bottom: 14px; }
                        .invoice-brand { display: flex; align-items: center; gap: 10px; }
                        .invoice-logo { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; background: #0f172a; color: #fff; font-size: 22px; font-weight: 900; }
                        .invoice-head h2 { margin: 0; font-size: 28px; }
                        .invoice-number { text-align: left; }
                        .invoice-grid, .invoice-totals, .invoice-summary { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 14px 0; }
                        .invoice-grid div, .invoice-totals div, .invoice-summary-note { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
                        .invoice-grid span, .invoice-totals span, .invoice-number span { display: block; color: #64748b; font-size: 11px; font-weight: 700; margin-bottom: 4px; }
                        .invoice-totals .grand { background: #0f172a; color: #fff; }
                        .invoice-section-title { margin: 14px 0 8px; font-weight: 900; }
                        .invoice-note { text-align: center; color: #64748b; margin-top: 18px; }
                        .invoice-toolbar { display: none; }
                    </style>
                </head>
                <body>${invoice.outerHTML}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop.active').forEach((modal) => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
            }
        });

        function clearDeliveredFileNotice(link) {
            if (!link || link.dataset.noticeCleared === 'true') return;

            link.dataset.noticeCleared = 'true';
            const orderId = link.dataset.orderId;
            const deliveredFileId = link.dataset.deliveredFileId;

            document.querySelectorAll(`[data-delivered-file-dot="${deliveredFileId}"]`).forEach((dot) => dot.remove());
            document.querySelectorAll(`[data-delivered-files-dot="${orderId}"]`).forEach((dot) => dot.remove());
            document.querySelectorAll('.customer-notice-dot').forEach((dot) => dot.remove());
        }

        document.addEventListener('pointerdown', (event) => {
            const link = event.target.closest('[data-delivered-file-download]');
            if (!link) return;

            clearDeliveredFileNotice(link);
        });

        document.addEventListener('click', (event) => {
            const link = event.target.closest('[data-delivered-file-download]');
            if (!link) return;

            clearDeliveredFileNotice(link);
        });

        const openOrderId = new URLSearchParams(window.location.search).get('open_order');
        if (openOrderId) {
            openOrderModal(`orderModal${openOrderId}`);
        }
    </script>
    @include('shared.chat-widget')
</body>
</html>
