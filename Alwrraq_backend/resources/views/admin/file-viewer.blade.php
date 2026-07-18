<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>عرض الملف - الورّاق</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Tahoma, Arial, sans-serif; background: #f3f6fb; color: #0f172a; }
        .page { min-height: 100vh; padding: clamp(10px, 2.5vw, 18px); }
        .viewer-shell { max-width: 1220px; margin: 0 auto; display: grid; grid-template-columns: minmax(250px, 320px) minmax(0, 1fr); gap: 12px; align-items: start; }
        .panel { background: #ffffff; border: 1px solid #dbe3ef; border-radius: 12px; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.07); overflow: hidden; }
        .info-panel { padding: 10px; position: sticky; top: 10px; }
        .brand { min-height: 50px; padding: 7px 9px; margin-bottom: 8px; border-radius: 9px; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .brand-identity { display: flex; align-items: center; gap: 7px; min-width: 0; }
        .brand h1 { margin: 0; color: #ffffff; font-size: 16px; line-height: 1.2; white-space: nowrap; }
        .brand-logo { width: 34px; height: 34px; flex: 0 0 auto; border-radius: 8px; object-fit: cover; display: block; background: #ffffff; border: 1px solid rgba(255,255,255,0.18); }
        .brand-page-title { color: #bfdbfe; font-size: 10px; font-weight: 900; white-space: nowrap; }
        .file-name { margin: 0 0 7px; padding: 7px 8px; border: 1px solid #dbeafe; border-radius: 8px; background: #eff6ff; color: #111827; font-size: 10px; font-weight: 900; line-height: 1.4; word-break: break-word; }
        .meta-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 4px; }
        .meta-item { min-width: 0; min-height: 38px; padding: 5px; border: 1px solid #e2e8f0; border-radius: 7px; background: #f8fafc; text-align: center; }
        .meta-item span { display: block; margin-bottom: 2px; color: #64748b; font-size: 7px; font-weight: 900; line-height: 1.2; word-break: normal; }
        .meta-item strong { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; color: #0f172a; font-size: 8px; font-weight: 900; line-height: 1.25; word-break: normal; overflow-wrap: normal; }
        .meta-item.wide { grid-column: span 3; min-height: 34px; display: flex; align-items: center; justify-content: space-between; gap: 6px; text-align: right; }
        .meta-item.wide span { flex: 0 0 auto; margin: 0; }
        .meta-item.wide strong { flex: 1 1 auto; text-align: left; }
        .notice { margin-top: 7px; padding: 6px 7px; border-radius: 7px; border: 1px solid #bae6fd; background: #f0f9ff; color: #0c4a6e; font-size: 7.5px; font-weight: 900; line-height: 1.45; }
        .actions { display: flex; align-items: center; gap: 4px; margin-top: 7px; flex-wrap: wrap; }
        .action { width: auto; min-height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 5px 7px; border: 0; border-radius: 7px; color: #ffffff; font-family: inherit; font-size: 8px; font-weight: 900; text-decoration: none; cursor: pointer; }
        .action.blue { background: #2563eb; }
        .action.blue:hover { background: #1d4ed8; }
        .action.yellow { background: #facc15; color: #422006; }
        .action.yellow:hover { background: #eab308; }
        .action.green { background: #16a34a; }
        .action.green:hover { background: #15803d; }
        .action.dark { background: #0f172a; }
        .action.dark:hover { background: #1e293b; }
        .preview-panel { min-height: calc(100vh - 20px); display: flex; flex-direction: column; }
        .preview-head { padding: 9px 11px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; gap: 8px; align-items: center; }
        .preview-head h2 { margin: 0; font-size: 13px; color: #111827; }
        .preview-head span { color: #64748b; font-size: 9px; font-weight: 900; }
        .preview-frame { width: 100%; flex: 1; min-height: 760px; border: 0; background: #ffffff; }
        .unsupported { min-height: 520px; padding: 20px; display: grid; place-items: center; text-align: center; }
        .unsupported-box { max-width: 520px; padding: 16px; border: 1px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; }
        .unsupported-box h2 { margin: 0 0 6px; font-size: 16px; }
        .unsupported-box p { margin: 0; color: #475569; font-size: 11px; font-weight: 800; line-height: 1.7; }
        @media (max-width: 900px) {
            .viewer-shell { grid-template-columns: 1fr; }
            .info-panel { position: static; }
            .preview-frame { min-height: 620px; }
            .brand { min-height: 44px; }
            .meta-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .page { padding: 6px; }
            .viewer-shell { gap: 6px; }
            .info-panel { padding: 6px; border-radius: 9px; }
            .brand { min-height: 40px; margin-bottom: 5px; padding: 5px 7px; border-radius: 7px; }
            .brand-logo { width: 29px; height: 29px; border-radius: 7px; }
            .brand h1 { font-size: 14px; }
            .brand-page-title { font-size: 8px; }
            .file-name { margin-bottom: 4px; padding: 5px 6px; border-radius: 6px; font-size: 8px; }
            .meta-grid { gap: 3px; }
            .meta-item { min-height: 34px; padding: 4px 3px; border-radius: 6px; }
            .meta-item span { font-size: 6.3px; }
            .meta-item strong { font-size: 7.3px; }
            .meta-item.wide { min-height: 31px; padding: 4px 5px; }
            .notice { margin-top: 4px; padding: 5px; font-size: 6.8px; }
            .actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(70px, 1fr)); gap: 3px; margin-top: 5px; }
            .action { width: 100%; min-width: 0; min-height: 25px; padding: 4px 2px; border-radius: 6px; font-size: 7px; line-height: 1.15; text-align: center; }
            .preview-panel { min-height: calc(100dvh - 12px); border-radius: 9px; }
            .preview-head { padding: 7px 8px; }
            .preview-head h2 { font-size: 11px; }
            .preview-head span { font-size: 8px; }
            .preview-frame { min-height: 560px; }
        }
        @media print {
            body { background: #ffffff; }
            .info-panel, .preview-head { display: none; }
            .page { padding: 0; }
            .viewer-shell { display: block; max-width: none; }
            .preview-panel { border: 0; box-shadow: none; min-height: 100vh; }
            .preview-frame { min-height: 100vh; }
        }
    </style>
</head>
<body>
    @php($displayFileType = strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: $file->file_type))
    <div class="page">
        <div class="viewer-shell">
            <aside class="panel info-panel">
                <div class="brand">
                    <div class="brand-identity">
                        <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                        <h1>الورّاق</h1>
                    </div>
                    <span class="brand-page-title">عرض الملف</span>
                </div>

                <p class="file-name">{{ $file->original_name }}</p>

                <div class="meta-grid">
                    @php($isAcademicWord = in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word')
                    <div class="meta-item">
                        <span>رقم الطلب</span>
                        <strong>#{{ $order->id }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>نوع الملف</span>
                        <strong>{{ $displayFileType }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>عدد الصفحات</span>
                        <strong>{{ $file->pages }}</strong>
                    </div>
                    <div class="meta-item wide">
                        <span>العميل</span>
                        <strong>{{ $order->user->name ?? '-' }} - {{ $order->user->phone ?? '-' }}</strong>
                    </div>
                    <div class="meta-item wide">
                        <span>الخدمة</span>
                        <strong>{{ $serviceNames[$order->service_type] ?? $order->service_type }}</strong>
                    </div>
                    @if ($isAcademicWord)
                    <div class="meta-item wide">
                        <span>الاستخدام</span>
                        <strong>ملف Word للعرض فقط، وغير محتسب ضمن الطباعة أو التجليد أو التسعير.</strong>
                    </div>
                    @else
                    <div class="meta-item">
                        <span>عدد النسخ</span>
                        <strong>{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : $file->copies }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>نوع الطباعة</span>
                        <strong>{{ in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'word' ? 'للعرض فقط' : ($printSideNames[$file->print_sides] ?? 'وجهين') }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>حجم الصفحة</span>
                        <strong>{{ $pageSizeNames[$file->page_size] ?? 'A4' }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>نمط الطباعة</span>
                        <strong>{{ $printColor }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>التغليف / التجليد</span>
                        <strong>{{ $bindingNames[$file->binding_type] ?? '-' }}</strong>
                    </div>
                    @endif
                </div>

                <div class="notice">
                    الطباعة المباشرة تفتح نافذة الطابعة من المتصفح. تأكد من ضبط الطابعة على نفس البيانات أعلاه، ولا تعتمد على لون الورق من أمر الطباعة.
                </div>

                <div class="actions">
                    @if ($isPrintablePreview)
                        <button class="action yellow" type="button" onclick="printPreview('{{ route('admin.files.view', ['file' => $file, 'raw' => 1]) }}')">طباعة الملف مباشرة</button>
                    @endif
                    <a class="action blue" href="{{ route('admin.files.download', $file) }}" data-complete-order-download>تحميل الملف</a>
                    <a class="action green" href="{{ route('admin.orders', ['open_order' => $order->id]) }}">العودة لعرض الطلب</a>
                </div>
            </aside>

            <main class="panel preview-panel">
                <div class="preview-head">
                    <h2>عرض الملف</h2>
                    <span>{{ $displayFileType }}</span>
                </div>

                @if ($isPrintablePreview)
                    <iframe id="filePreview" class="preview-frame" src="{{ route('admin.files.view', ['file' => $file, 'raw' => 1]) }}"></iframe>
                @else
                    <div class="unsupported">
                        <div class="unsupported-box">
                            <h2>المعاينة المباشرة غير مدعومة لهذا النوع</h2>
                            <p>ملفات Word غالبًا لا يعرضها المتصفح مباشرة. استخدم زر تحميل الملف إذا احتجت فتحه في Word.</p>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <script>
        function printPreview(rawUrl) {
            const printFrame = document.createElement('iframe');
            printFrame.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0;pointer-events:none;border:0;';
            document.body.appendChild(printFrame);

            const triggerPrint = () => {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                    setTimeout(() => printFrame.remove(), 1000);
                } catch (error) {
                    printFrame.remove();
                }
            };

            printFrame.addEventListener('load', () => setTimeout(triggerPrint, 300), { once: true });
            printFrame.src = rawUrl;
        }
    </script>
    @include('shared.admin-live-updates', ['adminLiveRefreshMain' => false])
    @include('shared.language-tools')
</body>
</html>
