<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('shared.tab-brand')
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        body { margin: 0; font-family: Tahoma, Arial, sans-serif; background: #f3f6fb; color: #0f172a; }
        .page { width: 100%; max-width: 100%; min-height: 100vh; padding: clamp(10px, 2.5vw, 18px); }
        .viewer-shell { width: 100%; max-width: 1220px; min-width: 0; margin: 0 auto; display: grid; grid-template-columns: minmax(250px, 320px) minmax(0, 1fr); gap: 12px; align-items: start; }
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
        .pdf-preview { width: 100%; flex: 1; min-height: 760px; padding: 10px; background: #cbd5e1; overflow: auto; }
        .pdf-page { display: block; max-width: 100%; height: auto; margin: 0 auto 10px; background: #fff; box-shadow: 0 5px 18px rgba(15, 23, 42, .18); }
        .pdf-status { padding: 30px 12px; color: #475569; font-weight: 900; text-align: center; }
        .image-preview { flex: 1; min-height: 760px; padding: 18px; display: grid; place-items: center; background: #e2e8f0; }
        .image-preview img { display: block; max-width: 100%; max-height: calc(100vh - 80px); object-fit: contain; background: #ffffff; box-shadow: 0 8px 24px rgba(15, 23, 42, .18); }
        .word-preview { flex: 1; min-height: 760px; padding: clamp(22px, 4vw, 48px); overflow: auto; background: #ffffff; color: #111827; font-family: Arial, Tahoma, sans-serif; font-size: 15px; line-height: 1.9; }
        .word-preview p { margin: 0 0 11px; white-space: pre-wrap; }
        .word-table-wrap { width: 100%; margin: 14px 0; overflow-x: auto; }
        .word-table { width: 100%; border-collapse: collapse; }
        .word-table td { min-width: 90px; padding: 8px 9px; border: 1px solid #cbd5e1; vertical-align: top; }
        .word-table td p { margin-bottom: 4px; }
        .unsupported { min-height: 520px; padding: 20px; display: grid; place-items: center; text-align: center; }
        .unsupported-box { max-width: 520px; padding: 16px; border: 1px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; }
        .unsupported-box h2 { margin: 0 0 6px; font-size: 16px; }
        .unsupported-box p { margin: 0; color: #475569; font-size: 11px; font-weight: 800; line-height: 1.7; }
        .invoice-print-source { display: none; }
        @media (max-width: 900px) {
            .viewer-shell { grid-template-columns: 1fr; }
            .info-panel { position: static; }
            .pdf-preview { min-height: 620px; }
            .image-preview { min-height: 620px; padding: 10px; }
            .word-preview { min-height: 620px; }
            .brand { min-height: 44px; }
            .meta-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .page { padding: 6px; }
            .viewer-shell { grid-template-columns: minmax(0, 1fr); gap: 6px; }
            .viewer-shell, .panel, .preview-panel, .pdf-preview, .image-preview, .word-preview { width: 100%; max-width: 100%; min-width: 0; }
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
            .pdf-preview { min-height: 560px; padding: 6px; overflow-x: hidden; }
            .pdf-page { width: 100% !important; max-width: 100% !important; height: auto !important; }
            .image-preview { min-height: 560px; overflow: hidden; }
            .image-preview img { width: auto; max-width: 100%; height: auto; }
            .word-preview { min-height: 560px; padding: 16px 12px; overflow-x: hidden; overflow-wrap: anywhere; word-break: break-word; font-size: 13px; }
            .word-preview p { max-width: 100%; overflow-wrap: anywhere; word-break: break-word; }
            .word-table-wrap { max-width: 100%; overflow-x: hidden; }
            .word-table { width: 100%; max-width: 100%; table-layout: fixed; }
            .word-table td { min-width: 0; overflow-wrap: anywhere; word-break: break-word; }
        }
        @media (min-width: 1100px) {
            .viewer-shell { grid-template-columns: minmax(300px, 350px) minmax(0, 1fr); }
            .brand h1 { font-size: 20px; }
            .brand-logo { width: 40px; height: 40px; }
            .brand-page-title { font-size: 13px; }
            .file-name { font-size: 13px; line-height: 1.5; }
            .meta-item { min-height: 45px; }
            .meta-item span { font-size: 9.5px; }
            .meta-item strong { font-size: 11px; line-height: 1.4; }
            .notice { font-size: 10.5px; }
            .action { min-height: 32px; font-size: 10.5px; }
            .preview-head h2 { font-size: 16px; }
            .preview-head span { font-size: 11px; }
            .unsupported-box p { font-size: 13px; }
        }
        @media print {
            body { background: #ffffff; }
            .info-panel, .preview-head { display: none; }
            .page { padding: 0; }
            .viewer-shell { display: block; max-width: none; }
            .preview-panel { border: 0; box-shadow: none; min-height: 100vh; }
            .pdf-preview { min-height: 100vh; }
            .image-preview { min-height: 0; padding: 0; background: #ffffff; }
            .image-preview img { width: 100%; max-width: 100%; max-height: 270mm; box-shadow: none; object-fit: contain; }
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
                    <span class="brand-page-title">{{ $isImage ? 'عرض الصورة' : 'عرض الملف' }}</span>
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
                        <span>{{ $isImage ? 'الحجم' : 'عدد الصفحات' }}</span>
                        <strong>{{ $isImage ? number_format($file->size / 1024, 1).' KB' : $file->pages }}</strong>
                    </div>
                    <div class="meta-item wide">
                        <span>العميل</span>
                        <strong><span data-transliterate-name>{{ $order->user->name ?? '-' }}</span> - {{ $order->user->phone ?? '-' }}</strong>
                    </div>
                    <div class="meta-item wide">
                        <span>الخدمة</span>
                        <strong>{{ $order->serviceDefinition?->title ?? $serviceNames[$order->service_type] ?? $order->service_type }}</strong>
                    </div>
                    @if ($isImage)
                    <div class="meta-item wide">
                        <span>مسار الصورة</span>
                        <strong>{{ $file->relative_path ?: $file->original_name }}</strong>
                    </div>
                    @elseif ($isAcademicWord)
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
                    @if ($order->service_type === 'books')
                    <div class="meta-item">
                        <span>لون الجلد</span>
                        <strong>{{ ['black' => 'جلد أسود', 'green' => 'جلد أخضر', 'red' => 'جلد أحمر', 'blue' => 'جلد أزرق', 'beige' => 'جلد بيج', 'brown' => 'جلد بني'][$file->cover_color] ?? '-' }}</strong>
                    </div>
                    @endif
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

                @if ($isImage)
                    <div class="notice">يمكن عرض الصورة أو طباعتها مباشرة من المتصفح دون حفظها على الجهاز.</div>
                @else
                    <div class="notice">
                        الطباعة المباشرة تفتح طباعة الفاتورة أولًا، وبعد إنهائها تفتح طباعة الملف. تأكد من ضبط الطابعة على نفس البيانات أعلاه، ولا تعتمد على لون الورق من أمر الطباعة.
                    </div>
                @endif

                <div class="actions">
                    @if ($isImage)
                        <button class="action yellow" type="button" onclick="window.print()">طباعة الصورة</button>
                    @elseif ($isPrintablePreview)
                        <button class="action yellow" type="button" onclick="printInvoiceThenPreview('adminFileInvoice{{ $order->id }}', '{{ route('admin.files.view', ['file' => $file, 'raw' => 1]) }}')">طباعة الملف مباشرة</button>
                    @endif
                    <a class="action blue" href="{{ route('admin.files.download', $file) }}" data-direct-file-download>{{ $isImage ? 'تحميل الصورة' : 'تحميل الملف' }}</a>
                    <a class="action green" href="{{ route($order->status === 'cancelled' ? 'admin.orders.cancelled' : ($order->payment_status === 'paid' ? 'admin.orders' : 'admin.orders.unpaid'), ['open_order' => $order->id]) }}">العودة لعرض الطلب</a>
                </div>
            </aside>

            <main class="panel preview-panel">
                <div class="preview-head">
                    <h2>{{ $isImage ? 'عرض الصورة' : 'عرض الملف' }}</h2>
                    <span>{{ $displayFileType }}</span>
                </div>

                @if ($isPdf)
                    <div class="pdf-preview" id="adminPdfPreview">
                        <div class="pdf-status" id="adminPdfStatus">جاري تحميل ملف PDF...</div>
                    </div>
                @elseif ($isImage)
                    <div class="image-preview">
                        <img id="adminImagePreview" src="{{ route('admin.files.view', ['file' => $file, 'raw' => 1]) }}" alt="{{ $file->original_name }}">
                    </div>
                @elseif ($wordPreviewHtml)
                    <article class="word-preview" dir="auto">{!! $wordPreviewHtml !!}</article>
                @else
                    <div class="unsupported">
                        <div class="unsupported-box">
                            <h2>تعذر عرض ملف Word</h2>
                            <p>تعذر قراءة محتوى الملف. تأكد أن ملف Word بصيغة DOCX سليمة، أو استخدم زر تحميل الملف.</p>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    @if ($isPrintablePreview)
        <div class="invoice-print-source" aria-hidden="true">
            @include('shared.invoice', ['order' => $order, 'invoiceId' => 'adminFileInvoice'.$order->id])
        </div>
    @endif

    @if ($isPdf)
        @include('shared.pdf-preview', [
            'pdfPreviewId' => 'adminPdfPreview',
            'pdfStatusId' => 'adminPdfStatus',
            'pdfUrl' => route('admin.files.view', ['file' => $file, 'raw' => 1], false),
        ])
    @endif

    @if ($isImage && request()->boolean('print'))
        <script>
            const adminImagePreview = document.getElementById('adminImagePreview');
            const printAdminImage = () => setTimeout(() => window.print(), 150);
            if (adminImagePreview.complete) {
                printAdminImage();
            } else {
                adminImagePreview.addEventListener('load', printAdminImage, { once: true });
            }
        </script>
    @endif

    <script>
        function printInvoiceThenPreview(invoiceId, rawUrl) {
            const previewJob = preparePreview(rawUrl);
            const invoice = document.getElementById(invoiceId);
            if (!invoice) {
                printPreparedPreview(previewJob);
                return;
            }

            const invoiceFrame = document.createElement('iframe');
            invoiceFrame.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0;pointer-events:none;border:0;';
            document.body.appendChild(invoiceFrame);

            const invoiceDocument = invoiceFrame.contentWindow.document;
            invoiceDocument.write(`
                <!DOCTYPE html>
                <html lang="ar" dir="rtl">
                <head>
                    <meta charset="utf-8">
                    <title>فاتورة ضريبية مبسطة</title>
                    <style>
                        @page { size: A4; margin: 12mm; }
                        * { box-sizing: border-box; }
                        body { margin: 0; color: #111827; direction: rtl; font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 7px; border: 1px solid #e5e7eb; text-align: right; font-size: 10px; }
                        th { background: #f8fafc; }
                        .invoice-document { border: 0; padding: 0; }
                        .invoice-head { display: flex; justify-content: space-between; gap: 14px; padding-bottom: 12px; border-bottom: 3px solid #0f172a; margin-bottom: 12px; }
                        .invoice-brand { display: flex; align-items: center; gap: 9px; }
                        .invoice-logo { width: 40px; height: 40px; border-radius: 9px; overflow: hidden; }
                        .invoice-logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
                        .invoice-head h2 { margin: 0; font-size: 23px; }
                        .invoice-head p { margin: 3px 0 0; }
                        .invoice-number { text-align: left; }
                        .invoice-number span, .invoice-grid span, .invoice-totals span { display: block; margin-bottom: 3px; color: #64748b; font-size: 9px; font-weight: 700; }
                        .invoice-number small { display: inline-block; margin-top: 4px; }
                        .invoice-grid, .invoice-totals, .invoice-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin: 10px 0; }
                        .invoice-grid div, .invoice-totals div, .invoice-summary-note { padding: 7px; border: 1px solid #e5e7eb; border-radius: 7px; }
                        .invoice-grid .full { grid-column: 1 / -1; }
                        .invoice-totals .grand { background: #0f172a; color: #ffffff; }
                        .invoice-section-title { margin: 10px 0 6px; font-size: 12px; font-weight: 900; }
                        .invoice-note { margin-top: 12px; color: #64748b; font-size: 9px; text-align: center; }
                        .invoice-toolbar { display: none; }
                    </style>
                </head>
                <body>${invoice.outerHTML}</body>
                </html>
            `);
            invoiceDocument.close();

            let continued = false;
            const continueWithFile = () => {
                if (continued) return;
                continued = true;
                setTimeout(() => {
                    invoiceFrame.remove();
                    printPreparedPreview(previewJob);
                }, 350);
            };

            invoiceFrame.contentWindow.addEventListener('afterprint', continueWithFile, { once: true });
            setTimeout(() => {
                try {
                    invoiceFrame.contentWindow.focus();
                    invoiceFrame.contentWindow.print();
                    setTimeout(continueWithFile, 500);
                } catch (error) {
                    continueWithFile();
                }
            }, 450);
        }

        function preparePreview(rawUrl) {
            const printFrame = document.createElement('iframe');
            printFrame.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0;pointer-events:none;border:0;';
            document.body.appendChild(printFrame);

            const job = {
                frame: printFrame,
                ready: false,
                requested: false,
                printed: false,
            };

            printFrame.addEventListener('load', () => {
                job.ready = true;
                if (job.requested) {
                    printPreparedPreview(job);
                }
            }, { once: true });
            printFrame.src = rawUrl;

            return job;
        }

        function printPreparedPreview(job) {
            job.requested = true;
            if (!job.ready || job.printed) return;
            job.printed = true;

            setTimeout(() => {
                try {
                    job.frame.contentWindow.focus();
                    job.frame.contentWindow.print();
                    setTimeout(() => job.frame.remove(), 1500);
                } catch (error) {
                    job.frame.remove();
                }
            }, 300);
        }

        function printPreview(rawUrl) {
            printPreparedPreview(preparePreview(rawUrl));
        }
    </script>
    @include('shared.language-tools')
</body>
</html>
