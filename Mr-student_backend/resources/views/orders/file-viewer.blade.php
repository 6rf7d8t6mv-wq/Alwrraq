<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>عرض الملف</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; padding: clamp(12px, 3vw, 24px); }
        .viewer { max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: minmax(220px, 300px) minmax(0, 1fr); gap: 14px; align-items: start; }
        .panel { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); overflow: hidden; }
        .side { padding: 16px; }
        .brand { padding: 16px; margin-bottom: 14px; border-radius: 12px; background: #0f172a; color: #ffffff; }
        .brand h1 { margin: 0; font-size: 22px; }
        .brand p { margin: 6px 0 0; color: #cbd5e1; font-weight: 700; line-height: 1.7; }
        .file-name { margin: 0 0 14px; color: #0f172a; font-size: 16px; font-weight: 900; line-height: 1.8; word-break: break-word; }
        .meta { display: grid; gap: 8px; }
        .meta div { padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
        .meta span { display: block; margin-bottom: 4px; color: #64748b; font-size: 12px; font-weight: 900; }
        .meta strong { color: #0f172a; font-size: 14px; font-weight: 900; }
        .actions { display: grid; gap: 9px; margin-top: 14px; }
        .action { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; padding: 11px 14px; border-radius: 10px; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 900; }
        .action.dark { background: #0f172a; }
        .action.blue { background: #2563eb; }
        .preview { min-height: calc(100vh - 48px); display: flex; flex-direction: column; }
        .preview-head { padding: 13px 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; gap: 12px; align-items: center; }
        .preview-head h2 { margin: 0; font-size: 17px; }
        .preview-frame { width: 100%; flex: 1; min-height: 720px; border: 0; background: #ffffff; }
        .unsupported { min-height: 460px; padding: 28px; display: grid; place-items: center; text-align: center; }
        .unsupported-box { max-width: 520px; padding: 24px; border: 1px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; }
        .unsupported-box h2 { margin: 0 0 8px; }
        .unsupported-box p { margin: 0; color: #64748b; font-weight: 800; line-height: 1.8; }
        @media (max-width: 860px) {
            .viewer { grid-template-columns: 1fr; }
            .preview-frame { min-height: 560px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="viewer">
            <aside class="panel side">
                <div class="brand">
                    <h1>Mr-Student</h1>
                    <p>عرض الملف المرفوع داخل طلبك.</p>
                </div>

                <p class="file-name">{{ $file->original_name }}</p>

                <div class="meta">
                    <div><span>رقم الطلب</span><strong>#{{ $order->id }}</strong></div>
                    <div><span>نوع الملف</span><strong>{{ strtoupper($file->file_type) }}</strong></div>
                    <div><span>عدد الصفحات</span><strong>{{ $file->pages }}</strong></div>
                </div>

                <div class="actions">
                    <a class="action dark" href="{{ route('orders.index', ['open_order' => $order->id]) }}">العودة لعرض الطلب</a>
                </div>
            </aside>

            <main class="panel preview">
                <div class="preview-head">
                    <h2>معاينة الملف</h2>
                    <span>{{ strtoupper($file->file_type) }}</span>
                </div>

                @if ($isPreviewable)
                    <iframe class="preview-frame" src="{{ route('orders.file.view', ['order' => $order, 'file' => $file, 'raw' => 1]) }}"></iframe>
                @else
                    <div class="unsupported">
                        <div class="unsupported-box">
                            <h2>المعاينة غير مدعومة لهذا النوع</h2>
                            <p>إذا كان الملف Word فقد لا يعرضه المتصفح مباشرة. استخدم زر فتح الملف فقط.</p>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
    @include('shared.language-tools')
</body>
</html>
