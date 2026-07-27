<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('shared.tab-brand')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 16px; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .invoice-page { width: min(1120px, 100%); margin: 0 auto; }
        .page-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
        .page-title { margin: 0; color: #0f172a; font-size: clamp(18px, 4vw, 24px); }
        .action { display: inline-flex; align-items: center; justify-content: center; width: auto; min-height: 42px; padding: 10px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #fff; font-family: inherit; font-size: 14px; font-weight: 900; line-height: 1.4; text-align: center; text-decoration: none; cursor: pointer; }
        .action.secondary { background: #047857; }
        .action.invoice-button, .back-button { background: #0f4c81; border: 1px solid #2563eb; }
        .invoice-toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 12px; }
        .invoice-toolbar .action { flex: 1 1 0; min-width: 0; padding: 12px 8px; border-radius: 10px; font-size: 15px; }
        .invoice-document { color: #111827; background: #fff; border: 1px solid #dbe3ef; border-radius: 14px; padding: clamp(12px, 2vw, 18px); box-shadow: 0 18px 50px rgba(15, 23, 42, .08); }
        .invoice-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background: #0f172a; color: #fff; margin-bottom: 12px; }
        .invoice-brand { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .invoice-logo { width: 38px; height: 38px; flex: 0 0 auto; border-radius: 9px; overflow: hidden; background: #fff; }
        .invoice-logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .invoice-head h2 { margin: 0; color: #fff; font-size: 18px; }
        .invoice-head p { margin: 2px 0 0; color: #cbd5e1; font-size: 10px; }
        .invoice-number { display: flex; align-items: center; gap: 6px; min-width: 0; padding: 6px 8px; border: 1px solid rgba(255,255,255,.18); border-radius: 9px; background: rgba(255,255,255,.08); text-align: center; }
        .invoice-number span, .invoice-grid span, .invoice-totals span { display: block; margin: 0; color: #64748b; font-size: 10px; font-weight: 900; }
        .invoice-number span { color: #cbd5e1; }
        .invoice-number strong { color: #fff; font-size: 16px; white-space: nowrap; }
        .invoice-number small { padding: 3px 6px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: 9px; font-weight: 900; white-space: nowrap; }
        .invoice-section-title { margin: 12px 0 7px; color: #0f172a; font-size: 13px; font-weight: 900; }
        .invoice-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; margin-bottom: 12px; }
        .invoice-grid div { display: flex; align-items: center; justify-content: space-between; gap: 6px; min-width: 0; min-height: 42px; padding: 7px 8px; border: 1px solid #e2e8f0; border-radius: 9px; background: #f8fafc; }
        .invoice-grid strong { min-width: 0; font-size: 11px; line-height: 1.5; text-align: left; overflow-wrap: anywhere; }
        .invoice-table-wrap { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 10px; }
        table { width: 100%; min-width: 700px; border-collapse: collapse; }
        th, td { padding: 7px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; line-height: 1.45; text-align: right; }
        th { background: #eef2f7; color: #0f172a; }
        .invoice-summary { display: grid; grid-template-columns: minmax(220px, .8fr) minmax(0, 1.2fr); gap: 12px; align-items: stretch; margin-top: 16px; }
        .invoice-summary-note { display: flex; flex-direction: column; justify-content: center; gap: 7px; padding: 14px; border: 1px solid #dbe3ef; border-radius: 12px; background: #f8fafc; }
        .invoice-summary-note span { color: #64748b; font-size: 13px; line-height: 1.6; }
        .invoice-totals { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; }
        .invoice-totals div { display: flex; align-items: center; justify-content: space-between; gap: 5px; min-width: 0; min-height: 40px; padding: 6px 7px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
        .invoice-totals span { flex: 0 1 auto; font-size: 9px; white-space: nowrap; }
        .invoice-totals strong { flex: 0 0 auto; font-size: 11px; white-space: nowrap; }
        .invoice-totals .grand { background: #0f172a; color: #fff; }
        .invoice-totals .grand span { color: #cbd5e1; }
        .invoice-note { margin-top: 16px; color: #64748b; font-size: 12px; text-align: center; }
        @media (max-width: 820px) {
            body { padding: 10px; }
            .invoice-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .invoice-summary { grid-template-columns: 1fr; }
            .invoice-table-wrap { overflow: visible; border: 0; }
            .invoice-table-wrap table { display: block; min-width: 0; }
            .invoice-table-wrap thead { display: none; }
            .invoice-table-wrap tbody { display: grid; gap: 8px; }
            .invoice-table-wrap tr { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; padding: 7px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; }
            .invoice-table-wrap td { display: flex; align-items: center; justify-content: space-between; gap: 4px; min-width: 0; min-height: 36px; padding: 5px 6px; border: 1px solid #e5e7eb; border-radius: 7px; background: #f8fafc; white-space: normal; overflow-wrap: anywhere; }
            .invoice-table-wrap td:first-child, .invoice-table-wrap td[colspan] { grid-column: 1 / -1; }
            .invoice-table-wrap td::before { content: attr(data-label); color: #64748b; font-size: 8px; font-weight: 900; }
        }
        @media (max-width: 640px) {
            .page-toolbar { align-items: stretch; flex-direction: column; }
            .page-title { text-align: center; }
            .back-button { width: 100%; }
            .invoice-head { gap: 6px; padding: 8px; }
            .invoice-logo { width: 32px; height: 32px; }
            .invoice-head h2 { font-size: 15px; }
            .invoice-head p, .invoice-number span { font-size: 8px; }
            .invoice-number { gap: 4px; padding: 5px 6px; }
            .invoice-number strong { font-size: 13px; }
            .invoice-grid { gap: 5px; }
            .invoice-grid div { min-height: 38px; gap: 4px; padding: 5px 6px; }
            .invoice-grid span { font-size: 9px; }
            .invoice-grid strong { font-size: 10px; }
            .invoice-totals { gap: 4px; }
            .invoice-totals div { min-height: 37px; flex-direction: column; align-items: center; justify-content: center; gap: 1px; padding: 5px; }
            .invoice-totals span, .invoice-totals strong { display: block; width: 100%; line-height: 1.15; text-align: center; }
            .invoice-totals span { font-size: 7px; }
            .invoice-totals strong { font-size: 8.5px; }
        }
        @media print {
            body { padding: 0; background: #fff; }
            .page-toolbar, .invoice-toolbar { display: none; }
            .invoice-document { border: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <main class="invoice-page">
        <div class="page-toolbar">
            <h1 class="page-title">فاتورة الطلب #{{ $order->id }}</h1>
            <a class="action back-button" href="{{ route('orders.index') }}">رجوع لصفحة الطلبات</a>
        </div>

        @include('shared.invoice', ['order' => $order, 'invoiceId' => 'paperInvoice' . $order->id])
    </main>

    <script>
        function printInvoice() {
            window.print();
        }

        @if (request()->boolean('print'))
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 250);
            });
        @endif
    </script>
</body>
</html>
