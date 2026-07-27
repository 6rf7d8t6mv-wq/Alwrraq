<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('shared.tab-brand')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; padding: clamp(10px, 3vw, 24px); }
        .viewer { width: min(1180px, 100%); margin: 0 auto; display: grid; grid-template-columns: minmax(220px, 300px) minmax(0, 1fr); gap: 14px; align-items: start; }
        .panel { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 18px 45px rgba(15, 23, 42, .08); }
        .side { padding: 12px; }
        .brand { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 54px; margin-bottom: 10px; padding: 9px 10px; border-radius: 10px; background: #0f172a; color: #fff; }
        .brand-identity { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .brand-logo { width: 36px; height: 36px; flex: 0 0 auto; border-radius: 10px; object-fit: cover; background: #fff; }
        .brand h1 { margin: 0; font-size: 18px; }
        .brand span { color: #bfdbfe; font-size: 11px; font-weight: 900; }
        .file-name { margin: 0 0 9px; padding: 9px; border: 1px solid #dbeafe; border-radius: 9px; background: #eff6ff; font-size: 12px; font-weight: 900; line-height: 1.55; overflow-wrap: anywhere; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
        .meta div { min-width: 0; padding: 8px 6px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; text-align: center; }
        .meta span { display: block; margin-bottom: 2px; color: #64748b; font-size: 9px; font-weight: 900; }
        .meta strong { display: block; font-size: 11px; overflow-wrap: anywhere; }
        .actions { display: grid; grid-template-columns: 1fr; gap: 7px; margin-top: 9px; }
        .action { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 8px 10px; border-radius: 8px; color: #fff; text-decoration: none; font-size: 12px; font-weight: 900; text-align: center; }
        .action.green { background: #16a34a; }
        .action.blue { background: #2563eb; }
        .preview { min-height: calc(100vh - 48px); display: flex; flex-direction: column; }
        .preview-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 16px; border-bottom: 1px solid #e2e8f0; }
        .preview-head h2 { margin: 0; font-size: 17px; }
        .pdf-preview { width: 100%; flex: 1; min-height: 720px; padding: 10px; background: #cbd5e1; overflow: auto; }
        .pdf-page { display: block; max-width: 100%; height: auto; margin: 0 auto 10px; background: #fff; box-shadow: 0 5px 18px rgba(15, 23, 42, .18); }
        .pdf-status { padding: 30px 12px; color: #475569; font-weight: 900; text-align: center; }
        .word-preview { flex: 1; min-height: 720px; padding: clamp(22px, 5vw, 54px); overflow: auto; background: #fff; font-size: 16px; line-height: 1.9; }
        .word-preview p { margin: 0 0 12px; white-space: pre-wrap; }
        .word-table-wrap { width: 100%; margin: 16px 0; overflow-x: auto; }
        .word-table { width: 100%; border-collapse: collapse; }
        .word-table td { min-width: 100px; padding: 9px 10px; border: 1px solid #cbd5e1; vertical-align: top; }
        .unsupported { min-height: 460px; padding: 28px; display: grid; place-items: center; text-align: center; }
        .unsupported div { max-width: 520px; padding: 24px; border: 1px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; }
        .unsupported h2 { margin: 0 0 8px; }
        .unsupported p { margin: 0; color: #64748b; font-weight: 800; line-height: 1.8; }
        @media (max-width: 860px) {
            .viewer { grid-template-columns: 1fr; }
            .preview { min-height: 560px; }
            .pdf-preview, .word-preview { min-height: 560px; }
        }
    </style>
</head>
<body>
    @php($displayFileType = strtoupper(pathinfo($deliveredFile->original_name, PATHINFO_EXTENSION) ?: 'FILE'))
    <div class="page">
        <div class="viewer">
            <aside class="panel side">
                <div class="brand">
                    <div class="brand-identity">
                        <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                        <h1>الورّاق</h1>
                    </div>
                    <span>الملف المستلم</span>
                </div>

                <p class="file-name">{{ $deliveredFile->original_name }}</p>
                <div class="meta">
                    <div><span>رقم الطلب</span><strong>#{{ $order->id }}</strong></div>
                    <div><span>نوع الملف</span><strong>{{ $displayFileType }}</strong></div>
                </div>

                <div class="actions">
                    <a class="action green" href="{{ $backUrl }}">العودة إلى الطلب في صفحة الطلبات</a>
                    <a class="action blue" href="{{ $downloadUrl }}">تحميل الملف المستلم</a>
                </div>
            </aside>

            <main class="panel preview">
                <div class="preview-head">
                    <h2>معاينة الملف</h2>
                    <span>{{ $displayFileType }}</span>
                </div>

                @if ($isPdf)
                    <div
                        class="pdf-preview"
                        id="pdfPreview"
                        data-pdf-url="{{ $rawUrl }}"
                    >
                        <div class="pdf-status" id="pdfStatus">جاري تحميل الملف المستلم...</div>
                    </div>
                @elseif ($wordPreviewHtml)
                    <article class="word-preview" dir="auto">{!! $wordPreviewHtml !!}</article>
                @else
                    <div class="unsupported">
                        <div>
                            <h2>المعاينة غير مدعومة لهذا النوع</h2>
                            <p>يمكنك تحميل الملف وفتحه في التطبيق المناسب، ثم الرجوع إلى نفس الطلب من الزر الموجود هنا.</p>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
    @if ($isPdf)
        @include('shared.pdf-preview', [
            'pdfPreviewId' => 'pdfPreview',
            'pdfStatusId' => 'pdfStatus',
            'pdfUrl' => $rawUrl,
            'pdfErrorMessage' => 'تعذر عرض ملف PDF. حاول مرة أخرى أو استخدم زر تحميل الملف المستلم.',
        ])
    @endif
    @include('shared.language-tools')
</body>
</html>
