<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>خدمات الطباعة والتجليد</title>
        <style>
            :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
            * { box-sizing: border-box; }
            body { font-family: Arial, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); }
            .page-header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #f8fafc; padding: clamp(16px, 2vw, 24px) clamp(12px, 1.6vw, 18px); box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); position: fixed; top: 0; right: 0; z-index: 10; }
            .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 28px; }
            .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; }
            .brand-subtitle { margin: 4px 0 0; color: #cbd5e1; font-size: clamp(11px, 1.2vw, 14px); }
            .header-actions { display: flex; flex-direction: column; align-items: stretch; gap: clamp(8px, 1.2vw, 12px); color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); }
            .header-actions a { color: #f8fafc; text-decoration: none; }
            .header-user, .header-link { display: flex; align-items: center; gap: 8px; width: 100%; padding: 10px 12px; border-radius: 10px; background: rgba(255, 255, 255, 0.06); box-sizing: border-box; white-space: normal; line-height: 1.5; }
            .logout-button { width: 100%; background: transparent; color: #f8fafc; border: 1px solid #64748b; border-radius: 10px; padding: 10px 12px; cursor: pointer; text-align: center; }
            .container { width: min(100%, 1000px); margin: clamp(16px, 3vw, 32px) auto 24px; padding: clamp(18px, 3vw, 32px); background: #ffffff; border-radius: clamp(16px, 2vw, 24px); box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08); }
            h1 { margin: 0 0 8px; font-size: clamp(26px, 4vw, 36px); color: #111827; }
            h2 { margin: 28px 0 16px; font-size: clamp(20px, 2.4vw, 24px); color: #1f2937; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
            p { margin: 0 0 26px; color: #475569; line-height: 1.7; }
            
            .services-screen { display: flex; flex-direction: column; gap: 18px; }
            .services-title { margin-top: 0; }
            .service-card { display: flex; flex-direction: column; align-items: stretch; gap: 12px; padding: clamp(16px, 2.4vw, 22px); background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
            .service-icon { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 14px; background: #f1f5f9; color: #0f172a; font-size: 25px; }
            .service-title { margin: 0; color: #0f172a; font-size: clamp(18px, 2.2vw, 22px); font-weight: 900; line-height: 1.5; white-space: normal; overflow-wrap: break-word; }
            .service-description { margin: 0; color: #64748b; font-size: 14px; line-height: 1.7; }
            .service-entry { align-self: flex-start; min-width: 190px; padding: 13px 16px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border: none; border-radius: 10px; cursor: pointer; font-size: 15px; font-weight: 900; transition: all 0.25s ease; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18); }
            .service-entry:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(15, 23, 42, 0.24); background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
            .service-entry:active { transform: translateY(0); }
            
            .back-button { padding: 10px 16px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s; align-self: flex-start; margin-bottom: 20px; }
            .back-button:hover { background: #4b5563; }
            
            .upload-content { display: none; }
            .upload-content.active { display: block; }
            
            .upload-section { margin-top: 40px; display: flex; gap: 20px; flex-wrap: wrap; }
            .upload-box { flex: 1; min-width: 280px; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; background: #f9fafb; text-align: center; transition: all 0.3s; cursor: pointer; }
            .upload-box:hover { border-color: #38bdf8; background: #f0f9ff; }
            .upload-box.drag-over { border-color: #0f172a; background: #e0e7ff; }
            .upload-box h3 { margin: 0 0 12px; font-size: 18px; color: #111827; }
            .upload-box .file-icon { font-size: 36px; margin-bottom: 12px; }
            .upload-box input[type="file"] { display: none; }
            .upload-box .file-info { margin-top: 12px; font-size: 12px; color: #6b7280; }
            .upload-box .file-name { margin-top: 8px; padding: 8px; background: #ffffff; border-radius: 6px; color: #047857; font-weight: 600; }
            .upload-box.error { border-color: #ef4444; background: #fef2f2; }
            .upload-box.error .error-msg { color: #b91c1c; margin-top: 8px; font-size: 12px; }
            
            .files-list { margin-top: 20px; border: 1px solid #e2e8f0; border-radius: 12px; overflow-x: auto; overflow-y: hidden; background: #ffffff; }
            .files-list-header { min-width: 760px; background: #f8fafc; padding: 12px 16px; font-weight: 700; color: #111827; border-bottom: 1px solid #e2e8f0; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 12px; font-size: 13px; }
            .files-list-item { min-width: 760px; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 12px; align-items: center; font-size: 13px; }
            .files-list-header.has-price,
            .files-list-item.has-price { min-width: 1040px; grid-template-columns: 2fr 0.7fr 0.7fr 1.15fr 0.85fr 0.85fr 0.85fr 0.7fr 0.45fr; }
            .files-list-header.has-copies-price,
            .files-list-item.has-copies-price { min-width: 1040px; grid-template-columns: 2fr 0.7fr 0.7fr 0.65fr 0.85fr 0.85fr 0.85fr 0.7fr 0.45fr; }
            .files-list-header.has-academic-university,
            .files-list-item.has-academic-university { min-width: 1200px; grid-template-columns: 2fr 0.7fr 0.7fr 0.65fr 1.45fr 0.85fr 0.85fr 0.85fr 0.7fr 0.45fr; }
            .files-list-header.has-thesis-project,
            .files-list-item.has-thesis-project { min-width: 1320px; grid-template-columns: 2fr 0.7fr 0.7fr 0.65fr 1.15fr 1.45fr 0.85fr 0.85fr 0.85fr 0.7fr 0.45fr; }
            .files-list-item:last-child { border-bottom: none; }
            .file-name-cell { color: #111827; font-weight: 800; word-break: normal; overflow-wrap: anywhere; line-height: 1.6; }
            .file-pages { color: #475569; }
            .file-size { color: #6b7280; }
            .file-price { color: #0f172a; font-weight: 700; }
            .file-price-note { display: block; margin-top: 4px; color: #b91c1c; font-size: 11px; font-weight: 600; line-height: 1.4; }
            .binding-select { width: 100%; min-width: 130px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; color: #111827; font-weight: 600; }
            .binding-select:invalid { color: #6b7280; }
            .university-cell { display: flex; flex-direction: column; gap: 8px; }
            .university-input { width: 100%; min-width: 170px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; color: #111827; font-weight: 700; }
            .university-input:placeholder-shown { color: #6b7280; }
            .university-custom-input { width: 100%; min-width: 170px; padding: 8px 10px; border: 1px solid #94a3b8; border-radius: 6px; background: #f8fafc; color: #111827; font-weight: 700; }
            .copies-input { width: 72px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; color: #111827; font-weight: 700; text-align: center; }
            .file-remove { cursor: pointer; color: #ef4444; font-weight: 600; text-align: center; }
            .file-remove:hover { color: #b91c1c; }
            .empty-message { padding: 20px; text-align: center; color: #9ca3af; font-size: 14px; }
            
            .progress-bar { margin-top: 12px; width: 100%; height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; display: none; }
            .progress-bar.active { display: block; }
            .progress-bar-fill { height: 100%; background: #38bdf8; width: 0%; transition: width 0.3s; }
            
            .upload-button { padding: 10px 16px; background: #0f172a; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-top: 12px; transition: all 0.3s; }
            .upload-button:hover { background: #1e293b; }
            .upload-button:disabled { background: #cbd5e1; cursor: not-allowed; }
            
            .binding-section { margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; }
            .binding-section h3 { margin-top: 0; margin-bottom: 16px; color: #111827; font-size: 18px; }
            .binding-required { color: #b91c1c; font-weight: 600; font-size: 12px; }
            .pricing-summary { margin-top: 16px; padding: 14px 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; color: #111827; font-weight: 700; line-height: 1.7; }
            .pricing-summary.empty { color: #6b7280; font-weight: 600; }
            .checkout-row { margin-top: 14px; display: flex; justify-content: flex-end; }
            .checkout-button { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; background: #047857; color: #ffffff; border-radius: 8px; text-decoration: none; font-weight: 800; border: 0; cursor: pointer; }
            .checkout-button:hover { background: #065f46; }
            .checkout-button.disabled { background: #cbd5e1; color: #64748b; pointer-events: none; }
            .cart-modal-backdrop { position: fixed; inset: 0; display: none; align-items: flex-start; justify-content: center; padding: clamp(10px, 3vw, 24px) clamp(8px, 3vw, 20px); background: rgba(15, 23, 42, 0.62); z-index: 100; overflow-y: auto; }
            .cart-modal-backdrop.active { display: flex; }
            .cart-modal { width: min(1180px, 100%); height: min(760px, calc(100vh - 20px)); margin: auto 0; background: #ffffff; border-radius: 14px; box-shadow: 0 24px 80px rgba(15, 23, 42, 0.32); overflow: hidden; border: 1px solid rgba(226, 232, 240, 0.9); display: flex; flex-direction: column; }
            .cart-modal-head { display: flex; justify-content: space-between; align-items: center; gap: 14px; padding: clamp(12px, 3vw, 16px) clamp(14px, 3vw, 18px); background: #0f172a; color: #ffffff; }
            .cart-modal-title { font-size: clamp(16px, 4vw, 18px); font-weight: 900; }
            .cart-modal-close { border: 1px solid #64748b; background: transparent; color: #ffffff; border-radius: 8px; padding: 7px 11px; cursor: pointer; font-weight: 900; }
            .cart-modal-frame { display: block; width: 100%; flex: 1; min-height: 0; border: 0; background: #f3f4f6; }
            
            .page-footer { background: #0f172a; color: #cbd5e1; padding: 22px 24px; }
            .footer-content { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; font-size: 14px; }
            @media (max-width: 768px) {
                :root { --sidebar-width: 132px; --page-gap: 10px; }
                .page-header { padding: 14px 8px; box-shadow: -8px 0 24px rgba(15, 23, 42, 0.14); }
                .header-inner { gap: 14px; }
                .header-user, .header-link { padding: 9px 8px; gap: 5px; }
                .logout-button { padding: 9px 8px; }
                .container, .footer-content { padding: 16px; }
                .service-entry { width: 100%; }
                .upload-section { flex-direction: column; }
                .upload-box { min-width: 100%; }
                .files-list { border: 0; background: transparent; overflow: visible; }
                .files-list-header { display: none; }
                .files-list-item,
                .files-list-item.has-price,
                .files-list-item.has-copies-price,
                .files-list-item.has-academic-university,
                .files-list-item.has-thesis-project {
                    min-width: 0;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    padding: 14px;
                    margin-bottom: 12px;
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    background: #ffffff;
                    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
                }
                .files-list-item > div {
                    width: 100%;
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 12px;
                    padding: 8px 0;
                    border-bottom: 1px solid #f1f5f9;
                    line-height: 1.6;
                }
                .files-list-item > div:last-child { border-bottom: 0; }
                .files-list-item > div::before {
                    content: attr(data-label);
                    flex: 0 0 auto;
                    color: #64748b;
                    font-size: 12px;
                    font-weight: 900;
                    white-space: nowrap;
                }
                .files-list-item > div:empty { display: none; }
                .file-name-cell {
                    display: block !important;
                    padding: 0 0 10px !important;
                    border-bottom: 1px solid #e2e8f0 !important;
                    font-size: 14px;
                    text-align: right;
                }
                .file-name-cell::before {
                    display: block;
                    margin-bottom: 6px;
                }
                .binding-select,
                .copies-input,
                .university-input,
                .university-custom-input {
                    width: min(100%, 220px);
                    min-width: 0;
                }
            }
            @media (max-width: 420px) {
                :root { --sidebar-width: 92px; --page-gap: 8px; }
                .brand { font-size: 16px; }
                .brand-subtitle { font-size: 10px; }
                .header-actions { font-size: 11px; }
                .header-user, .header-link { padding: 8px 6px; }
                .container { padding: 14px; border-radius: 14px; }
            }
        </style>
    </head>
    <body>
        <header class="page-header">
            <div class="header-inner">
                <div>
                    <div class="brand">Mr-Student</div>
                    <p class="brand-subtitle">خدمات الطباعة والتجليد</p>
                </div>
                <div class="header-actions">
                    <span class="header-user">👤 {{ auth()->user()->name }}</span>
                    <a class="header-link" href="{{ route('orders.index') }}">🧾 طلباتي</a>
                    <a class="header-link" href="{{ route('account.settings') }}">⚙️ إعداداتي</a>
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.orders') }}">لوحة المدير</a>
                    @endif
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">خروج</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="container" id="services">
            <!-- Services Selection Screen -->
            <div id="servicesScreen" class="services-screen">
                <h2 class="services-title">اختر الخدمة المطلوبة</h2>

                <article class="service-card">
                    <div class="service-icon">📝</div>
                    <h3 class="service-title">طباعة وتغليف المذكرات</h3>
                    <p class="service-description">رفع ملفات Word أو PDF، اختيار نوع التغليف، ومراجعة السعر قبل إتمام الطلب.</p>
                    <button class="service-entry" type="button" onclick="selectService('notes')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">📚</div>
                    <h3 class="service-title">طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج</h3>
                    <p class="service-description">خدمة مخصصة للرسائل العلمية والبحث التكميلي وبحث التخرج مع احتساب النسخ والتجليد.</p>
                    <button class="service-entry" type="button" onclick="selectService('thesis')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">🎓</div>
                    <h3 class="service-title">طباعة وتجليد رسالة دكتوراه</h3>
                    <p class="service-description">تجهيز ملفات الدكتوراه للطباعة والتجليد مع عرض كامل للتكاليف قبل الدفع.</p>
                    <button class="service-entry" type="button" onclick="selectService('phd')">الدخول للخدمة</button>
                </article>
            </div>

            <!-- Upload Section for Notes -->
            <div id="uploadNotes" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات مذكرات</h2>
                
                <div class="upload-section">
                    <div class="upload-box" id="notesWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملفات Word</h3>
                        <input type="file" id="notesWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="notesWordUploadBtn" onclick="document.getElementById('notesWordFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="notesWordProgress"><div class="progress-bar-fill"></div></div>
                        <div id="notesWordError" class="error-msg" style="display: none;"></div>
                    </div>

                    <div class="upload-box" id="notesPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="notesPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="notesPdfUploadBtn" onclick="document.getElementById('notesPdfFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="notesPdfProgress"><div class="progress-bar-fill"></div></div>
                        <div id="notesPdfError" class="error-msg" style="display: none;"></div>
                    </div>
                </div>

                <div style="margin-top: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📄 ملفات Word المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-price">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>نوع التغليف</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التجليد</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="notesWordFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div style="margin-top: 40px; margin-bottom: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📕 ملفات PDF المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-price">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>نوع التغليف</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التجليد</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="notesPdfFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <!-- Binding Options for Notes -->
                <div class="binding-section">
                    <h3>إجمالي المذكرات</h3>
                    <div id="notesPricingSummary" class="pricing-summary empty">اختر نوع التغليف لكل ملف لعرض السعر.</div>
                </div>
            </div>

            <!-- Upload Section for Thesis -->
            <div id="uploadThesis" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات رسالة ماجستير أو بحث</h2>
                
                <div class="upload-section">
                    <div class="upload-box" id="thesisWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملفات Word</h3>
                        <input type="file" id="thesisWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="thesisWordUploadBtn" onclick="document.getElementById('thesisWordFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="thesisWordProgress"><div class="progress-bar-fill"></div></div>
                        <div id="thesisWordError" class="error-msg" style="display: none;"></div>
                    </div>

                    <div class="upload-box" id="thesisPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="thesisPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="thesisPdfUploadBtn" onclick="document.getElementById('thesisPdfFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="thesisPdfProgress"><div class="progress-bar-fill"></div></div>
                        <div id="thesisPdfError" class="error-msg" style="display: none;"></div>
                    </div>
                </div>

                <div style="margin-top: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📄 ملفات Word المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-academic-university">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>النسخ</div>
                            <div>الجامعة/المعهد</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التجليد</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="thesisWordFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div style="margin-top: 40px; margin-bottom: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📕 ملفات PDF المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-thesis-project">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>النسخ</div>
                            <div>مشروع الرسالة</div>
                            <div>الجامعة/المعهد</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التجليد</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="thesisPdfFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي الماجستير</h3>
                    <div id="thesisPricingSummary" class="pricing-summary empty">ارفع الملفات لعرض الإجمالي.</div>
                </div>
            </div>

            <!-- Upload Section for PhD -->
            <div id="uploadPhd" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات رسالة دكتوراه</h2>
                
                <div class="upload-section">
                    <div class="upload-box" id="phdWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملفات Word</h3>
                        <input type="file" id="phdWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="phdWordUploadBtn" onclick="document.getElementById('phdWordFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="phdWordProgress"><div class="progress-bar-fill"></div></div>
                        <div id="phdWordError" class="error-msg" style="display: none;"></div>
                    </div>

                    <div class="upload-box" id="phdPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="phdPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="phdPdfUploadBtn" onclick="document.getElementById('phdPdfFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="phdPdfProgress"><div class="progress-bar-fill"></div></div>
                        <div id="phdPdfError" class="error-msg" style="display: none;"></div>
                    </div>
                </div>

                <div style="margin-top: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📄 ملفات Word المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-academic-university">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>النسخ</div>
                            <div>الجامعة/المعهد</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التجليد</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="phdWordFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div style="margin-top: 40px; margin-bottom: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📕 ملفات PDF المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-academic-university">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>النسخ</div>
                            <div>الجامعة/المعهد</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التجليد</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="phdPdfFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي الدكتوراه</h3>
                    <div id="phdPricingSummary" class="pricing-summary empty">ارفع الملفات لعرض الإجمالي.</div>
                </div>
            </div>
        </main>

        <datalist id="saudiUniversitiesList"></datalist>

        <script>
            // Store uploaded files for each service
            const uploadedFiles = {
                notes: { word: [], pdf: [] },
                thesis: { word: [], pdf: [] },
                phd: { word: [], pdf: [] }
            };
            const currentOrders = {
                notes: null,
                thesis: null,
                phd: null
            };

            const OTHER_UNIVERSITY_VALUE = 'أخرى';
            const saudiUniversitiesAndInstitutes = [
                'جامعة أم القرى',
                'الجامعة الإسلامية بالمدينة المنورة',
                'جامعة الإمام محمد بن سعود الإسلامية',
                'جامعة الملك سعود',
                'جامعة الملك عبدالعزيز',
                'جامعة الملك فيصل',
                'جامعة الملك خالد',
                'جامعة القصيم',
                'جامعة طيبة',
                'جامعة الطائف',
                'جامعة حائل',
                'جامعة جازان',
                'جامعة الجوف',
                'جامعة الباحة',
                'جامعة تبوك',
                'جامعة نجران',
                'جامعة الحدود الشمالية',
                'جامعة الأميرة نورة بنت عبدالرحمن',
                'جامعة الملك سعود بن عبدالعزيز للعلوم الصحية',
                'جامعة الإمام عبدالرحمن بن فيصل',
                'جامعة الأمير سطام بن عبدالعزيز',
                'جامعة شقراء',
                'جامعة المجمعة',
                'الجامعة السعودية الإلكترونية',
                'جامعة جدة',
                'جامعة بيشة',
                'جامعة حفر الباطن',
                'جامعة الملك عبدالله للعلوم والتقنية',
                'جامعة الملك فهد للبترول والمعادن',
                'جامعة الأمير محمد بن فهد',
                'جامعة الأمير سلطان',
                'جامعة اليمامة',
                'جامعة دار العلوم',
                'جامعة عفت',
                'جامعة دار الحكمة',
                'جامعة الأعمال والتكنولوجيا',
                'جامعة الفيصل',
                'جامعة رياض العلم',
                'جامعة المعرفة',
                'جامعة المستقبل',
                'جامعة سليمان الراجحي',
                'جامعة الأمير مقرن بن عبدالعزيز',
                'جامعة الأمير فهد بن سلطان',
                'جامعة الأصالة',
                'جامعة اليمامة الأهلية',
                'كليات عنيزة الأهلية',
                'كليات بريدة الأهلية',
                'كلية البترجي الطبية',
                'كليات الريان الأهلية',
                'كليات الشرق العربي',
                'كلية ابن سينا الأهلية للعلوم الطبية',
                'كلية الفارابي الأهلية',
                'كلية جدة العالمية',
                'كليات الخليج',
                'كلية المعرفة للعلوم والتقنية',
                'كلية الأمير سلطان العسكرية للعلوم الصحية',
                'كلية الملك فهد الأمنية',
                'كلية الملك خالد العسكرية',
                'كلية الملك عبدالعزيز الحربية',
                'كلية الملك فيصل الجوية',
                'كلية الملك فهد البحرية',
                'كلية الملك عبدالله للدفاع الجوي',
                'معهد الإدارة العامة',
                'معهد الجبيل التقني',
                'كلية الجبيل الصناعية',
                'كلية الجبيل الجامعية',
                'معهد ينبع التقني',
                'كلية ينبع الصناعية',
                'كلية ينبع الجامعية',
                'المعهد السعودي التقني للخطوط الحديدية',
                'المعهد العالي للصناعات البلاستيكية',
                'المعهد السعودي للإلكترونيات والأجهزة المنزلية',
                'المعهد التقني السعودي لخدمات البترول',
                'الأكاديمية السعودية الرقمية',
                'المعهد الوطني للتدريب الصناعي',
                'الكلية التقنية',
                'الكلية التقنية العالمية',
                'المعهد الثانوي الصناعي',
                'المعهد المهني الصناعي'
            ];

            const knownUniversities = new Set(saudiUniversitiesAndInstitutes);

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function initializeSaudiUniversitiesList() {
                const list = document.getElementById('saudiUniversitiesList');
                if (!list || list.dataset.ready === 'true') return;

                list.innerHTML = [
                    ...saudiUniversitiesAndInstitutes,
                    OTHER_UNIVERSITY_VALUE
                ].map(name => `<option value="${escapeHtml(name)}"></option>`).join('');
                list.dataset.ready = 'true';
            }

            const getCsrfToken = () => {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            };

            async function updateStoredFile(fileData, payload) {
                if (!fileData.id) return;

                try {
                    await fetch(`/order-files/${fileData.id}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                } catch (error) {
                    console.error('Failed to update file details', error);
                }
            }

            const fileConfigs = {
                notesWord: { inputId: 'notesWordFile', boxId: 'notesWordBox', progressId: 'notesWordProgress', errorId: 'notesWordError', listId: 'notesWordFilesList', service: 'notes', type: 'word' },
                notesPdf: { inputId: 'notesPdfFile', boxId: 'notesPdfBox', progressId: 'notesPdfProgress', errorId: 'notesPdfError', listId: 'notesPdfFilesList', service: 'notes', type: 'pdf' },
                thesisWord: { inputId: 'thesisWordFile', boxId: 'thesisWordBox', progressId: 'thesisWordProgress', errorId: 'thesisWordError', listId: 'thesisWordFilesList', service: 'thesis', type: 'word' },
                thesisPdf: { inputId: 'thesisPdfFile', boxId: 'thesisPdfBox', progressId: 'thesisPdfProgress', errorId: 'thesisPdfError', listId: 'thesisPdfFilesList', service: 'thesis', type: 'pdf' },
                phdWord: { inputId: 'phdWordFile', boxId: 'phdWordBox', progressId: 'phdWordProgress', errorId: 'phdWordError', listId: 'phdWordFilesList', service: 'phd', type: 'word' },
                phdPdf: { inputId: 'phdPdfFile', boxId: 'phdPdfBox', progressId: 'phdPdfProgress', errorId: 'phdPdfError', listId: 'phdPdfFilesList', service: 'phd', type: 'pdf' }
            };

            const fileTypes = {
                word: { types: ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], extensions: ['.doc', '.docx'] },
                pdf: { types: ['application/pdf'], extensions: ['.pdf'] }
            };

            function selectService(service) {
                initializeSaudiUniversitiesList();
                document.getElementById('servicesScreen').style.display = 'none';
                document.getElementById('upload' + service.charAt(0).toUpperCase() + service.slice(1)).classList.add('active');
                initializeService(service);
            }

            function backToServices() {
                document.getElementById('servicesScreen').style.display = 'flex';
                document.getElementById('uploadNotes').classList.remove('active');
                document.getElementById('uploadThesis').classList.remove('active');
                document.getElementById('uploadPhd').classList.remove('active');
            }

            function initializeService(service) {
                const configKeys = Object.keys(fileConfigs).filter(key => fileConfigs[key].service === service);
                configKeys.forEach(key => setupFileUpload(key));
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }

            function calculateNotesFilePrice(pages, binding) {
                const printPrice = Math.ceil(pages / 15);
                let bindingPrice = 0;
                let note = '';

                if (binding === 'normal') {
                    bindingPrice = 3;
                } else if (binding === 'wire') {
                    if (pages < 100) {
                        bindingPrice = 5;
                    } else if (pages < 300) {
                        bindingPrice = 7;
                    } else if (pages <= 600) {
                        bindingPrice = 9;
                    } else {
                        bindingPrice = 14;
                        note = 'الملف لازم يتقسم على ملفين';
                    }
                }

                return {
                    printPrice,
                    bindingPrice,
                    total: printPrice + bindingPrice,
                    note
                };
            }

            function getPrintPrice(pages) {
                return Math.ceil(pages / 15);
            }

            function calculateAcademicFilePrice(service, pages, copies) {
                const copyCount = Math.max(1, Number(copies) || 1);
                const printPrice = getPrintPrice(pages) * copyCount;
                const bindingSinglePrice = service === 'phd' ? 90 : 70;
                const bindingMultiPrice = service === 'phd' ? 70 : 55;
                const bindingPrice = copyCount === 1 ? bindingSinglePrice : bindingMultiPrice * copyCount;

                return {
                    printPrice,
                    bindingPrice,
                    total: printPrice + bindingPrice
                };
            }

            function getAllNotesFiles() {
                return [
                    ...uploadedFiles.notes.word,
                    ...uploadedFiles.notes.pdf
                ];
            }

            function getAllServiceFiles(service) {
                return [
                    ...uploadedFiles[service].word,
                    ...uploadedFiles[service].pdf
                ];
            }

            function renderCheckoutSummary(summary, service, message, totals = null, canCheckout = false) {
                const orderId = currentOrders[service];
                summary.classList.toggle('empty', !canCheckout);

                if (!canCheckout || !orderId || !totals) {
                    summary.innerHTML = `
                        <div>${message}</div>
                        <div class="checkout-row">
                            <span class="checkout-button disabled">إتمام الطلب</span>
                            ${orderId ? '<a class="checkout-button" style="margin-inline-start: 8px; background: #0f172a;" href="{{ route('orders.index') }}">طلباتي</a>' : ''}
                        </div>
                    `;
                    return;
                }

                const bindingLabel = service === 'notes' ? 'سعر التغليف' : 'سعر التجليد';

                summary.innerHTML = `
                    <div>سعر الطباعة: ${totals.print} ريال | ${bindingLabel}: ${totals.binding} ريال | الإجمالي: ${totals.total} ريال</div>
                    <div class="checkout-row">
                        <button class="checkout-button" type="button" onclick="openCartModal(${orderId})">إتمام الطلب</button>
                        <a class="checkout-button" style="margin-inline-start: 8px; background: #0f172a;" href="{{ route('orders.index') }}">طلباتي</a>
                    </div>
                `;
            }

            function updateNotesPricingSummary() {
                const summary = document.getElementById('notesPricingSummary');
                if (!summary) return;

                const files = getAllNotesFiles();

                if (files.length === 0) {
                    renderCheckoutSummary(summary, 'notes', 'ارفع الملفات لعرض الإجمالي.');
                    return;
                }

                if (files.some(fileData => !fileData.binding)) {
                    renderCheckoutSummary(summary, 'notes', 'اختر نوع التغليف لكل ملف قبل إتمام الطلب.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = calculateNotesFilePrice(fileData.pages, fileData.binding);
                    sum.print += price.printPrice;
                    sum.binding += price.bindingPrice;
                    sum.total += price.total;
                    return sum;
                }, { print: 0, binding: 0, total: 0 });

                renderCheckoutSummary(summary, 'notes', '', totals, true);
            }

            function updateAcademicPricingSummary(service) {
                const summary = document.getElementById(`${service}PricingSummary`);
                if (!summary) return;

                const files = getAllServiceFiles(service);

                if (files.length === 0) {
                    renderCheckoutSummary(summary, service, 'ارفع الملفات لعرض الإجمالي.');
                    return;
                }

                if (service === 'thesis' && uploadedFiles.thesis.pdf.some(fileData => !fileData.thesisProjectType)) {
                    renderCheckoutSummary(summary, service, 'اختر نوع مشروع الرسالة لكل ملف PDF قبل إتمام الطلب.');
                    return;
                }

                if (files.some(fileData => !fileData.universityName)) {
                    renderCheckoutSummary(summary, service, 'اختر الجامعة أو المعهد لكل ملف قبل إتمام الطلب.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies);
                    sum.print += price.printPrice;
                    sum.binding += price.bindingPrice;
                    sum.total += price.total;
                    return sum;
                }, { print: 0, binding: 0, total: 0 });

                renderCheckoutSummary(summary, service, '', totals, true);
            }

            async function countPDFPages(file) {
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const content = e.target.result;
                        const pageMatches = content.match(/\/Type\s*\/Page[^s]/g);
                        const pageCount = pageMatches ? pageMatches.length : 1;
                        resolve(pageCount);
                    };
                    reader.readAsText(file);
                });
            }

            async function countWordPages(file) {
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = async (e) => {
                        try {
                            const zip = new JSZip();
                            const docxData = await zip.loadAsync(e.target.result);
                            const docXml = await docxData.files['word/document.xml'].async('string');
                            const paragraphs = docXml.match(/<w:p>/g) || [];
                            const pageCount = Math.max(1, Math.ceil(paragraphs.length / 30));
                            resolve(pageCount);
                        } catch (error) {
                            resolve(1);
                        }
                    };
                    reader.readAsArrayBuffer(file);
                });
            }

            function academicUniversityHtml(service, type, index, fileData) {
                if (service !== 'thesis' && service !== 'phd') return '';

                const universityName = fileData.universityName || '';
                const usesOther = fileData.universityChoice === OTHER_UNIVERSITY_VALUE || (universityName && !knownUniversities.has(universityName));
                const searchValue = usesOther ? OTHER_UNIVERSITY_VALUE : universityName;
                const customValue = usesOther ? universityName : '';

                return `
                    <div data-label="الجامعة/المعهد" class="university-cell">
                        <input
                            class="university-input"
                            list="saudiUniversitiesList"
                            value="${escapeHtml(searchValue)}"
                            placeholder="ابحث عن جامعتك"
                            required
                            oninput="setAcademicFileUniversity('${service}', '${type}', ${index}, this.value, false)"
                            onchange="setAcademicFileUniversity('${service}', '${type}', ${index}, this.value)"
                        />
                        ${usesOther ? `
                            <input
                                class="university-custom-input"
                                value="${escapeHtml(customValue)}"
                                placeholder="اكتب اسم الجامعة أو المعهد"
                                required
                                oninput="setCustomAcademicUniversity('${service}', '${type}', ${index}, this.value, false)"
                                onchange="setCustomAcademicUniversity('${service}', '${type}', ${index}, this.value)"
                            />
                        ` : ''}
                    </div>
                `;
            }

            function updateFilesList(configKey) {
                const config = fileConfigs[configKey];
                const listDiv = document.getElementById(config.listId);
                const files = uploadedFiles[config.service][config.type];
                const showPrice = config.service === 'notes';
                const showAcademicPrice = config.service === 'thesis' || config.service === 'phd';
                const showThesisProject = config.service === 'thesis' && config.type === 'pdf';

                if (files.length === 0) {
                    listDiv.innerHTML = '<div class="empty-message">لم يتم تحميل أي ملفات</div>';
                    if (showPrice) {
                        updateNotesPricingSummary();
                    } else if (showAcademicPrice) {
                        updateAcademicPricingSummary(config.service);
                    }
                    return;
                }

                let html = '';
                files.forEach((fileData, index) => {
                    const price = showPrice && fileData.binding ? calculateNotesFilePrice(fileData.pages, fileData.binding) : null;
                    const bindingHtml = showPrice
                        ? `
                            <div data-label="التغليف">
                                <select class="binding-select" required onchange="setNotesFileBinding('${config.type}', ${index}, this.value)">
                                    <option value="" ${!fileData.binding ? 'selected' : ''} disabled>اختر التغليف</option>
                                    <option value="tape" ${fileData.binding === 'tape' ? 'selected' : ''}>تغليف دبوس</option>
                                    <option value="wire" ${fileData.binding === 'wire' ? 'selected' : ''}>تغليف سلك</option>
                                    <option value="normal" ${fileData.binding === 'normal' ? 'selected' : ''}>تغليف عادي</option>
                                    <option value="none" ${fileData.binding === 'none' ? 'selected' : ''}>بدون أي تغليف</option>
                                </select>
                            </div>
                        `
                        : '';
                    const notesPrintPriceHtml = showPrice
                        ? `<div class="file-price" data-label="سعر الطباعة">${price ? `${price.printPrice} ريال` : '-'}</div>`
                        : '';
                    const notesBindingPriceHtml = showPrice
                        ? `<div class="file-price" data-label="سعر التغليف">${price ? `${price.bindingPrice} ريال` : '-'}</div>`
                        : '';
                    const notesTotalPriceHtml = showPrice
                        ? `<div class="file-price" data-label="الإجمالي">${price ? `${price.total} ريال` : 'اختر التغليف'}${price?.note ? `<span class="file-price-note">${price.note}</span>` : ''}</div>`
                        : '';
                    const academicPrice = showAcademicPrice ? calculateAcademicFilePrice(config.service, fileData.pages, fileData.copies) : null;
                    const copiesHtml = showAcademicPrice
                        ? `<div data-label="عدد النسخ"><input class="copies-input" type="number" min="1" step="1" value="${fileData.copies || 1}" onchange="setAcademicFileCopies('${config.service}', '${config.type}', ${index}, this.value)" /></div>`
                        : '';
                    const thesisProjectHtml = showThesisProject
                        ? `
                            <div data-label="مشروع الرسالة">
                                <select class="binding-select" onchange="setThesisProjectType(${index}, this.value)">
                                    <option value="" ${!fileData.thesisProjectType ? 'selected' : ''} disabled>اختر المشروع</option>
                                    <option value="thesis" ${fileData.thesisProjectType === 'thesis' ? 'selected' : ''}>رسالة ماجستير</option>
                                    <option value="supplementary" ${fileData.thesisProjectType === 'supplementary' ? 'selected' : ''}>بحث تكميلي</option>
                                    <option value="graduation" ${fileData.thesisProjectType === 'graduation' ? 'selected' : ''}>بحث تخرج</option>
                                </select>
                            </div>
                        `
                        : '';
                    const universityHtml = showAcademicPrice
                        ? academicUniversityHtml(config.service, config.type, index, fileData)
                        : '';
                    const academicPrintPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="سعر الطباعة">${academicPrice.printPrice} ريال</div>`
                        : '';
                    const academicBindingPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="سعر التجليد">${academicPrice.bindingPrice} ريال</div>`
                        : '';
                    const academicTotalPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="الإجمالي">${academicPrice.total} ريال</div>`
                        : '';

                    html += `
                        <div class="files-list-item${showPrice ? ' has-price' : ''}${showAcademicPrice ? ' has-academic-university' : ''}${showThesisProject ? ' has-thesis-project' : ''}">
                            <div class="file-name-cell" data-label="اسم الملف">${fileData.filename}</div>
                            <div class="file-pages" data-label="الصفحات">${fileData.pages} صفحة</div>
                            <div class="file-size" data-label="الحجم">${fileData.size}</div>
                            ${bindingHtml}
                            ${notesPrintPriceHtml}
                            ${notesBindingPriceHtml}
                            ${notesTotalPriceHtml}
                            ${copiesHtml}
                            ${thesisProjectHtml}
                            ${universityHtml}
                            ${academicPrintPriceHtml}
                            ${academicBindingPriceHtml}
                            ${academicTotalPriceHtml}
                            <div data-label="الحالة" style="color: #047857; font-weight: 600;">✓ مرفوع</div>
                            <div class="file-remove" data-label="الإجراء" onclick="removeFile('${config.service}', '${config.type}', ${index})">حذف</div>
                        </div>
                    `;
                });

                listDiv.innerHTML = html;
                if (showPrice) {
                    updateNotesPricingSummary();
                } else if (showAcademicPrice) {
                    updateAcademicPricingSummary(config.service);
                }
            }

            async function deleteStoredFile(fileData) {
                if (!fileData.id) return;

                try {
                    await fetch(`/order-files/${fileData.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    });
                } catch (error) {
                    console.error('Failed to delete file', error);
                }
            }

            async function removeFile(service, type, index) {
                const fileData = uploadedFiles[service][type][index];
                await deleteStoredFile(fileData);
                uploadedFiles[service][type].splice(index, 1);
                const configKey = Object.keys(fileConfigs).find(key => fileConfigs[key].service === service && fileConfigs[key].type === type);
                updateFilesList(configKey);
                if (service === 'notes') {
                    updateNotesPricingSummary();
                } else if (service === 'thesis' || service === 'phd') {
                    updateAcademicPricingSummary(service);
                }
            }

            function setNotesFileBinding(type, index, binding) {
                const fileData = uploadedFiles.notes[type][index];
                fileData.binding = binding;
                const price = calculateNotesFilePrice(fileData.pages, binding);
                updateStoredFile(fileData, {
                    binding_type: binding,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(`notes${type.charAt(0).toUpperCase() + type.slice(1)}`);
                updateNotesPricingSummary();
            }

            function setAcademicFileCopies(service, type, index, copies) {
                const fileData = uploadedFiles[service][type][index];
                fileData.copies = Math.max(1, Number(copies) || 1);
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies);
                updateStoredFile(fileData, {
                    copies: fileData.copies,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                updateAcademicPricingSummary(service);
            }

            function setAcademicFileUniversity(service, type, index, value, rerender = true) {
                const fileData = uploadedFiles[service][type][index];
                const selectedValue = String(value || '').trim();
                const wasOther = fileData.universityChoice === OTHER_UNIVERSITY_VALUE;

                fileData.universityChoice = selectedValue;
                if (selectedValue === OTHER_UNIVERSITY_VALUE) {
                    fileData.universityName = fileData.customUniversity || '';
                } else {
                    fileData.universityName = selectedValue;
                    fileData.customUniversity = '';
                }

                updateStoredFile(fileData, {
                    university_name: fileData.universityName || null
                });
                if (rerender || selectedValue === OTHER_UNIVERSITY_VALUE || wasOther) {
                    updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                }
                updateAcademicPricingSummary(service);
            }

            function setCustomAcademicUniversity(service, type, index, value, rerender = true) {
                const fileData = uploadedFiles[service][type][index];
                const customValue = String(value || '').trim();

                fileData.universityChoice = OTHER_UNIVERSITY_VALUE;
                fileData.customUniversity = customValue;
                fileData.universityName = customValue;

                updateStoredFile(fileData, {
                    university_name: fileData.universityName || null
                });
                if (rerender) {
                    updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                }
                updateAcademicPricingSummary(service);
            }

            function setThesisProjectType(index, projectType) {
                const fileData = uploadedFiles.thesis.pdf[index];
                fileData.thesisProjectType = projectType;
                updateStoredFile(fileData, {
                    thesis_project_type: projectType
                });
                updateFilesList('thesisPdf');
            }

            function updateNotesFilesPricing() {
                updateFilesList('notesWord');
                updateFilesList('notesPdf');
            }

            function setupFileUpload(configKey) {
                const config = fileConfigs[configKey];
                const fileType = fileTypes[config.type];
                const input = document.getElementById(config.inputId);
                const box = document.getElementById(config.boxId);

                if (input.dataset.uploadReady === 'true') {
                    return;
                }
                input.dataset.uploadReady = 'true';

                input.addEventListener('change', function(e) {
                    const files = Array.from(e.target.files);
                    if (files.length > 0) {
                        uploadMultipleFiles(files, configKey);
                    }
                });

                box.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    box.classList.add('drag-over');
                });

                box.addEventListener('dragleave', () => {
                    box.classList.remove('drag-over');
                });

                box.addEventListener('drop', (e) => {
                    e.preventDefault();
                    box.classList.remove('drag-over');
                    const files = Array.from(e.dataTransfer.files);
                    if (files.length > 0) {
                        uploadMultipleFiles(files, configKey);
                    }
                });
            }

            async function uploadMultipleFiles(files, configKey) {
                const config = fileConfigs[configKey];
                const fileType = fileTypes[config.type];
                const errorDiv = document.getElementById(config.errorId);
                const progressDiv = document.getElementById(config.progressId);
                const progressBar = progressDiv.querySelector('.progress-bar-fill');
                const uploadBtn = document.getElementById(config.inputId.replace('File', 'UploadBtn'));

                const validFiles = files.filter(file => {
                    const isValidType = fileType.types.includes(file.type) || 
                                       fileType.extensions.some(ext => file.name.toLowerCase().endsWith(ext));
                    if (!isValidType) {
                        errorDiv.textContent = `${file.name}: صيغة غير صحيحة`;
                        errorDiv.style.display = 'block';
                        return false;
                    }
                    return true;
                });

                if (validFiles.length === 0) return;

                errorDiv.style.display = 'none';
                errorDiv.textContent = '';
                uploadBtn.disabled = true;
                progressDiv.classList.add('active');

                let completed = 0;
                for (const file of validFiles) {
                    progressBar.style.width = ((completed / validFiles.length) * 100) + '%';
                    
                    let pageCount = 1;
                    try {
                        if (config.type === 'pdf') {
                            pageCount = await countPDFPages(file);
                        } else {
                            if (typeof JSZip !== 'undefined') {
                                pageCount = await countWordPages(file);
                            }
                        }
                    } catch (e) {
                        pageCount = 1;
                    }

                    await uploadFile(file, configKey, pageCount);
                    completed++;
                }

                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressDiv.classList.remove('active');
                    uploadBtn.disabled = false;
                    document.getElementById(config.inputId).value = '';
                }, 500);
            }

            function uploadFile(file, configKey, pageCount) {
                return new Promise((resolve) => {
                    const config = fileConfigs[configKey];
                    const errorDiv = document.getElementById(config.errorId);
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', config.type);
                    formData.append('service', config.service);

                    const xhr = new XMLHttpRequest();

                    xhr.addEventListener('load', () => {
                        let response = {};
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (error) {
                            response = {};
                        }

                        if (xhr.status === 200) {
                            if (response.success) {
                                currentOrders[config.service] = response.order_id;
                                uploadedFiles[config.service][config.type].push({
                                    id: response.file_id,
                                    filename: response.filename,
                                    pages: response.pages || pageCount,
                                    size: formatFileSize(response.size),
                                    binding: '',
                                    copies: 1,
                                    thesisProjectType: '',
                                    universityChoice: '',
                                    universityName: response.university_name || '',
                                    customUniversity: ''
                                });
                                updateFilesList(configKey);
                            }
                        } else {
                            errorDiv.textContent = response.message || 'تعذر تحميل الملف';
                            errorDiv.style.display = 'block';
                        }
                        resolve();
                    });

                    xhr.addEventListener('error', () => {
                        errorDiv.textContent = 'تعذر الاتصال بالخادم أثناء تحميل الملف';
                        errorDiv.style.display = 'block';
                        resolve();
                    });

                    xhr.open('POST', '/upload-file');
                    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                });
            }

            function openCartModal(orderId) {
                const modal = document.getElementById('cartModal');
                const frame = document.getElementById('cartModalFrame');

                frame.src = `/cart/${orderId}`;
                modal.classList.add('active');
                modal.focus();
                document.body.style.overflow = 'hidden';
            }

            function closeCartModal() {
                const modal = document.getElementById('cartModal');
                const frame = document.getElementById('cartModalFrame');

                modal.classList.remove('active');
                frame.src = 'about:blank';
                document.body.style.overflow = '';
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeCartModal();
                }
            });

            // Load JSZip library
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            document.head.appendChild(script);
        </script>

        <div class="cart-modal-backdrop" id="cartModal" tabindex="-1" onclick="if (event.target.id === 'cartModal') closeCartModal()">
            <div class="cart-modal" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
                <div class="cart-modal-head">
                    <div class="cart-modal-title">السلة والدفع</div>
                    <button class="cart-modal-close" type="button" onclick="closeCartModal()">إغلاق</button>
                </div>
                <iframe class="cart-modal-frame" id="cartModalFrame" title="السلة والدفع" src="about:blank"></iframe>
            </div>
        </div>

        <footer class="page-footer" id="info">
            <div class="footer-content">
                <p>منصة متخصصة في خدمات الطباعة والتجليد للمذكرات والأبحاث والرسائل العلمية.</p>
                <p>&copy; 2026 خدمات الطباعة والتجليد. جميع الحقوق محفوظة.</p>
            </div>
        </footer>
    </body>
</html>
