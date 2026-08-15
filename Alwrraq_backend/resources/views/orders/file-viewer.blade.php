<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('shared.tab-brand')
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { width: 100%; max-width: 100%; min-height: 100vh; padding: clamp(12px, 3vw, 24px); }
        .viewer { width: 100%; max-width: 1180px; min-width: 0; margin: 0 auto; display: grid; grid-template-columns: minmax(220px, 300px) minmax(0, 1fr); gap: 14px; align-items: start; }
        .panel { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); overflow: hidden; }
        .side { padding: 12px; }
        .brand { min-height: 54px; padding: 9px 10px; margin-bottom: 10px; border-radius: 10px; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .brand-identity { display: inline-flex; align-items: center; gap: 8px; min-width: 0; }
        .brand h1 { margin: 0; font-size: 18px; line-height: 1.2; white-space: nowrap; }
        .brand-logo { width: 36px; height: 36px; flex: 0 0 auto; border-radius: 10px; object-fit: cover; display: block; background: #ffffff; border: 1px solid rgba(255,255,255,0.18); }
        .brand-page-title { color: #bfdbfe; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .file-name { margin: 0 0 9px; padding: 8px 9px; border: 1px solid #dbeafe; border-radius: 9px; background: #eff6ff; color: #0f172a; font-size: 12px; font-weight: 900; line-height: 1.55; word-break: break-word; }
        .meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; }
        .meta div { min-width: 0; padding: 7px 6px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; text-align: center; }
        .meta span { display: block; margin-bottom: 2px; color: #64748b; font-size: 9.5px; font-weight: 900; line-height: 1.35; }
        .meta strong { display: block; color: #0f172a; font-size: 11px; font-weight: 900; line-height: 1.35; overflow-wrap: anywhere; }
        .actions { display: flex; align-items: center; justify-content: flex-start; gap: 7px; margin-top: 8px; }
        .action { width: auto; min-height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 7px 11px; border-radius: 8px; color: #ffffff; text-decoration: none; font-size: 11px; font-weight: 900; }
        .action.dark { background: #0f172a; }
        .action.blue { background: #2563eb; }
        .action.green { background: #16a34a; }
        .action.green:hover { background: #15803d; }
        .preview { min-height: calc(100vh - 48px); display: flex; flex-direction: column; }
        .preview-head { padding: 13px 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; gap: 12px; align-items: center; }
        .preview-head h2 { margin: 0; font-size: 17px; }
        .pdf-preview { width: 100%; flex: 1; min-height: 720px; padding: 10px; background: #cbd5e1; overflow: auto; }
        .pdf-page { display: block; max-width: 100%; height: auto; margin: 0 auto 10px; background: #fff; box-shadow: 0 5px 18px rgba(15, 23, 42, .18); }
        .pdf-status { padding: 30px 12px; color: #475569; font-weight: 900; text-align: center; }
        .image-preview { flex: 1; min-height: 720px; padding: 18px; display: grid; place-items: center; background: #e2e8f0; }
        .image-preview img { display: block; max-width: 100%; max-height: calc(100vh - 100px); object-fit: contain; background: #ffffff; box-shadow: 0 8px 24px rgba(15, 23, 42, .18); }
        .word-preview { flex: 1; min-height: 720px; padding: clamp(22px, 5vw, 54px); overflow: auto; background: #ffffff; color: #111827; font-family: Arial, Tahoma, sans-serif; font-size: 16px; line-height: 1.9; }
        .word-preview p { margin: 0 0 12px; white-space: pre-wrap; }
        .word-table-wrap { width: 100%; margin: 16px 0; overflow-x: auto; }
        .word-table { width: 100%; border-collapse: collapse; }
        .word-table td { min-width: 100px; padding: 9px 10px; border: 1px solid #cbd5e1; vertical-align: top; }
        .word-table td p { margin-bottom: 5px; }
        .unsupported { min-height: 460px; padding: 28px; display: grid; place-items: center; text-align: center; }
        .unsupported-box { max-width: 520px; padding: 24px; border: 1px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; }
        .unsupported-box h2 { margin: 0 0 8px; }
        .unsupported-box p { margin: 0; color: #64748b; font-weight: 800; line-height: 1.8; }
        @media (max-width: 860px) {
            .page { padding: 8px; }
            .viewer { grid-template-columns: minmax(0, 1fr); }
            .viewer, .panel, .preview, .pdf-preview, .image-preview, .word-preview { width: 100%; max-width: 100%; min-width: 0; }
            .pdf-preview { min-height: 560px; padding: 6px; overflow-x: hidden; }
            .pdf-page { width: 100% !important; max-width: 100% !important; height: auto !important; }
            .image-preview { min-height: 560px; padding: 10px; overflow: hidden; }
            .image-preview img { width: auto; max-width: 100%; height: auto; }
            .word-preview { min-height: 560px; padding: 16px 12px; overflow-x: hidden; overflow-wrap: anywhere; word-break: break-word; }
            .word-preview p { max-width: 100%; overflow-wrap: anywhere; word-break: break-word; }
            .word-table-wrap { max-width: 100%; overflow-x: hidden; }
            .word-table { width: 100%; max-width: 100%; table-layout: fixed; }
            .word-table td { min-width: 0; overflow-wrap: anywhere; word-break: break-word; }
        }
        @media print {
            @page { margin: 8mm; }
            body { background: #ffffff; }
            .side, .preview-head { display: none; }
            .page { padding: 0; }
            .viewer { display: block; max-width: none; }
            .preview { min-height: 0; border: 0; box-shadow: none; }
            .image-preview { min-height: 0; padding: 0; background: #ffffff; }
            .image-preview img { width: 100%; max-width: 100%; max-height: 270mm; box-shadow: none; object-fit: contain; }
        }
    </style>
</head>
<body>
    @php
        $displayFileType = strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: $file->file_type);
    @endphp
    <div class="page">
        <div class="viewer">
            <aside class="panel side">
                <div class="brand">
                    <div class="brand-identity">
                        <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                        <h1>الورّاق</h1>
                    </div>
                    <span class="brand-page-title">{{ $isImage ? 'عرض الصورة' : 'عرض الملف' }}</span>
                </div>

                <p class="file-name">{{ $file->original_name }}</p>

                <div class="meta">
                    <div><span>رقم الطلب</span><strong>#{{ $order->id }}</strong></div>
                    <div><span>نوع الملف</span><strong>{{ $displayFileType }}</strong></div>
                    <div>
                        <span>{{ $isImage ? 'الحجم' : 'عدد الصفحات' }}</span>
                        <strong>{{ $isImage ? number_format($file->size / 1024, 1).' KB' : $file->pages }}</strong>
                    </div>
                </div>

                <div class="actions">
                    @if ($isImage)
                        <button class="action dark" type="button" onclick="window.print()">طباعة الصورة</button>
                        <a class="action blue" href="{{ route('orders.file.view', ['order' => $order, 'file' => $file, 'download' => 1], false) }}">تحميل الصورة</a>
                    @else
                        <a class="action blue" href="{{ route('orders.file.view', ['order' => $order, 'file' => $file, 'download' => 1], false) }}">تحميل الملف</a>
                    @endif
                    @if (request('from') === 'upload')
                        <a class="action green" href="{{ route('home', ['service' => $order->service_type, 'order' => $order->id]) }}">العودة للملفات المحملة</a>
                    @elseif (request('from') === 'cart')
                        <a class="action green" href="{{ route('cart.index') }}">العودة للسلة</a>
                    @else
                        <a class="action green" href="{{ route('orders.index', ['open_order' => $order->id]) }}">العودة لعرض الطلب</a>
                    @endif
                </div>
            </aside>

            <main class="panel preview">
                <div class="preview-head">
                    <h2>{{ $isImage ? 'عرض الصورة' : 'معاينة الملف' }}</h2>
                    <span>{{ $displayFileType }}</span>
                </div>

                @if ($isPdf)
                    <div class="pdf-preview" id="uploadedPdfPreview">
                        <div class="pdf-status" id="uploadedPdfStatus">جاري تحميل ملف PDF...</div>
                    </div>
                @elseif ($isImage)
                    <div class="image-preview">
                        <img id="uploadedImagePreview" src="{{ route('orders.file.view', ['order' => $order, 'file' => $file, 'raw' => 1], false) }}" alt="{{ $file->original_name }}">
                    </div>
                @elseif ($wordPreviewHtml)
                    <article class="word-preview" dir="auto">{!! $wordPreviewHtml !!}</article>
                @else
                    <div class="unsupported">
                        <div class="unsupported-box">
                            <h2>المعاينة غير مدعومة لهذا النوع</h2>
                            <p>تعذر قراءة محتوى ملف Word. تأكد أن الملف بصيغة DOCX سليمة.</p>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
    @if ($isPdf)
        @include('shared.pdf-preview', [
            'pdfPreviewId' => 'uploadedPdfPreview',
            'pdfStatusId' => 'uploadedPdfStatus',
            'pdfUrl' => route('orders.file.view', ['order' => $order, 'file' => $file, 'raw' => 1], false),
        ])
    @endif
    @if ($isImage && request()->boolean('print'))
        <script>
            const uploadedImagePreview = document.getElementById('uploadedImagePreview');
            const printUploadedImage = () => setTimeout(() => window.print(), 150);
            if (uploadedImagePreview.complete) {
                printUploadedImage();
            } else {
                uploadedImagePreview.addEventListener('load', printUploadedImage, { once: true });
            }
        </script>
    @endif
    @include('shared.language-tools')
</body>
</html>
