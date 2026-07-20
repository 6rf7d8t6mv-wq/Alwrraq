<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>خدمات الطباعة والتجليد</title>
        <style>
            :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
            * { box-sizing: border-box; }
            body { min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; font-family: Arial, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); }
            .page-header { width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; background: #0f172a; color: #f8fafc; padding: clamp(16px, 2vw, 24px) clamp(12px, 1.6vw, 18px); box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); position: fixed; top: 0; right: 0; z-index: 10; }
            .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 0; }
            .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; margin-bottom: 4px; }
            .brand-logo { width: 46px; height: 46px; border-radius: 14px; object-fit: cover; background: #ffffff; border: 1px solid rgba(255,255,255,0.18); box-shadow: 0 12px 26px rgba(0,0,0,0.18); margin-bottom: 10px; display: block; }
            .brand-subtitle { margin: 4px 0 0; color: #cbd5e1; font-size: clamp(11px, 1.2vw, 14px); }
            .header-actions { display: flex; flex-direction: column; align-items: stretch; gap: clamp(8px, 1.2vw, 12px); color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin-top: 24px; }
            .header-actions a { color: #f8fafc; text-decoration: none; }
            .header-user { display: block; color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin: 0 0 12px; line-height: 1.6; }
            .header-link { display: flex; align-items: center; gap: 8px; width: 100%; padding: 10px 12px; border-radius: 10px; background: rgba(255, 255, 255, 0.06); box-sizing: border-box; white-space: normal; line-height: 1.5; border: 1px solid transparent; }
            .header-link:hover { background: #1e293b; border-color: #334155; }
            .header-link { position: relative; }
            .header-link.settings-link { background: #0f4c81; border: 1px solid rgba(96, 165, 250, 0.35); }
            .header-link.settings-link:hover { background: #1d6fa5; border-color: #60a5fa; }
            .customer-notice-dot { position: absolute; top: 8px; left: 9px; width: 7px; height: 7px; border-radius: 999px; background: #dc2626; box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.95); }
            .logout-button { width: 100%; background: #b91c1c; color: #f8fafc; border: 1px solid rgba(248, 113, 113, 0.5); border-radius: 10px; padding: 10px 12px; cursor: pointer; text-align: center; font-weight: 800; }
            .logout-button:hover { background: #dc2626; border-color: #f87171; }
            .container { width: min(100%, 1000px); margin: clamp(16px, 3vw, 32px) auto 24px; padding: clamp(18px, 3vw, 32px); background: #ffffff; border-radius: clamp(16px, 2vw, 24px); box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08); }
            h1 { margin: 0 0 8px; font-size: clamp(26px, 4vw, 36px); color: #111827; }
            h2 { margin: 28px 0 16px; font-size: clamp(20px, 2.4vw, 24px); color: #1f2937; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
            p { margin: 0 0 26px; color: #475569; line-height: 1.7; }
            
            .services-screen { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: clamp(14px, 2vw, 20px); align-items: stretch; }
            .services-title-block { grid-column: 1 / -1; padding: clamp(18px, 3vw, 26px); border-radius: 20px; background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%); color: #0f172a; border: 1px solid #bfdbfe; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
            .services-title { margin: 0; color: #0f172a; border: 0; padding: 0; font-size: clamp(24px, 3.2vw, 34px); line-height: 1.4; }
            .services-subtitle { margin: 8px 0 0; color: #475569; font-size: clamp(13px, 1.5vw, 15px); line-height: 1.8; }
            .service-card { min-height: 250px; display: flex; flex-direction: column; align-items: stretch; gap: 13px; padding: clamp(18px, 2.6vw, 26px); background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease; }
            .service-card:hover { transform: translateY(-4px); border-color: #cbd5e1; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.13); }
            .service-icon { width: 56px; height: 56px; display: inline-flex; align-items: center; justify-content: center; border-radius: 16px; background: #eef2ff; color: #0f172a; font-size: 28px; box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.04); }
            .service-title { margin: 0; color: #0f172a; font-size: clamp(17px, 1.8vw, 21px); font-weight: 900; line-height: 1.5; white-space: normal; overflow-wrap: break-word; }
            .service-description { margin: 0; color: #64748b; font-size: 14px; line-height: 1.8; flex: 1; }
            .service-entry { width: 100%; align-self: stretch; min-width: 0; padding: 13px 16px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border: none; border-radius: 10px; cursor: pointer; font-size: 15px; font-weight: 900; transition: all 0.25s ease; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18); }
            .service-entry:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(15, 23, 42, 0.24); background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
            .service-entry:active { transform: translateY(0); }
            
            .back-button { padding: 12px 19px; background: #16a34a; color: white; border: none; border-radius: 7px; cursor: pointer; font-size: 15px; font-weight: 700; transition: all 0.3s; align-self: flex-start; margin-bottom: 20px; }
            .back-button:hover { background: #15803d; }
            
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
            .files-list-header.has-formatting-price,
            .files-list-item.has-formatting-price { min-width: 820px; grid-template-columns: 2fr 0.8fr 0.8fr 1fr 1fr 0.7fr 0.45fr; }
            .files-list-header.has-color-printing-price,
            .files-list-item.has-color-printing-price { min-width: 1320px; grid-template-columns: 2fr 0.65fr 0.7fr 0.65fr 0.9fr 0.8fr 1fr 0.85fr 0.85fr 0.85fr 0.7fr 0.45fr; }
            .files-list { border: 0; background: transparent; overflow: visible; }
            .files-list-header { display: none; }
            .files-list-item,
                .files-list-item.has-price,
                .files-list-item.has-copies-price,
                .files-list-item.has-color-printing-price,
                .files-list-item.has-academic-university,
            .files-list-item.has-formatting-price,
            .files-list-item.has-thesis-project {
                width: 100%;
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
                padding: 10px 0;
                border-bottom: 1px solid #f1f5f9;
                line-height: 1.7;
                font-size: 16px;
            }
            .files-list-item > div:last-child { border-bottom: 0; }
            .files-list-item > div::before {
                content: attr(data-label);
                flex: 0 0 auto;
                color: #64748b;
                font-size: 13px;
                font-weight: 900;
                white-space: nowrap;
            }
            .files-list-item > div:empty { display: none; }
            .file-name-cell {
                display: block !important;
                padding: 0 0 10px !important;
                border-bottom: 1px solid #e2e8f0 !important;
                font-size: 17px;
                text-align: right;
            }
            .file-name-cell::before {
                display: block;
                margin-bottom: 6px;
            }
            .files-list-item:last-child { margin-bottom: 0; }
            .file-name-cell { color: #111827; font-weight: 900; word-break: normal; overflow-wrap: anywhere; line-height: 1.75; }
            .file-pages { color: #475569; font-size: 16px; font-weight: 800; }
            .file-size { color: #6b7280; font-size: 16px; font-weight: 800; }
            .file-price { color: #0f172a; font-size: 16px; font-weight: 900; }
            .file-price-note { display: block; margin-top: 4px; color: #b91c1c; font-size: 12px; font-weight: 700; line-height: 1.5; }
            .binding-select { width: 100%; min-width: 158px; padding: 12px 13px; border: 1px solid #cbd5e1; border-radius: 9px; background: #ffffff; color: #111827; font-size: 14px; font-weight: 800; }
            .binding-select:invalid { color: #6b7280; }
            .binding-select option { padding: 12px 10px; line-height: 2; border-bottom: 1px solid #e5e7eb; background: #ffffff; color: #111827; }
            .binding-select option:checked { background: #e0f2fe; color: #0f172a; font-weight: 900; }
            .university-cell { display: flex; flex-direction: column; gap: 8px; min-width: 190px; }
            .university-input { width: 100%; min-width: 198px; padding: 12px 13px; border: 1px solid #cbd5e1; border-radius: 9px; background: #ffffff; color: #111827; font-size: 14px; font-weight: 800; }
            .university-input:placeholder-shown { color: #6b7280; }
            .university-custom-input { width: 100%; min-width: 198px; padding: 12px 13px; border: 1px solid #94a3b8; border-radius: 9px; background: #f8fafc; color: #111827; font-size: 14px; font-weight: 800; }
            .university-picker-button { width: 100%; margin: 0; padding: 12px 13px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-size: 14px; font-weight: 900; cursor: pointer; }
            .university-dropdown { display: none; }
            .university-dropdown.active { display: block; }
            .university-results { display: block; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; overflow: hidden; max-height: 170px; overflow-y: auto; }
            .university-result { width: 100%; margin: 0; padding: 10px 12px; border: 0; border-bottom: 1px solid #e5e7eb; border-radius: 0; background: #ffffff; color: #111827; text-align: right; font-size: 14px; font-weight: 800; cursor: pointer; }
            .university-result:hover { background: #f8fafc; }
            .university-result:last-child { border-bottom: 0; }
            .copies-stepper { display: inline-grid; grid-template-columns: 44px 78px 44px; gap: 8px; align-items: center; }
            .copies-stepper-button { width: 44px; height: 44px; margin: 0; padding: 0; border: 0; border-radius: 10px; background: #0f172a; color: #ffffff; font-size: 23px; font-weight: 900; line-height: 1; cursor: pointer; }
            .copies-stepper-button:hover { background: #1e293b; }
            .copies-stepper-button:disabled { background: #cbd5e1; color: #64748b; cursor: not-allowed; }
            .copies-input { width: 78px; height: 44px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 10px; background: #ffffff; color: #111827; font-size: 15px; font-weight: 900; text-align: center; }
            .file-remove { cursor: pointer; color: #ef4444; font-size: 15px; font-weight: 800; text-align: center; }
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
            .delivery-notice { margin-top: 10px; padding: 10px 12px; border-radius: 8px; background: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; font-size: 13px; font-weight: 800; line-height: 1.7; }
            .word-usage-notice { margin-top: 10px; padding: 10px 12px; border-radius: 8px; background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; font-size: 13px; font-weight: 800; line-height: 1.7; }
            .service-notice { margin: 14px 0 24px; padding: 14px 16px; border-radius: 14px; background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 100%); color: #0f172a; border: 1px solid #c7ddf5; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08); display: flex; gap: 12px; align-items: flex-start; line-height: 1.8; }
            .service-notice-icon { width: 34px; height: 34px; min-width: 34px; border-radius: 10px; background: #0f172a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18); }
            .service-notice-content { flex: 1; min-width: 0; }
            .service-notice-title { margin: 0 0 2px; color: #0f172a; font-size: 14px; font-weight: 900; }
            .service-notice-text { margin: 0; color: #334155; font-size: 13px; font-weight: 800; }
            #uploadResearch .binding-section { margin-top: 12px; padding: 10px; }
            #uploadResearch .binding-section h3 { margin-bottom: 7px; font-size: 14px; }
            .research-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; align-items: end; }
            .research-field { display: flex; flex-direction: column; gap: 3px; }
            .research-field label { color: #111827; font-size: 11px; font-weight: 900; }
            .research-input { width: 100%; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; color: #111827; font-size: 16px; line-height: 1.1; font-weight: 700; }
            .research-pages-save-row { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: 8px; min-width: 0; }
            .research-pages-save-row .research-field { flex: 1; min-width: 0; flex-direction: row; align-items: center; gap: 7px; }
            .research-pages-save-row .research-field label { flex: 0 1 auto; margin: 0; font-size: 10px; line-height: 1.2; white-space: nowrap; }
            .research-pages-stepper { flex: 0 0 auto; grid-template-columns: 30px 52px 30px; gap: 4px; }
            .research-pages-stepper .copies-stepper-button { width: 30px; height: 30px; border-radius: 7px; font-size: 16px; }
            .research-pages-stepper .copies-input { width: 52px; height: 30px; border-radius: 7px; }
            #researchSaveButton { flex: 0 0 auto; min-height: 30px; margin: 0; padding: 6px 12px; border-radius: 7px; font-size: 11px; }
            .research-delivery-notice { margin: 0 0 6px; padding: 5px 7px; border-radius: 6px; background: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; font-size: 9.5px; font-weight: 800; line-height: 1.35; }
            .research-input:focus { outline: 2px solid rgba(14, 165, 233, 0.18); border-color: #38bdf8; }
            .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
            .english-number-warning.active { display: block; }
            .checkout-row { margin-top: 14px; display: flex; justify-content: flex-end; }
            .checkout-button { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; background: #047857; color: #ffffff; border-radius: 8px; text-decoration: none; font-weight: 800; border: 0; cursor: pointer; }
            .checkout-button:hover { background: #065f46; }
            .checkout-button.disabled { background: #cbd5e1; color: #64748b; pointer-events: none; }

            .page-footer { flex: 0 0 auto; width: 100%; margin-top: auto; background: #0f172a; color: #cbd5e1; padding: 14px 18px; }
            .footer-content { max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 4px; font-size: 12px; line-height: 1.45; }
            @media (max-width: 1180px) {
                .services-screen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .service-card { min-height: 230px; }
            }
            @media (max-width: 900px) {
                .services-screen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .service-card { min-height: 220px; padding: 16px; }
                .service-icon { width: 48px; height: 48px; font-size: 24px; }
                .service-title { font-size: 16px; }
                .service-description { font-size: 13px; line-height: 1.7; }
                .service-entry { padding: 11px 9px; font-size: 13px; }
            }
            @media (max-width: 768px) {
                :root { --sidebar-width: 0px; --page-gap: 10px; }
                body { padding: 0; }
                .services-title-block { padding: 10px 12px; border-radius: 12px; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06); }
                .services-title { font-size: 19px; line-height: 1.35; }
                .page-header { position: sticky; top: 0; width: 100%; min-height: 0; max-height: none; padding: 8px 10px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16); }
                .header-inner { height: auto; display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 8px; }
                .header-inner > div:first-child { display: flex; align-items: center; gap: 8px; min-width: 0; }
                .brand-logo { width: 34px; height: 34px; border-radius: 10px; margin: 0; flex: 0 0 auto; }
                .brand { margin: 0; font-size: 17px; line-height: 1.2; white-space: nowrap; }
                .brand-subtitle { display: none; }
                .header-actions { grid-column: 1 / -1; margin-top: 0; display: none; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
                .header-user { grid-column: 1 / -1; margin: 0; padding: 0; }
                .header-form { margin: 0; }
                .header-user, .header-link { padding: 9px 8px; gap: 5px; }
                .logout-button { padding: 9px 8px; }
                .container { width: calc(100% - 20px); margin: 14px auto 22px; padding: 16px; }
                .page-footer { padding: 8px 10px; }
                .footer-content { padding: 0; gap: 2px; font-size: 10.5px; line-height: 1.35; text-align: center; }
                .upload-content.active { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; column-gap: 8px; row-gap: 10px; }
                .upload-content.active > .back-button { grid-column: 1; grid-row: 1; margin: 0; padding: 9px 12px; font-size: 12px; border-radius: 8px; white-space: nowrap; }
                .upload-content.active > h2 { grid-column: 2; grid-row: 1; margin: 0; text-align: left; font-size: 16px; line-height: 1.35; color: #0f172a; }
                .upload-content.active > :not(.back-button):not(h2) { grid-column: 1 / -1; }
                .upload-content.active > .service-notice { margin: 0 0 4px; padding: 8px 10px; border-radius: 10px; gap: 8px; align-items: center; box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06); }
                .upload-content.active .service-notice-icon { width: 24px; height: 24px; min-width: 24px; border-radius: 7px; font-size: 12px; box-shadow: none; }
                .upload-content.active .service-notice-title { margin: 0; font-size: 12px; line-height: 1.3; }
                .upload-content.active .service-notice-text { font-size: 11px; line-height: 1.55; }
                .upload-section { flex-direction: column; margin-top: 10px; gap: 10px; }
                .upload-box {
                    min-width: 0;
                    width: 100%;
                    padding: 10px;
                    display: grid;
                    grid-template-columns: 78px minmax(0, 1fr) auto;
                    align-items: center;
                    gap: 5px 8px;
                    text-align: start;
                    border-radius: 10px;
                }
                .upload-box .file-icon {
                    grid-column: 1;
                    grid-row: 1;
                    margin: 0;
                    font-size: 24px;
                    line-height: 1;
                    text-align: center;
                }
                .upload-box h3 {
                    grid-column: 1;
                    grid-row: 2;
                    margin: 0;
                    font-size: 10px;
                    line-height: 1.35;
                    text-align: center;
                }
                .upload-box .file-info,
                .upload-box .word-usage-notice,
                .upload-box .binding-required {
                    grid-column: 2;
                    margin: 0;
                    padding: 0;
                    border: 0;
                    background: transparent;
                    font-size: 10.5px;
                    line-height: 1.45;
                }
                .upload-box .upload-button {
                    grid-column: 3;
                    grid-row: 1 / span 2;
                    align-self: center;
                    margin: 0;
                    padding: 8px 10px;
                    border-radius: 8px;
                    font-size: 11px;
                    white-space: nowrap;
                }
                .upload-box .progress-bar,
                .upload-box .error-msg {
                    grid-column: 1 / -1;
                }
                .upload-content.active > div[style*="margin-top: 40px"] {
                    margin-top: -7px !important;
                    margin-bottom: 4px !important;
                }
                .upload-content.active > div[style*="margin-top: 40px"] > h3 {
                    margin-bottom: 0 !important;
                    font-size: 13px;
                    line-height: 1.35;
                }
                .files-list { margin-top: 0; border: 0; background: transparent; overflow: visible; }
                .files-list-header { display: none; }
                .files-list-item,
                .files-list-item.has-price,
                .files-list-item.has-copies-price,
                .files-list-item.has-color-printing-price,
                .files-list-item.has-academic-university,
                .files-list-item.has-formatting-price,
                .files-list-item.has-thesis-project {
                    min-width: 0;
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 6px;
                    padding: 8px;
                    margin-bottom: 8px;
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    background: #ffffff;
                    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
                }
                .files-list-item > div {
                    width: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 5px;
                    min-width: 0;
                    padding: 6px 7px;
                    border: 1px solid #edf2f7;
                    border-radius: 8px;
                    background: #f8fafc;
                    line-height: 1.35;
                    font-size: 11px;
                }
                .files-list-item > div:last-child { border-bottom: 1px solid #edf2f7; }
                .files-list-item > div::before {
                    content: attr(data-label);
                    flex: 0 0 auto;
                    color: #64748b;
                    font-size: 10.5px;
                    font-weight: 900;
                    white-space: nowrap;
                }
                .files-list-item > div:empty { display: none; }
                .file-name-cell {
                    grid-column: 1 / -1;
                    display: flex !important;
                    padding: 7px 8px !important;
                    border: 1px solid #dbeafe !important;
                    border-radius: 9px;
                    background: #eff6ff !important;
                    font-size: 12px;
                    text-align: right;
                    line-height: 1.45;
                }
                .file-name-cell::before {
                    display: inline-flex;
                    margin-bottom: 0;
                }
                .binding-select,
                .copies-input,
                .copies-stepper,
                .university-input,
                .university-custom-input,
                .university-picker-button {
                    width: 100%;
                    min-width: 0;
                }
                .binding-select,
                .university-input,
                .university-custom-input,
                .university-picker-button {
                    padding: 7px 8px;
                    border-radius: 7px;
                    font-size: 10.5px;
                }
                .academic-choice-cell {
                    flex-direction: column;
                    align-items: stretch !important;
                    justify-content: flex-start !important;
                    gap: 3px !important;
                }
                .academic-choice-cell::before { font-size: 9.5px !important; }
                .academic-choice-select {
                    padding: 5px 4px;
                    font-size: 9px;
                    line-height: 1.2;
                }
                .copies-stepper { grid-template-columns: 28px minmax(34px, 1fr) 28px; gap: 4px; }
                .copies-stepper-button { width: 28px; height: 28px; border-radius: 7px; font-size: 15px; }
                .copies-input { width: 100%; height: 28px; padding: 4px 5px; border-radius: 7px; font-size: 11px; }
                .research-pages-stepper .copies-stepper-button { width: 28px; height: 28px; border-radius: 7px; font-size: 15px; }
                .research-pages-stepper .copies-input { width: 100%; height: 28px; border-radius: 7px; }
                .research-pages-stepper { width: auto; min-width: 0; flex: 0 0 98px; grid-template-columns: 28px 34px 28px; }
                .research-pages-save-row { gap: 5px; }
                .research-pages-save-row .research-field { gap: 4px; }
                .research-pages-save-row .research-field label { font-size: 9px; white-space: normal; }
                #researchSaveButton { padding: 5px 8px; font-size: 10px; }
                .academic-copies-cell .copies-stepper { grid-template-columns: 22px minmax(24px, 1fr) 22px; gap: 2px; }
                .academic-copies-cell .copies-stepper-button { width: 22px; height: 26px; border-radius: 6px; font-size: 13px; }
                .academic-copies-cell .copies-input { height: 26px; padding: 3px; font-size: 10px; }
                .file-pages,
                .file-size,
                .file-price,
                .file-remove {
                    font-size: 11px;
                    line-height: 1.35;
                }
                .file-price-note { font-size: 9.5px; line-height: 1.35; }
                .binding-section {
                    margin-top: 4px;
                    padding: 8px;
                    border-radius: 10px;
                }
                .binding-section h3 {
                    margin-bottom: 4px;
                    font-size: 13px;
                    line-height: 1.3;
                }
                .pricing-summary {
                    margin-top: 0;
                    padding: 7px;
                    border-radius: 8px;
                    line-height: 1.35;
                }
                .checkout-summary-line {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 5px;
                    align-items: stretch;
                }
                .checkout-summary-metric,
                .checkout-row {
                    min-width: 0;
                    margin: 0;
                    padding: 6px 7px;
                    border: 1px solid #edf2f7;
                    border-radius: 8px;
                    background: #f8fafc;
                    font-size: 10.5px;
                    font-weight: 900;
                    line-height: 1.25;
                    text-align: center;
                }
                .checkout-row {
                    display: flex;
                    justify-content: stretch;
                    padding: 0;
                    background: transparent;
                    border: 0;
                }
                .checkout-button {
                    width: 100%;
                    min-height: 100%;
                    padding: 7px 6px;
                    border-radius: 8px;
                    font-size: 10.5px;
                    line-height: 1.2;
                }
                .research-form-grid { grid-template-columns: 1fr; }
            }
            @media (max-width: 420px) {
                :root { --sidebar-width: 0px; --page-gap: 8px; }
                .brand { font-size: 16px; }
                .brand-subtitle { font-size: 10px; }
                .header-actions { font-size: 11px; }
                .header-user, .header-link { padding: 8px 6px; }
                .container { padding: 14px; border-radius: 14px; }
                .services-screen { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
                .service-card { min-width: 0; min-height: 0; gap: 7px; padding: 10px; border-radius: 12px; }
                .service-icon { width: 36px; height: 36px; border-radius: 10px; font-size: 18px; }
                .service-title { font-size: 11px; line-height: 1.4; overflow-wrap: normal; word-break: normal; }
                .service-description { font-size: 9.5px; line-height: 1.45; }
                .service-entry { margin-top: auto; padding: 8px 5px; border-radius: 8px; font-size: 10px; line-height: 1.25; }
                .academic-choice-select { padding-inline: 2px; font-size: 8.5px; }
                .files-list-item,
                .files-list-item.has-price,
                .files-list-item.has-copies-price,
                .files-list-item.has-color-printing-price,
                .files-list-item.has-academic-university,
                .files-list-item.has-formatting-price,
                .files-list-item.has-thesis-project {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (min-width: 1100px) {
                .service-description { font-size: 16px; }
                .service-entry { font-size: 16px; }
                .files-list-header,
                .files-list-item { font-size: 15px; }
                .upload-box .file-info,
                .upload-box.error .error-msg,
                .binding-required { font-size: 14px; }
                .research-field label { font-size: 13px; }
                .research-pages-save-row .research-field label { font-size: 12px; }
                #researchSaveButton { font-size: 13px; }
                .research-delivery-notice { font-size: 11.5px; }
                .footer-content { font-size: 14px; }
            }
            @media (min-width: 769px) {
                .services-screen { gap: 9px; }
                .services-title-block { padding: 12px 15px; border-radius: 13px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
                .services-title { font-size: 25px; line-height: 1.3; }
                .services-subtitle { margin-top: 3px; font-size: 13px; line-height: 1.55; }
                .service-card { min-height: 180px; gap: 6px; padding: 10px 11px; border-radius: 12px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
                .service-icon { width: 40px; height: 40px; border-radius: 10px; font-size: 20px; }
                .service-title { font-size: 16px; line-height: 1.4; }
                .service-description { font-size: 13px; line-height: 1.55; }
                .service-entry { padding: 8px 9px; border-radius: 8px; font-size: 13px; }
            }
        </style>
    </head>
    <body class="customer-app-page">
        <header class="page-header">
            <div class="header-inner">
                <div class="header-brand">
                    <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                    <div class="brand">الورّاق</div>
                    <p class="brand-subtitle">خدمات الطباعة والتجليد</p>
                </div>
                <div class="header-identity">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->role === 'admin' ? 'المدير' : 'العميل' }}</small>
                </div>
                <div class="header-actions">
                    @php
                        $hasCustomerOrderNotice = \App\Models\Order::query()
                            ->where('user_id', auth()->id())
                            ->whereNull('customer_notification_seen_at')
                            ->whereHas('deliveredFiles', fn ($query) => $query->whereNull('customer_downloaded_at'))
                            ->exists();
                    @endphp
                    <a class="header-link" href="{{ route('home') }}">🏠 الرئيسية</a>
                    <a class="header-link" href="{{ route('orders.index') }}">
                        🧾 طلباتي
                        @if ($hasCustomerOrderNotice)
                            <span class="customer-notice-dot" data-customer-orders-dot aria-label="تحديث جديد في طلباتك"></span>
                        @endif
                    </a>
                    <a class="header-link" href="{{ route('cart.index') }}">🛒 السلة</a>
                    <a class="header-link settings-link" href="{{ route('account.settings') }}">⚙️ الإعدادات</a>
                    @if (auth()->user()->role === 'admin')
                        <a class="header-link admin-header-link" href="{{ route('admin.orders') }}">لوحة المدير</a>
                    @endif
                    <form class="header-form" method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">🚪 خروج</button>
                    </form>
                    @include('shared.language-switcher')
                </div>
            </div>
        </header>

        <main class="container" id="services">
            <!-- Services Selection Screen -->
            <div id="servicesScreen" class="services-screen">
                <div class="services-title-block">
                    <h2 class="services-title">اختر الخدمة المطلوبة</h2>
                </div>

                <article class="service-card">
                    <div class="service-icon">📝</div>
                    <h3 class="service-title">طباعة المذكرات وملفات ال PDF</h3>
                    <p class="service-description">طباعة أبيض وأسود بدون ألوان للمذكرات وملفات ال PDF بجميع أحجامها وتغليفها.</p>
                    <button class="service-entry" type="button" onclick="selectService('notes')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">🎨</div>
                    <h3 class="service-title">طباعة الملفات بالألوان</h3>
                    <p class="service-description">طباعة ملفات PDF ملونة مع اختيار حجم الصفحة وعدد النسخ والتغليف.</p>
                    <button class="service-entry" type="button" onclick="selectService('color_printing')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">📘</div>
                    <h3 class="service-title">طباعة وتجليد كتب كعب جلد طبيعي</h3>
                    <p class="service-description">طباعة ملفات PDF والكتب بجميع أحجامها والتغليف وتجليد كعب جلد طبيعي.</p>
                    <button class="service-entry" type="button" onclick="selectService('books')">الدخول للخدمة</button>
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
                    <p class="service-description">تجهيز ملفات الدكتوراه للطباعة والتجليد مع عرض كامل للتكاليف قبل الإضافة للسلة.</p>
                    <button class="service-entry" type="button" onclick="selectService('phd')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">✍️</div>
                    <h3 class="service-title">تنسيق وتدقيق الرسائل الجامعية</h3>
                    <p class="service-description">رفع ملف Word فقط واحتساب سعر التنسيق تلقائيًا حسب عدد الصفحات.</p>
                    <button class="service-entry" type="button" onclick="selectService('formatting')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">📑</div>
                    <h3 class="service-title">إنشاء بحوث جامعية وأكاديمية ودراسية</h3>
                    <p class="service-description">اكتب اسم البحث وعدد الصفحات المطلوبة، ويتم احتساب سعر الخدمة تلقائيًا.</p>
                    <button class="service-entry" type="button" onclick="selectService('research')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">✏️</div>
                    <h3 class="service-title">القرطاسية</h3>
                    <p class="service-description">تصفح منتجات القرطاسية وابحث عنها وأضف ما تحتاجه إلى السلة.</p>
                    <button class="service-entry" type="button" onclick="window.location.href='{{ route('stationery.index') }}'">الدخول للمتجر</button>
                </article>
            </div>

            <!-- Upload Section for Notes -->
            <div id="uploadNotes" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات مذكرات</h2>
                <div class="service-notice">
                    <span class="service-notice-icon">i</span>
                    <div class="service-notice-content">
                        <p class="service-notice-title">معلومة مهمة قبل رفع الملفات</p>
                        <p class="service-notice-text">يمكنك رفع أكثر من ملف داخل الطلب نفسه، وإذا ظهر خيار عدد النسخ يمكنك تحديد عدد مرات تنفيذ العمل على كل ملف.</p>
                    </div>
                </div>
                
                <div class="upload-section">
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

            <!-- Upload Section for Books -->
            <div id="uploadBooks" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات الكتب</h2>
                <div class="service-notice">
                    <span class="service-notice-icon">i</span>
                    <div class="service-notice-content">
                        <p class="service-notice-title">معلومة مهمة قبل رفع الملفات</p>
                        <p class="service-notice-text">يمكنك رفع أكثر من ملف داخل الطلب نفسه، وإذا ظهر خيار عدد النسخ يمكنك تحديد عدد مرات تنفيذ العمل على كل ملف.</p>
                    </div>
                </div>

                <div class="upload-section">
                    <div class="upload-box" id="booksPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="booksPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <button class="upload-button" id="booksPdfUploadBtn" onclick="document.getElementById('booksPdfFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="booksPdfProgress"><div class="progress-bar-fill"></div></div>
                        <div id="booksPdfError" class="error-msg" style="display: none;"></div>
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
                        <div id="booksPdfFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي الكتب</h3>
                    <div id="booksPricingSummary" class="pricing-summary empty">اختر نوع التغليف لكل ملف لعرض السعر.</div>
                </div>
            </div>

            <!-- Upload Section for Color Printing -->
            <div id="uploadColorPrinting" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات الطباعة بالألوان</h2>
                <div class="service-notice">
                    <span class="service-notice-icon">i</span>
                    <div class="service-notice-content">
                        <p class="service-notice-title">معلومة مهمة قبل رفع الملفات</p>
                        <p class="service-notice-text">يمكنك رفع أكثر من ملف داخل الطلب نفسه، وإذا ظهر خيار عدد النسخ يمكنك تحديد عدد مرات تنفيذ العمل على كل ملف.</p>
                    </div>
                </div>

                <div class="upload-section">
                    <div class="upload-box" id="colorPrintingPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="colorPrintingPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="file-info">يدعم A4 و A3 و A5 و B5</p>
                        <button class="upload-button" id="colorPrintingPdfUploadBtn" onclick="document.getElementById('colorPrintingPdfFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="colorPrintingPdfProgress"><div class="progress-bar-fill"></div></div>
                        <div id="colorPrintingPdfError" class="error-msg" style="display: none;"></div>
                    </div>
                </div>

                <div style="margin-top: 40px; margin-bottom: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">🎨 ملفات PDF المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-color-printing-price">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>النسخ</div>
                            <div>نوع الطباعة</div>
                            <div>حجم الصفحة</div>
                            <div>التغليف</div>
                            <div>سعر الطباعة</div>
                            <div>سعر التغليف</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="colorPrintingPdfFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي طباعة الملفات بالألوان</h3>
                    <div id="color_printingPricingSummary" class="pricing-summary empty">اختر التغليف لكل ملف لعرض السعر.</div>
                </div>
            </div>

            <!-- Upload Section for Thesis -->
            <div id="uploadThesis" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات رسالة ماجستير أو بحث</h2>
                <div class="service-notice">
                    <span class="service-notice-icon">i</span>
                    <div class="service-notice-content">
                        <p class="service-notice-title">معلومة مهمة قبل رفع الملفات</p>
                        <p class="service-notice-text">يمكنك رفع أكثر من ملف داخل الطلب نفسه، وإذا ظهر خيار عدد النسخ يمكنك تحديد عدد مرات تنفيذ العمل على كل ملف.</p>
                    </div>
                </div>
                
                <div class="upload-section">
                    <div class="upload-box" id="thesisWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملفات Word</h3>
                        <input type="file" id="thesisWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <p class="word-usage-notice">ملف Word للعرض أو لاستخدامه في عمل الكعب والكليشة فقط، وليس للطباعه.</p>
                        <button class="upload-button" id="thesisWordUploadBtn" onclick="document.getElementById('thesisWordFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="thesisWordProgress"><div class="progress-bar-fill"></div></div>
                        <div id="thesisWordError" class="error-msg" style="display: none;"></div>
                    </div>

                    <div class="upload-box" id="thesisPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="thesisPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="binding-required">ملف PDF إجباري للإضافة إلى السلة.</p>
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
                <div class="service-notice">
                    <span class="service-notice-icon">i</span>
                    <div class="service-notice-content">
                        <p class="service-notice-title">معلومة مهمة قبل رفع الملفات</p>
                        <p class="service-notice-text">يمكنك رفع أكثر من ملف داخل الطلب نفسه، وإذا ظهر خيار عدد النسخ يمكنك تحديد عدد مرات تنفيذ العمل على كل ملف.</p>
                    </div>
                </div>
                
                <div class="upload-section">
                    <div class="upload-box" id="phdWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملفات Word</h3>
                        <input type="file" id="phdWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">حجم الملف: بدون حد أقصى</p>
                        <p class="word-usage-notice">ملف Word للعرض أو لاستخدامه في عمل الكعب والكليشة فقط، وليس للطباعه.</p>
                        <button class="upload-button" id="phdWordUploadBtn" onclick="document.getElementById('phdWordFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="phdWordProgress"><div class="progress-bar-fill"></div></div>
                        <div id="phdWordError" class="error-msg" style="display: none;"></div>
                    </div>

                    <div class="upload-box" id="phdPdfBox">
                        <div class="file-icon">📕</div>
                        <h3>تحميل ملفات PDF</h3>
                        <input type="file" id="phdPdfFile" accept=".pdf" multiple />
                        <p class="file-info">صيغ مدعومة: .pdf</p>
                        <p class="binding-required">ملف PDF إجباري للإضافة إلى السلة.</p>
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

            <!-- Upload Section for Formatting -->
            <div id="uploadFormatting" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تنسيق وتدقيق الرسائل الجامعية</h2>
                <div class="service-notice">
                    <span class="service-notice-icon">i</span>
                    <div class="service-notice-content">
                        <p class="service-notice-title">معلومة مهمة قبل رفع الملفات</p>
                        <p class="service-notice-text">يمكنك رفع أكثر من ملف داخل الطلب نفسه، وإذا ظهر خيار عدد النسخ يمكنك تحديد عدد مرات تنفيذ العمل على كل ملف.</p>
                    </div>
                </div>

                <div class="upload-section">
                    <div class="upload-box" id="formattingWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملف Word</h3>
                        <input type="file" id="formattingWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">سعر التنسيق: {{ rtrim(rtrim(number_format($servicePricing['formatting_page_price'], 2, '.', ''), '0'), '.') }} ريال لكل صفحة</p>
                        <button class="upload-button" id="formattingWordUploadBtn" onclick="document.getElementById('formattingWordFile').click()">اختر ملفات</button>
                        <div class="progress-bar" id="formattingWordProgress"><div class="progress-bar-fill"></div></div>
                        <div id="formattingWordError" class="error-msg" style="display: none;"></div>
                    </div>
                </div>

                <div style="margin-top: 40px; margin-bottom: 40px;">
                    <h3 style="margin-bottom: 16px; color: #111827;">📄 ملفات Word المحملة</h3>
                    <div class="files-list">
                        <div class="files-list-header has-formatting-price">
                            <div>اسم الملف</div>
                            <div>الصفحات</div>
                            <div>الحجم</div>
                            <div>سعر التنسيق</div>
                            <div>الإجمالي</div>
                            <div>الحالة</div>
                            <div></div>
                        </div>
                        <div id="formattingWordFilesList" class="empty-message">لم يتم تحميل أي ملفات</div>
                    </div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي تنسيق وتدقيق الرسائل الجامعية</h3>
                    <div id="formattingPricingSummary" class="pricing-summary empty">ارفع ملفات Word لعرض الإجمالي.</div>
                </div>
            </div>

            <!-- Research Creation Service -->
            <div id="uploadResearch" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>إنشاء بحوث جامعية وأكاديمية ودراسية</h2>

                <div class="binding-section">
                    <h3>تفاصيل البحث</h3>
                    <div class="research-form-grid">
                        <div class="research-field">
                            <label for="researchTitle">عنوان البحث المطلوب</label>
                            <input class="research-input" id="researchTitle" type="text" maxlength="255" placeholder="مثال: أثر التقنية في التعليم" oninput="updateResearchPricingSummary()" />
                        </div>
                        <div class="research-field">
                            <label for="researchStudentName">اسم الطالب</label>
                            <input class="research-input" id="researchStudentName" type="text" maxlength="255" autocomplete="off" placeholder="اكتب اسم الطالب" oninput="updateResearchPricingSummary()" />
                        </div>
                        <div class="research-field">
                            <label for="researchInstructorName">اسم الدكتور أو الأستاذ</label>
                            <input class="research-input" id="researchInstructorName" type="text" maxlength="255" autocomplete="off" placeholder="اكتب اسم الدكتور أو الأستاذ" oninput="updateResearchPricingSummary()" />
                        </div>
                        <div class="research-field">
                            <label for="researchInstitutionName">اسم الجامعة أو المدرسة أو المعهد</label>
                            <input class="research-input" id="researchInstitutionName" type="text" maxlength="255" autocomplete="off" placeholder="اكتب اسم الجهة التعليمية" oninput="updateResearchPricingSummary()" />
                        </div>
                        <div class="research-pages-save-row">
                            <div class="research-field">
                                <label for="researchPages">عدد صفحات البحث المطلوبة</label>
                                <div class="copies-stepper research-pages-stepper">
                                    <button class="copies-stepper-button" type="button" onclick="changeResearchPages(-1)">-</button>
                                    <input class="copies-input" id="researchPages" type="number" min="1" max="9999" step="1" value="1" readonly aria-label="عدد صفحات البحث المطلوبة" />
                                    <button class="copies-stepper-button" type="button" onclick="changeResearchPages(1)">+</button>
                                </div>
                            </div>
                            <button class="upload-button" id="researchSaveButton" type="button" onclick="saveResearchRequest()">حفظ الطلب</button>
                        </div>
                    </div>
                    <div id="researchError" class="error-msg" style="display: none;"></div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي إنشاء البحوث</h3>
                    <div class="research-delivery-notice">سيتم إرسال الملف بعد الانتهاء داخل التطبيق في صفحة طلباتي خلال ٢٤ ساعة إلى ٤٨ ساعة إن شاء الله.</div>
                    <div id="researchPricingSummary" class="pricing-summary empty">اكتب اسم البحث وعدد الصفحات لعرض الإجمالي.</div>
                </div>
            </div>
        </main>

        <datalist id="saudiUniversitiesList"></datalist>

        <script>
            const servicePricing = @json($servicePricing);
            // Store uploaded files for each service
            const uploadedFiles = {
                notes: { word: [], pdf: [] },
                books: { word: [], pdf: [] },
                color_printing: { word: [], pdf: [] },
                thesis: { word: [], pdf: [] },
                phd: { word: [], pdf: [] },
                formatting: { word: [], pdf: [] },
                research: { word: [], pdf: [] }
            };
            const currentOrders = {
                notes: null,
                books: null,
                color_printing: null,
                thesis: null,
                phd: null,
                formatting: null,
                research: null
            };
            const savedResearchRequest = {
                title: '',
                studentName: '',
                instructorName: '',
                institutionName: '',
                pages: 0
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
            const academicCoverColors = {
                black: 'أسود',
                light_blue: 'أزرق فاتح',
                navy: 'أزرق كحلي',
                dark_green: 'الأخضر الداكن',
                light_green: 'الأخضر الفاتح',
                burgundy: 'العنابي',
                beige: 'البيج',
                white: 'الأبيض'
            };
            const academicWritingColors = {
                gold: 'كتابة باللون الذهبي',
                black: 'كتابة باللون الأسود'
            };
            const bookLeatherColors = {
                black: 'جلد أسود',
                green: 'جلد أخضر',
                red: 'جلد أحمر',
                blue: 'جلد أزرق',
                beige: 'جلد بيج',
                brown: 'جلد بني'
            };
            const blackWritingAllowedCovers = ['beige', 'light_blue', 'light_green', 'white'];

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
                notesPdf: { inputId: 'notesPdfFile', boxId: 'notesPdfBox', progressId: 'notesPdfProgress', errorId: 'notesPdfError', listId: 'notesPdfFilesList', service: 'notes', type: 'pdf' },
                booksPdf: { inputId: 'booksPdfFile', boxId: 'booksPdfBox', progressId: 'booksPdfProgress', errorId: 'booksPdfError', listId: 'booksPdfFilesList', service: 'books', type: 'pdf' },
                colorPrintingPdf: { inputId: 'colorPrintingPdfFile', boxId: 'colorPrintingPdfBox', progressId: 'colorPrintingPdfProgress', errorId: 'colorPrintingPdfError', listId: 'colorPrintingPdfFilesList', service: 'color_printing', type: 'pdf' },
                thesisWord: { inputId: 'thesisWordFile', boxId: 'thesisWordBox', progressId: 'thesisWordProgress', errorId: 'thesisWordError', listId: 'thesisWordFilesList', service: 'thesis', type: 'word' },
                thesisPdf: { inputId: 'thesisPdfFile', boxId: 'thesisPdfBox', progressId: 'thesisPdfProgress', errorId: 'thesisPdfError', listId: 'thesisPdfFilesList', service: 'thesis', type: 'pdf' },
                phdWord: { inputId: 'phdWordFile', boxId: 'phdWordBox', progressId: 'phdWordProgress', errorId: 'phdWordError', listId: 'phdWordFilesList', service: 'phd', type: 'word' },
                phdPdf: { inputId: 'phdPdfFile', boxId: 'phdPdfBox', progressId: 'phdPdfProgress', errorId: 'phdPdfError', listId: 'phdPdfFilesList', service: 'phd', type: 'pdf' },
                formattingWord: { inputId: 'formattingWordFile', boxId: 'formattingWordBox', progressId: 'formattingWordProgress', errorId: 'formattingWordError', listId: 'formattingWordFilesList', service: 'formatting', type: 'word' }
            };

            const fileTypes = {
                word: { types: ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], extensions: ['.doc', '.docx'] },
                pdf: { types: ['application/pdf'], extensions: ['.pdf'] }
            };

            function getConfigKey(service, type) {
                return Object.keys(fileConfigs).find(key => fileConfigs[key].service === service && fileConfigs[key].type === type);
            }

            function selectService(service) {
                initializeSaudiUniversitiesList();
                document.body.classList.add('customer-service-view');
                const uploadIds = {
                    color_printing: 'uploadColorPrinting'
                };
                const serviceUrl = new URL(window.location.href);
                serviceUrl.searchParams.set('service', service);
                window.history.replaceState({}, '', serviceUrl);
                document.getElementById('servicesScreen').style.display = 'none';
                document.getElementById(uploadIds[service] || ('upload' + service.charAt(0).toUpperCase() + service.slice(1))).classList.add('active');
                initializeService(service);
            }

            function backToServices() {
                document.body.classList.remove('customer-service-view');
                const homeUrl = new URL(window.location.href);
                homeUrl.searchParams.delete('service');
                homeUrl.searchParams.delete('order');
                window.history.replaceState({}, '', homeUrl);
                document.getElementById('servicesScreen').style.display = '';
                document.getElementById('uploadNotes').classList.remove('active');
                document.getElementById('uploadBooks').classList.remove('active');
                document.getElementById('uploadColorPrinting').classList.remove('active');
                document.getElementById('uploadThesis').classList.remove('active');
                document.getElementById('uploadPhd').classList.remove('active');
                document.getElementById('uploadFormatting').classList.remove('active');
                document.getElementById('uploadResearch').classList.remove('active');
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

            function normalizeDigits(value) {
                return String(value ?? '').replace(/[٠-٩۰-۹]/g, (digit) => ({
                    '٠': '0',
                    '١': '1',
                    '٢': '2',
                    '٣': '3',
                    '٤': '4',
                    '٥': '5',
                    '٦': '6',
                    '٧': '7',
                    '٨': '8',
                    '٩': '9',
                    '۰': '0',
                    '۱': '1',
                    '۲': '2',
                    '۳': '3',
                    '۴': '4',
                    '۵': '5',
                    '۶': '6',
                    '۷': '7',
                    '۸': '8',
                    '۹': '9',
                })[digit] || digit);
            }

            function numericValue(value) {
                return Number(String(value ?? '').trim());
            }

            function formatMoney(value) {
                const amount = Number(value || 0);
                return Number.isInteger(amount) ? String(amount) : amount.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
            }

            function bindEnglishNumberWarnings(root = document) {
                const selector = '#researchPages, .copies-input, .cd-copies-input';
                root.querySelectorAll(selector).forEach((input) => {
                    if (input.dataset.englishNumberBound === 'true') return;

                    const showWarning = () => {
                        input.value = normalizeDigits(input.value);

                        let warning = input.nextElementSibling;
                        if (!warning || !warning.classList.contains('english-number-warning')) {
                            warning = document.createElement('div');
                            warning.className = 'english-number-warning';
                            warning.textContent = 'تنبيه: لا يقبل هذا الحقل إلا الأرقام الإنجليزية فقط 0-9.';
                            input.insertAdjacentElement('afterend', warning);
                        }

                        const invalid = input.value !== '' && !/^[0-9]+$/.test(input.value);
                        warning.classList.toggle('active', invalid);
                        input.setCustomValidity(invalid ? 'استخدم الأرقام الإنجليزية فقط 0-9.' : '');
                    };

                    input.addEventListener('input', showWarning);
                    showWarning();
                    input.dataset.englishNumberBound = 'true';
                });
            }

            function calculateNotesFilePrice(pages, binding, paperColor = 'white', service = 'notes', pageSize = 'A4', copies = 1) {
                if (service === 'color_printing') {
                    return calculateColorPrintingFilePrice(pages, binding, pageSize, copies, 'one_side');
                }

                const copyCount = Math.max(1, numericValue(copies) || 1);
                const printPages = Math.max(1, numericValue(pages) || 1) * copyCount;

                if (service === 'books') {
                    const printPrice = paperColor === 'yellow'
                        ? Math.ceil(printPages / servicePricing.books_yellow_pages) * servicePricing.books_yellow_group_price
                        : Math.ceil(printPages / servicePricing.books_white_pages) * servicePricing.books_white_group_price;
                    const bindingPrice = (pageSize === 'A4' ? servicePricing.books_binding_a4 : servicePricing.books_binding_small) * copyCount;

                    return {
                        printPrice,
                        bindingPrice,
                        total: printPrice + bindingPrice,
                        note: ''
                    };
                }

                const printPrice = paperColor === 'yellow'
                    ? Math.ceil(printPages / servicePricing.notes_yellow_pages) * servicePricing.notes_yellow_group_price
                    : Math.ceil(printPages / servicePricing.notes_white_pages) * servicePricing.notes_white_group_price;
                const bindingPrice = calculateNotesBindingPrice(pages, binding) * copyCount;
                const note = binding === 'wire' && pages > 600 ? 'الملف لازم يتقسم على ملفين' : '';


                return {
                    printPrice,
                    bindingPrice,
                    total: printPrice + bindingPrice,
                    note
                };
            }

            function calculateColorPrintingFilePrice(pages, binding, pageSize = 'A4', copies = 1, printSides = 'one_side') {
                const sheetCount = Math.max(1, numericValue(pages) || 1) * Math.max(1, numericValue(copies) || 1);
                const unitPrice = pageSize === 'A3'
                    ? (sheetCount <= 5 ? servicePricing.color_a3_first_5 : (sheetCount <= 10 ? servicePricing.color_a3_to_10 : servicePricing.color_a3_over_10))
                    : (sheetCount <= 5 ? servicePricing.color_a4_first_5 : (sheetCount <= 10 ? servicePricing.color_a4_to_10 : servicePricing.color_a4_over_10));
                const printPrice = sheetCount * unitPrice;
                const thermalBindingUnits = printSides === 'two_sides' ? Math.ceil(sheetCount / 2) : sheetCount;

                const bindingPrice = binding === 'thermal'
                    ? thermalBindingUnits * (pageSize === 'A3' ? servicePricing.thermal_a3_sheet : servicePricing.thermal_a4_sheet)
                    : calculateNotesBindingPrice(pages, binding);
                const note = binding === 'thermal'
                    ? (printSides === 'two_sides'
                        ? 'تنبيه: عند اختيار وجهين يتم حساب كل ورقتين بقيمة ورقة واحدة في التغليف الحراري.'
                        : 'تنبيه: التغليف الحراري يحسب على كل ورقة لحالها.')
                    : '';

                return {
                    printPrice,
                    bindingPrice,
                    total: printPrice + bindingPrice,
                    note
                };
            }

            function calculateNotesBindingPrice(pages, binding) {
                const pageCount = Math.max(1, numericValue(pages) || 1);

                if (binding === 'normal') {
                    return servicePricing.notes_binding_normal;
                }

                if (binding === 'wire') {
                    if (pageCount < 100) {
                        return servicePricing.notes_binding_wire_under_100;
                    }

                    if (pageCount < 300) {
                        return servicePricing.notes_binding_wire_under_300;
                    }

                    if (pageCount <= 600) {
                        return servicePricing.notes_binding_wire_up_to_600;
                    }

                    return servicePricing.notes_binding_wire_over_600;
                }

                return 0;
            }

            function calculatePrintProductPrintTotal(files, service) {
                const totalsByPaper = files.reduce((sum, fileData) => {
                    const paperColor = fileData.paperColor === 'yellow' ? 'yellow' : 'white';
                    sum[paperColor] += (numericValue(fileData.pages) || 0) * Math.max(1, numericValue(fileData.copies) || 1);
                    return sum;
                }, { white: 0, yellow: 0 });
                const whiteDivisor = service === 'notes' ? servicePricing.notes_white_pages : servicePricing.books_white_pages;
                const whiteGroupPrice = service === 'notes' ? servicePricing.notes_white_group_price : servicePricing.books_white_group_price;
                const whiteTotal = Math.ceil(totalsByPaper.white / whiteDivisor) * whiteGroupPrice;
                const yellowDivisor = service === 'books' ? servicePricing.books_yellow_pages : servicePricing.notes_yellow_pages;
                const yellowGroupPrice = service === 'books' ? servicePricing.books_yellow_group_price : servicePricing.notes_yellow_group_price;
                const yellowTotal = Math.ceil(totalsByPaper.yellow / yellowDivisor) * yellowGroupPrice;

                return whiteTotal + yellowTotal;
            }

            function getPrintPrice(pages) {
                return Math.ceil(pages / servicePricing.academic_print_pages) * servicePricing.academic_print_group_price;
            }

            function canUseAcademicWritingColor(coverColor, writingColor) {
                return writingColor !== 'black' || blackWritingAllowedCovers.includes(coverColor);
            }

            function calculateAcademicFilePrice(service, pages, copies, writingColor = '', cdType = 'none', cdCopies = 0) {
                const copyCount = Math.max(1, numericValue(copies) || 1);
                const printPrice = getPrintPrice(pages) * copyCount;
                const cdCount = cdType === 'none' ? 0 : Math.max(1, numericValue(cdCopies) || 1);
                const cdPrice = cdType === 'printed'
                    ? cdCount * servicePricing.academic_cd_printed
                    : (cdType === 'plain' ? cdCount * servicePricing.academic_cd_plain : 0);
                if (!writingColor) {
                    return {
                        printPrice,
                        bindingPrice: 0,
                        cdPrice,
                        total: printPrice + cdPrice
                    };
                }

                const bindingSinglePrice = writingColor === 'gold' ? servicePricing.academic_gold_single : servicePricing.academic_black_single;
                const bindingMultiPrice = writingColor === 'gold' ? servicePricing.academic_gold_multiple : servicePricing.academic_black_multiple;
                const bindingPrice = copyCount === 1 ? bindingSinglePrice : bindingMultiPrice * copyCount;

                return {
                    printPrice,
                    bindingPrice,
                    cdPrice,
                    total: printPrice + bindingPrice + cdPrice
                };
            }

            function calculateFormattingFilePrice(pages) {
                const formattingPrice = (numericValue(pages) || 1) * servicePricing.formatting_page_price;

                return {
                    printPrice: 0,
                    bindingPrice: formattingPrice,
                    total: formattingPrice
                };
            }

            function calculateResearchPrice(pages) {
                const researchPrice = Math.max(1, numericValue(pages) || 1) * servicePricing.research_page_price;

                return {
                    printPrice: 0,
                    bindingPrice: researchPrice,
                    total: researchPrice
                };
            }

            function getAllNotesFiles() {
                return uploadedFiles.notes.pdf;
            }

            function getAllBookFiles() {
                return uploadedFiles.books.pdf;
            }

            function getAllColorPrintingFiles() {
                return uploadedFiles.color_printing.pdf;
            }

            function getAllServiceFiles(service) {
                return [
                    ...uploadedFiles[service].word,
                    ...uploadedFiles[service].pdf
                ];
            }

            function getAcademicPrintableFiles(service) {
                return uploadedFiles[service].pdf;
            }

            function renderCheckoutSummary(summary, service, message, totals = null, canCheckout = false) {
                const orderId = currentOrders[service];
                summary.classList.toggle('empty', !canCheckout);

                if (!canCheckout || !orderId || !totals) {
                    summary.innerHTML = `
                        <div>${message}</div>
                        <div class="checkout-row">
                            <span class="checkout-button disabled">أكمل بيانات الطلب</span>
                        </div>
                    `;
                    return;
                }

                const noPrintServiceLabels = {
                    formatting: 'سعر التنسيق',
                    research: 'سعر إنشاء البحوث'
                };
                const productBindingLabel = service === 'books'
                    ? 'سعر التجليد'
                    : (['notes', 'color_printing'].includes(service) ? 'سعر التغليف' : (noPrintServiceLabels[service] || 'سعر التجليد'));
                const cdMetric = ['thesis', 'phd'].includes(service)
                    ? `<span class="checkout-summary-metric">سعر CD: ${formatMoney(totals.cd || 0)} ريال</span>`
                    : '';

                const totalsHtml = noPrintServiceLabels[service]
                    ? `
                        <span class="checkout-summary-metric">${noPrintServiceLabels[service]}: ${formatMoney(totals.binding)} ريال</span>
                        <span class="checkout-summary-metric">الإجمالي: ${formatMoney(totals.total)} ريال</span>
                    `
                    : `
                        <span class="checkout-summary-metric">سعر الطباعة: ${formatMoney(totals.print)} ريال</span>
                        <span class="checkout-summary-metric">${productBindingLabel}: ${formatMoney(totals.binding)} ريال</span>
                        ${cdMetric}
                        <span class="checkout-summary-metric">الإجمالي: ${formatMoney(totals.total)} ريال</span>
                    `;
                const deliveryNoticeMessages = {
                    formatting: 'سيتم إرسال الملف بعد الانتهاء داخل التطبيق في صفحة طلباتي فور الانتهاء من التنسيق إن شاء الله.'
                };
                const deliveryNotice = deliveryNoticeMessages[service]
                    ? `<div class="delivery-notice">${deliveryNoticeMessages[service]}</div>`
                    : '';

                summary.innerHTML = `
                    <div class="checkout-summary-line">
                        ${totalsHtml}
                        <div class="checkout-row">
                            <a class="checkout-button" href="/cart">الانتقال للسلة</a>
                        </div>
                    </div>
                    ${deliveryNotice}
                `;
            }

            function updateNotesPricingSummary() {
                updatePrintProductPricingSummary('notes');
            }

            function updateBooksPricingSummary() {
                updatePrintProductPricingSummary('books');
            }

            function updatePrintProductPricingSummary(service) {
                const summary = document.getElementById(`${service}PricingSummary`);
                if (!summary) return;

                const files = service === 'books'
                    ? getAllBookFiles()
                    : (service === 'color_printing' ? getAllColorPrintingFiles() : getAllNotesFiles());

                if (files.length === 0) {
                    renderCheckoutSummary(summary, service, 'ارفع الملفات لعرض الإجمالي.');
                    return;
                }

                if (service !== 'books' && files.some(fileData => !fileData.binding)) {
                    renderCheckoutSummary(summary, service, 'اختر نوع التغليف لكل ملف قبل الانتقال للسلة.');
                    return;
                }

                if (service === 'books' && files.some(fileData => !fileData.coverColor)) {
                    renderCheckoutSummary(summary, service, 'اختر لون الجلد لكل ملف قبل الانتقال للسلة.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = service === 'color_printing'
                        ? calculateColorPrintingFilePrice(fileData.pages, fileData.binding, fileData.pageSize, fileData.copies, fileData.printSides)
                        : calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize, fileData.copies);
                    if (service === 'color_printing') {
                        sum.print += price.printPrice;
                    }
                    sum.binding += price.bindingPrice;
                    return sum;
                }, { print: 0, binding: 0, total: 0 });
                if (service !== 'color_printing') {
                    totals.print = calculatePrintProductPrintTotal(files, service);
                }
                totals.total = totals.print + totals.binding;

                renderCheckoutSummary(summary, service, '', totals, true);
            }

            function updateAcademicPricingSummary(service) {
                const summary = document.getElementById(`${service}PricingSummary`);
                if (!summary) return;

                const files = getAcademicPrintableFiles(service);

                if (files.length === 0) {
                    renderCheckoutSummary(summary, service, 'ارفع ملف PDF للإضافة إلى السلة وعرض الإجمالي.');
                    return;
                }

                if (service === 'thesis' && uploadedFiles.thesis.pdf.some(fileData => !fileData.thesisProjectType)) {
                    renderCheckoutSummary(summary, service, 'اختر نوع مشروع الرسالة لكل ملف PDF قبل الانتقال للسلة.');
                    return;
                }

                if (files.some(fileData => !fileData.coverColor || !fileData.writingColor)) {
                    renderCheckoutSummary(summary, service, 'اختر لون الرسالة ولون الكتابة لكل ملف قبل الانتقال للسلة.');
                    return;
                }

                if (files.some(fileData => !canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor))) {
                    renderCheckoutSummary(summary, service, 'الكتابة باللون الأسود متاحة فقط مع البيج أو الأزرق الفاتح أو الأخضر الفاتح أو الأبيض.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                    sum.print += price.printPrice;
                    sum.binding += price.bindingPrice;
                    sum.cd += price.cdPrice;
                    sum.total += price.total;
                    return sum;
                }, { print: 0, binding: 0, cd: 0, total: 0 });

                renderCheckoutSummary(summary, service, '', totals, true);
            }

            function updateFormattingPricingSummary() {
                const summary = document.getElementById('formattingPricingSummary');
                if (!summary) return;

                const files = uploadedFiles.formatting.word;

                if (files.length === 0) {
                    renderCheckoutSummary(summary, 'formatting', 'ارفع ملفات Word لعرض الإجمالي.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = calculateFormattingFilePrice(fileData.pages);
                    sum.print += price.printPrice;
                    sum.binding += price.bindingPrice;
                    sum.total += price.total;
                    return sum;
                }, { print: 0, binding: 0, total: 0 });

                renderCheckoutSummary(summary, 'formatting', '', totals, true);
            }

            function updateResearchPricingSummary() {
                const summary = document.getElementById('researchPricingSummary');
                if (!summary) return;

                const title = document.getElementById('researchTitle')?.value.trim() || '';
                const studentName = document.getElementById('researchStudentName')?.value.trim() || '';
                const instructorName = document.getElementById('researchInstructorName')?.value.trim() || '';
                const institutionName = document.getElementById('researchInstitutionName')?.value.trim() || '';
                const pagesValue = String(document.getElementById('researchPages')?.value || '').trim();
                const pages = /^[0-9]+$/.test(pagesValue) ? Math.max(1, numericValue(pagesValue) || 0) : 0;

                if (!title) {
                    renderCheckoutSummary(summary, 'research', 'اكتب اسم البحث المطلوب أولًا.');
                    return;
                }

                if (!pages) {
                    renderCheckoutSummary(summary, 'research', 'حدد عدد الصفحات المطلوبة.');
                    return;
                }

                if (!studentName || !instructorName || !institutionName) {
                    renderCheckoutSummary(summary, 'research', 'أكمل اسم الطالب واسم الدكتور أو الأستاذ واسم الجهة التعليمية.');
                    return;
                }

                const price = calculateResearchPrice(pages);
                const totals = {
                    print: price.printPrice,
                    binding: price.bindingPrice,
                    total: price.total
                };

                const hasSavedCurrentRequest = currentOrders.research
                    && savedResearchRequest.title === title
                    && savedResearchRequest.studentName === studentName
                    && savedResearchRequest.instructorName === instructorName
                    && savedResearchRequest.institutionName === institutionName
                    && savedResearchRequest.pages === pages;

                if (!hasSavedCurrentRequest) {
                    summary.classList.remove('empty');
                    summary.innerHTML = `
                        <div>سعر إنشاء البحوث: ${totals.binding} ريال | الإجمالي: ${totals.total} ريال</div>
                        <div class="checkout-row">
                            <span class="checkout-button disabled">احفظ الطلب أولًا</span>
                        </div>
                    `;
                    return;
                }

                renderCheckoutSummary(summary, 'research', '', totals, true);
            }

            function changeResearchPages(delta) {
                const pagesInput = document.getElementById('researchPages');
                if (!pagesInput) return;

                const currentPages = Math.max(1, Math.min(9999, numericValue(pagesInput.value) || 1));
                pagesInput.value = Math.max(1, Math.min(9999, currentPages + delta));
                updateResearchPricingSummary();
            }

            async function saveResearchRequest() {
                const titleInput = document.getElementById('researchTitle');
                const studentNameInput = document.getElementById('researchStudentName');
                const instructorNameInput = document.getElementById('researchInstructorName');
                const institutionNameInput = document.getElementById('researchInstitutionName');
                const pagesInput = document.getElementById('researchPages');
                const button = document.getElementById('researchSaveButton');
                const errorDiv = document.getElementById('researchError');
                const title = titleInput.value.trim();
                const studentName = studentNameInput.value.trim();
                const instructorName = instructorNameInput.value.trim();
                const institutionName = institutionNameInput.value.trim();
                const pagesValue = String(pagesInput.value || '').trim();
                const pages = /^[0-9]+$/.test(pagesValue) ? Math.max(1, numericValue(pagesValue) || 0) : 0;

                if (!title || !studentName || !instructorName || !institutionName || !pages) {
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = 'أكمل اسم البحث والطالب والدكتور أو الأستاذ والجهة التعليمية وعدد الصفحات.';
                    updateResearchPricingSummary();
                    return;
                }

                errorDiv.style.display = 'none';
                button.disabled = true;
                button.textContent = 'جاري الحفظ...';

                try {
                    const response = await fetch('/research-order', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            research_title: title,
                            research_student_name: studentName,
                            research_instructor_name: instructorName,
                            university_name: institutionName,
                            pages
                        })
                    });
                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'تعذر حفظ طلب إنشاء البحوث');
                    }

                    currentOrders.research = result.order_id;
                    savedResearchRequest.title = title;
                    savedResearchRequest.studentName = studentName;
                    savedResearchRequest.instructorName = instructorName;
                    savedResearchRequest.institutionName = institutionName;
                    savedResearchRequest.pages = pages;
                    updateResearchPricingSummary();
                } catch (error) {
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = error.message || 'تعذر حفظ طلب إنشاء البحوث';
                } finally {
                    button.disabled = false;
                    button.textContent = 'حفظ الطلب';
                }
            }

            async function countPDFPages(file) {
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const content = e.target.result;
                        const pageMatches = content.match(/\/Type\s*\/Page\b(?!s)/g);
                        if (pageMatches && pageMatches.length > 0) {
                            resolve(pageMatches.length);
                            return;
                        }

                        const countMatches = [...content.matchAll(/\/Count\s+(\d+)/g)]
                            .map(match => numericValue(match[1]))
                            .filter(count => count > 0);
                        resolve(countMatches.length ? Math.max(...countMatches) : 1);
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
                const dropdownId = `universityDropdown-${service}-${type}-${index}`;
                const searchId = `universitySearch-${service}-${type}-${index}`;
                const resultsId = `universityResults-${service}-${type}-${index}`;

                return `
                    <div data-label="الجامعة/المعهد" class="university-cell">
                        <button class="university-picker-button" type="button" onclick="toggleAcademicUniversityDropdown('${service}', '${type}', ${index})">اختيار من القائمة</button>
                        <div id="${dropdownId}" class="university-dropdown">
                            <input
                                id="${searchId}"
                                class="university-input"
                                value=""
                                autocomplete="off"
                                placeholder="ابحث داخل القائمة"
                                oninput="renderAcademicUniversityResults('${service}', '${type}', ${index}, this.value)"
                            />
                            <div id="${resultsId}" class="university-results"></div>
                        </div>
                        <input
                            class="university-custom-input"
                            value="${escapeHtml(universityName)}"
                            placeholder="اختياري: اختر أو اكتب الجامعة/المعهد"
                            oninput="setAcademicFileUniversity('${service}', '${type}', ${index}, this.value, false)"
                            onchange="setAcademicFileUniversity('${service}', '${type}', ${index}, this.value)"
                        />
                    </div>
                `;
            }

            function updateFilesList(configKey) {
                const config = fileConfigs[configKey];
                const listDiv = document.getElementById(config.listId);
                const files = uploadedFiles[config.service][config.type];
                const showPrice = config.service === 'notes' || config.service === 'books' || config.service === 'color_printing';
                const showColorPrintingPrice = config.service === 'color_printing';
                const showAcademicPrice = (config.service === 'thesis' || config.service === 'phd') && config.type === 'pdf';
                const showFormattingPrice = config.service === 'formatting';
                const showThesisProject = config.service === 'thesis' && config.type === 'pdf';
                const showPrintSides = (['notes', 'books', 'color_printing'].includes(config.service) && config.type === 'pdf') || showAcademicPrice;
                const showPageSize = ['notes', 'books', 'color_printing'].includes(config.service) && config.type === 'pdf';

                if (files.length === 0) {
                    listDiv.innerHTML = '<div class="empty-message">لم يتم تحميل أي ملفات</div>';
                    if (showPrice) {
                        updatePrintProductPricingSummary(config.service);
                    } else if (showAcademicPrice) {
                        updateAcademicPricingSummary(config.service);
                    } else if (showFormattingPrice) {
                        updateFormattingPricingSummary();
                    }
                    return;
                }

                let html = '';
                files.forEach((fileData, index) => {
                    const price = showPrice && (fileData.binding || config.service === 'books')
                        ? (showColorPrintingPrice
                            ? calculateColorPrintingFilePrice(fileData.pages, fileData.binding, fileData.pageSize, fileData.copies, fileData.printSides)
                            : calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, config.service, fileData.pageSize, fileData.copies))
                        : null;
                    const bindingHtml = showPrice
                        ? `
                            <div data-label="${config.service === 'books' ? 'التجليد' : 'التغليف'}">
                                ${config.service === 'books'
                                    ? '<span class="file-price">تجليد كعب جلد طبيعي</span>'
                                    : `<select class="binding-select" required onchange="setPrintProductFileBinding('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="" ${!fileData.binding ? 'selected' : ''} disabled>اختر التغليف</option>
                                    <option value="tape" ${fileData.binding === 'tape' ? 'selected' : ''}>تغليف دبوس</option>
                                    <option value="wire" ${fileData.binding === 'wire' ? 'selected' : ''}>تغليف سلك</option>
                                    <option value="normal" ${fileData.binding === 'normal' ? 'selected' : ''}>تغليف عادي</option>
                                    ${showColorPrintingPrice ? `<option value="thermal" ${fileData.binding === 'thermal' ? 'selected' : ''}>تغليف حراري - يحسب على كل ورقة</option>` : ''}
                                    <option value="none" ${fileData.binding === 'none' ? 'selected' : ''}>بدون أي تغليف</option>
                                </select>`}
                            </div>
                        `
                        : '';
                    const notesPrintPriceHtml = showPrice
                        ? `<div class="file-price" data-label="سعر الطباعة">${price ? `${formatMoney(price.printPrice)} ريال` : '-'}</div>`
                        : '';
                    const notesBindingPriceHtml = showPrice
                        ? `<div class="file-price" data-label="${config.service === 'books' ? 'سعر التجليد' : 'سعر التغليف'}">${price ? `${formatMoney(price.bindingPrice)} ريال` : '-'}</div>`
                        : '';
                    const notesTotalPriceHtml = showPrice
                        ? `<div class="file-price" data-label="الإجمالي">${price ? `${formatMoney(price.total)} ريال` : 'اختر التغليف'}${price?.note ? `<span class="file-price-note">${price.note}</span>` : ''}</div>`
                        : '';
                    const paperColorHtml = config.service === 'notes' || config.service === 'books'
                        ? `
                            <div data-label="لون الورق">
                                <select class="binding-select" onchange="setPrintProductPaperColor('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="white" ${(fileData.paperColor || 'white') === 'white' ? 'selected' : ''}>أبيض</option>
                                    <option value="yellow" ${fileData.paperColor === 'yellow' ? 'selected' : ''}>أصفر</option>
                                </select>
                            </div>
                        `
                        : '';
                    const bookLeatherColorHtml = config.service === 'books'
                        ? `
                            <div data-label="لون الجلد">
                                <select class="binding-select" onchange="setBookLeatherColor('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="" ${!fileData.coverColor ? 'selected' : ''} disabled>اختر لون الجلد</option>
                                    ${Object.entries(bookLeatherColors).map(([value, label]) => `<option value="${value}" ${fileData.coverColor === value ? 'selected' : ''}>${label}</option>`).join('')}
                                </select>
                            </div>
                        `
                        : '';
                    const printSidesHtml = showPrintSides
                        ? `
                            <div class="${showAcademicPrice ? 'academic-choice-cell' : ''}" data-label="نوع الطباعة">
                                <select class="binding-select ${showAcademicPrice ? 'academic-choice-select' : ''}" onchange="setFilePrintSides('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="one_side" ${(fileData.printSides || (showColorPrintingPrice ? 'one_side' : 'two_sides')) === 'one_side' ? 'selected' : ''}>وجه واحد</option>
                                    <option value="two_sides" ${(fileData.printSides || 'two_sides') === 'two_sides' ? 'selected' : ''}>وجهين</option>
                                </select>
                            </div>
                        `
                        : '';
                    const pageSizeHtml = showPageSize
                        ? `
                            <div data-label="حجم الصفحة">
                                <select class="binding-select" onchange="setFilePageSize('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="A4" ${(fileData.pageSize || 'A4') === 'A4' ? 'selected' : ''}>A4</option>
                                    ${showColorPrintingPrice ? `<option value="A3" ${fileData.pageSize === 'A3' ? 'selected' : ''}>A3</option>` : ''}
                                    <option value="A5" ${fileData.pageSize === 'A5' ? 'selected' : ''}>A5</option>
                                    <option value="B5" ${fileData.pageSize === 'B5' ? 'selected' : ''}>B5</option>
                                </select>
                            </div>
                        `
                        : '';
                    const hasAcademicColors = showAcademicPrice && fileData.coverColor && fileData.writingColor && canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor);
                    const academicPrice = showAcademicPrice ? calculateAcademicFilePrice(config.service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies) : null;
                    const formattingPrice = showFormattingPrice ? calculateFormattingFilePrice(fileData.pages) : null;
                    const copiesKey = `${config.service}-${config.type}-${index}`;
                    const showPrintProductCopies = showPrice && ['notes', 'books', 'color_printing'].includes(config.service);
                    const copiesHtml = showAcademicPrice || showPrintProductCopies
                        ? `<div class="${showAcademicPrice ? 'academic-choice-cell academic-copies-cell' : ''}" data-label="عدد النسخ">
                            <div class="copies-stepper">
                                <button class="copies-stepper-button" type="button" onclick="${showPrintProductCopies ? 'changePrintProductFileCopies' : 'changeAcademicFileCopies'}('${config.service}', '${config.type}', ${index}, -1)">-</button>
                                <input class="copies-input" data-copies-input="${copiesKey}" type="number" inputmode="numeric" min="1" max="999" step="1" value="${fileData.copies || 1}" oninput="${showPrintProductCopies ? 'setPrintProductFileCopies' : 'setAcademicFileCopies'}('${config.service}', '${config.type}', ${index}, this.value, false)" onchange="${showPrintProductCopies ? 'setPrintProductFileCopies' : 'setAcademicFileCopies'}('${config.service}', '${config.type}', ${index}, this.value, true)" />
                                <button class="copies-stepper-button" type="button" onclick="${showPrintProductCopies ? 'changePrintProductFileCopies' : 'changeAcademicFileCopies'}('${config.service}', '${config.type}', ${index}, 1)">+</button>
                            </div>
                        </div>`
                        : '';
                    const cdTypeHtml = showAcademicPrice
                        ? `<div class="academic-choice-cell" data-label="خيار CD">
                            <select class="binding-select academic-choice-select" onchange="setAcademicCdType('${config.service}', '${config.type}', ${index}, this.value)">
                                <option value="none" ${(fileData.cdType || 'none') === 'none' ? 'selected' : ''}>بدون CD</option>
                                <option value="plain" ${fileData.cdType === 'plain' ? 'selected' : ''}>CD عادي (${formatMoney(servicePricing.academic_cd_plain)} ريال)</option>
                                <option value="printed" ${fileData.cdType === 'printed' ? 'selected' : ''}>CD مطبوع (${formatMoney(servicePricing.academic_cd_printed)} ريال)</option>
                            </select>
                        </div>`
                        : '';
                    const cdCopiesKey = `cd-${config.service}-${config.type}-${index}`;
                    const cdCopiesDisabled = !fileData.cdType || fileData.cdType === 'none';
                    const cdCopiesHtml = showAcademicPrice
                        ? `<div class="academic-choice-cell academic-copies-cell" data-label="عدد CD">
                            <div class="copies-stepper">
                                <button class="copies-stepper-button" type="button" ${cdCopiesDisabled ? 'disabled' : ''} onclick="changeAcademicCdCopies('${config.service}', '${config.type}', ${index}, -1)">-</button>
                                <input class="copies-input cd-copies-input" data-cd-copies-input="${cdCopiesKey}" type="number" inputmode="numeric" min="1" max="999" step="1" value="${cdCopiesDisabled ? 0 : (fileData.cdCopies || 1)}" ${cdCopiesDisabled ? 'disabled' : ''} oninput="setAcademicCdCopies('${config.service}', '${config.type}', ${index}, this.value, false)" onchange="setAcademicCdCopies('${config.service}', '${config.type}', ${index}, this.value, true)" />
                                <button class="copies-stepper-button" type="button" ${cdCopiesDisabled ? 'disabled' : ''} onclick="changeAcademicCdCopies('${config.service}', '${config.type}', ${index}, 1)">+</button>
                            </div>
                        </div>`
                        : '';
                    const thesisProjectHtml = showThesisProject
                        ? `
                            <div class="academic-choice-cell" data-label="مشروع الرسالة">
                                <select class="binding-select academic-choice-select" onchange="setThesisProjectType(${index}, this.value)">
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
                    const coverColorHtml = showAcademicPrice
                        ? `
                            <div class="academic-choice-cell" data-label="لون الرسالة">
                                <select class="binding-select academic-choice-select" onchange="setAcademicCoverColor('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="" ${!fileData.coverColor ? 'selected' : ''} disabled>اختر لون الرسالة</option>
                                    ${Object.entries(academicCoverColors).map(([value, label]) => `<option value="${value}" ${fileData.coverColor === value ? 'selected' : ''}>${label}</option>`).join('')}
                                </select>
                            </div>
                        `
                        : '';
                    const writingColorHtml = showAcademicPrice
                        ? `
                            <div class="academic-choice-cell" data-label="لون الكتابة">
                                <select class="binding-select academic-choice-select" onchange="setAcademicWritingColor('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="" ${!fileData.writingColor ? 'selected' : ''} disabled>اختر لون الكتابة</option>
                                    ${Object.entries(academicWritingColors).map(([value, label]) => {
                                        const disabled = value === 'black' && fileData.coverColor && !blackWritingAllowedCovers.includes(fileData.coverColor);
                                        return `<option value="${value}" ${fileData.writingColor === value ? 'selected' : ''} ${disabled ? 'disabled' : ''}>${label}</option>`;
                                    }).join('')}
                                </select>
                                ${fileData.coverColor && fileData.writingColor && !canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor) ? '<span class="file-price-note">الكتابة السوداء لا تناسب هذا اللون</span>' : ''}
                            </div>
                        `
                        : '';
                    const academicPrintPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="سعر الطباعة" data-academic-price="${config.service}-${config.type}-${index}" data-price-kind="print">${academicPrice.printPrice} ريال</div>`
                        : '';
                    const academicBindingPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="سعر التجليد" data-academic-price="${config.service}-${config.type}-${index}" data-price-kind="binding">${hasAcademicColors ? `${academicPrice.bindingPrice} ريال` : 'اختر الألوان'}</div>`
                        : '';
                    const academicCdPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="سعر CD" data-academic-price="${config.service}-${config.type}-${index}" data-price-kind="cd">${academicPrice.cdPrice} ريال</div>`
                        : '';
                    const academicTotalPriceHtml = showAcademicPrice
                        ? `<div class="file-price" data-label="الإجمالي" data-academic-price="${config.service}-${config.type}-${index}" data-price-kind="total">${hasAcademicColors ? `${academicPrice.total} ريال` : 'اختر الألوان'}</div>`
                        : '';
                    const formattingPriceHtml = showFormattingPrice
                        ? `<div class="file-price" data-label="سعر التنسيق">${formattingPrice.bindingPrice} ريال</div>`
                        : '';
                    const formattingTotalPriceHtml = showFormattingPrice
                        ? `<div class="file-price" data-label="الإجمالي">${formattingPrice.total} ريال</div>`
                        : '';

                    html += `
                        <div class="files-list-item${showPrice ? ' has-price' : ''}${showColorPrintingPrice ? ' has-color-printing-price' : ''}${showAcademicPrice ? ' has-academic-university' : ''}${showThesisProject ? ' has-thesis-project' : ''}${showFormattingPrice ? ' has-formatting-price' : ''}">
                            <div class="file-name-cell" data-label="اسم الملف">${fileData.filename}</div>
                            <div class="file-pages" data-label="الصفحات">${fileData.pages} صفحة</div>
                            <div class="file-size" data-label="الحجم">${fileData.size}</div>
                            ${printSidesHtml}
                            ${pageSizeHtml}
                            ${paperColorHtml}
                            ${bookLeatherColorHtml}
                            ${bindingHtml}
                            ${notesPrintPriceHtml}
                            ${notesBindingPriceHtml}
                            ${notesTotalPriceHtml}
                            ${copiesHtml}
                            ${cdTypeHtml}
                            ${cdCopiesHtml}
                            ${thesisProjectHtml}
                            ${universityHtml}
                            ${coverColorHtml}
                            ${writingColorHtml}
                            ${academicPrintPriceHtml}
                            ${academicBindingPriceHtml}
                            ${academicCdPriceHtml}
                            ${academicTotalPriceHtml}
                            ${formattingPriceHtml}
                            ${formattingTotalPriceHtml}
                            <div data-label="الحالة" style="color: #047857; font-weight: 600;">✓ مرفوع</div>
                            <div class="file-remove" data-label="الإجراء" onclick="removeFile('${config.service}', '${config.type}', ${index})">حذف</div>
                        </div>
                    `;
                });

                listDiv.innerHTML = html;
                bindEnglishNumberWarnings(listDiv);
                if (showPrice) {
                    updatePrintProductPricingSummary(config.service);
                } else if (showAcademicPrice) {
                    updateAcademicPricingSummary(config.service);
                } else if (showFormattingPrice) {
                    updateFormattingPricingSummary();
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
                if (service === 'notes' || service === 'books' || service === 'color_printing') {
                    updatePrintProductPricingSummary(service);
                } else if (service === 'thesis' || service === 'phd') {
                    updateAcademicPricingSummary(service);
                } else if (service === 'formatting') {
                    updateFormattingPricingSummary();
                }
            }

            function setPrintProductFileBinding(service, type, index, binding) {
                const fileData = uploadedFiles[service][type][index];
                fileData.binding = binding;
                const price = service === 'color_printing'
                    ? calculateColorPrintingFilePrice(fileData.pages, binding, fileData.pageSize, fileData.copies, fileData.printSides)
                    : calculateNotesFilePrice(fileData.pages, binding, fileData.paperColor, service, fileData.pageSize, fileData.copies);
                updateStoredFile(fileData, {
                    binding_type: binding,
                    ...(service === 'color_printing' ? { print_sides: fileData.printSides || 'one_side' } : {}),
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(getConfigKey(service, type));
                updatePrintProductPricingSummary(service);
            }

            function setPrintProductFileCopies(service, type, index, copies, rerender = true) {
                const fileData = uploadedFiles[service][type][index];
                const copiesValue = String(copies ?? '').trim();
                if (!/^[0-9]+$/.test(copiesValue)) {
                    if (rerender) {
                        updateFilesList(getConfigKey(service, type));
                    }
                    updatePrintProductPricingSummary(service);
                    return;
                }

                fileData.copies = Math.max(1, numericValue(copiesValue) || 1);
                const price = service === 'color_printing'
                    ? calculateColorPrintingFilePrice(fileData.pages, fileData.binding, fileData.pageSize, fileData.copies, fileData.printSides)
                    : calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize, fileData.copies);
                const payload = {
                    copies: fileData.copies,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                };
                if (service === 'color_printing') {
                    payload.print_sides = fileData.printSides || 'one_side';
                }
                updateStoredFile(fileData, payload);
                if (rerender) {
                    updateFilesList(getConfigKey(service, type));
                }
                updatePrintProductPricingSummary(service);
            }

            function changePrintProductFileCopies(service, type, index, delta) {
                const fileData = uploadedFiles[service][type][index];
                const currentCopies = Math.max(1, numericValue(fileData.copies) || 1);
                const nextCopies = Math.min(999, Math.max(1, currentCopies + delta));
                const key = `${service}-${type}-${index}`;
                const input = document.querySelector(`[data-copies-input="${key}"]`);

                fileData.copies = nextCopies;
                if (input) {
                    input.value = nextCopies;
                }

                const price = service === 'color_printing'
                    ? calculateColorPrintingFilePrice(fileData.pages, fileData.binding, fileData.pageSize, fileData.copies, fileData.printSides)
                    : calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize, fileData.copies);
                const payload = {
                    copies: fileData.copies,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                };
                if (service === 'color_printing') {
                    payload.print_sides = fileData.printSides || 'one_side';
                }
                updateStoredFile(fileData, payload);
                updateFilesList(getConfigKey(service, type));
                updatePrintProductPricingSummary(service);
            }

            function setPrintProductPaperColor(service, type, index, paperColor) {
                const fileData = uploadedFiles[service][type][index];
                fileData.paperColor = paperColor || 'white';
                const price = calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize, fileData.copies);
                updateStoredFile(fileData, {
                    paper_color: fileData.paperColor,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(getConfigKey(service, type));
                updatePrintProductPricingSummary(service);
            }

            function setBookLeatherColor(service, type, index, coverColor) {
                const fileData = uploadedFiles[service][type][index];
                fileData.coverColor = coverColor || '';
                updateStoredFile(fileData, {
                    cover_color: fileData.coverColor || null
                });
                updateFilesList(getConfigKey(service, type));
                updatePrintProductPricingSummary(service);
            }

            function setFilePrintSides(service, type, index, printSides) {
                const fileData = uploadedFiles[service][type][index];
                fileData.printSides = printSides || (service === 'color_printing' ? 'one_side' : 'two_sides');
                const payload = {
                    print_sides: fileData.printSides
                };

                if (service === 'color_printing') {
                    const price = calculateColorPrintingFilePrice(fileData.pages, fileData.binding, fileData.pageSize, fileData.copies, fileData.printSides);
                    payload.print_price = price.printPrice;
                    payload.binding_price = price.bindingPrice;
                    payload.total_price = price.total;
                }

                updateStoredFile(fileData, payload);
                if (service === 'color_printing') {
                    updateFilesList(getConfigKey(service, type));
                    updatePrintProductPricingSummary(service);
                }
            }

            function setFilePageSize(service, type, index, pageSize) {
                const fileData = uploadedFiles[service][type][index];
                fileData.pageSize = pageSize || 'A4';
                const payload = {
                    page_size: fileData.pageSize
                };

                if (service === 'books' || service === 'color_printing') {
                    const price = service === 'color_printing'
                        ? calculateColorPrintingFilePrice(fileData.pages, fileData.binding, fileData.pageSize, fileData.copies, fileData.printSides)
                        : calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize, fileData.copies);
                    payload.print_price = price.printPrice;
                    payload.binding_price = price.bindingPrice;
                    payload.total_price = price.total;
                    if (service === 'color_printing') {
                        payload.print_sides = fileData.printSides || 'one_side';
                    }
                }

                updateStoredFile(fileData, payload);
                if (service === 'books' || service === 'color_printing') {
                    updateFilesList(getConfigKey(service, type));
                    updatePrintProductPricingSummary(service);
                }
            }

            function refreshAcademicFilePriceDisplay(service, type, index) {
                const fileData = uploadedFiles[service][type][index];
                const key = `${service}-${type}-${index}`;
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                const hasAcademicColors = fileData.coverColor && fileData.writingColor && canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor);
                const printCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="print"]`);
                const bindingCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="binding"]`);
                const cdCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="cd"]`);
                const totalCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="total"]`);

                if (printCell) {
                    printCell.textContent = `${price.printPrice} ريال`;
                }

                if (bindingCell) {
                    bindingCell.textContent = hasAcademicColors ? `${price.bindingPrice} ريال` : 'اختر الألوان';
                }

                if (cdCell) {
                    cdCell.textContent = `${price.cdPrice} ريال`;
                }

                if (totalCell) {
                    totalCell.textContent = hasAcademicColors ? `${price.total} ريال` : 'اختر الألوان';
                }
            }

            function setAcademicFileCopies(service, type, index, copies, rerender = true) {
                const fileData = uploadedFiles[service][type][index];
                const copiesValue = String(copies ?? '').trim();
                if (!/^[0-9]+$/.test(copiesValue)) {
                    if (rerender) {
                        updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                    }
                    updateAcademicPricingSummary(service);
                    return;
                }

                fileData.copies = Math.max(1, numericValue(copiesValue) || 1);
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                updateStoredFile(fileData, {
                    copies: fileData.copies,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                if (rerender) {
                    updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                } else {
                    refreshAcademicFilePriceDisplay(service, type, index);
                }
                updateAcademicPricingSummary(service);
            }

            function changeAcademicFileCopies(service, type, index, delta) {
                const fileData = uploadedFiles[service][type][index];
                const currentCopies = Math.max(1, numericValue(fileData.copies) || 1);
                const nextCopies = Math.min(999, Math.max(1, currentCopies + delta));
                const key = `${service}-${type}-${index}`;
                const input = document.querySelector(`[data-copies-input="${key}"]`);

                fileData.copies = nextCopies;
                if (input) {
                    input.value = nextCopies;
                }

                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                updateStoredFile(fileData, {
                    copies: fileData.copies,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                refreshAcademicFilePriceDisplay(service, type, index);
                updateAcademicPricingSummary(service);
            }

            function setAcademicCdType(service, type, index, cdType) {
                const fileData = uploadedFiles[service][type][index];
                fileData.cdType = ['plain', 'printed'].includes(cdType) ? cdType : 'none';
                fileData.cdCopies = fileData.cdType === 'none' ? 0 : Math.max(1, numericValue(fileData.cdCopies) || 1);
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);

                updateStoredFile(fileData, {
                    cd_type: fileData.cdType,
                    cd_copies: fileData.cdCopies,
                    cd_price: price.cdPrice,
                    total_price: price.total
                });
                updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                updateAcademicPricingSummary(service);
            }

            function setAcademicCdCopies(service, type, index, copies, rerender = true) {
                const fileData = uploadedFiles[service][type][index];
                if (!fileData.cdType || fileData.cdType === 'none') return;

                const copiesValue = String(copies ?? '').trim();
                if (!/^[0-9]+$/.test(copiesValue)) {
                    if (rerender) {
                        updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                    }
                    return;
                }

                fileData.cdCopies = Math.min(999, Math.max(1, numericValue(copiesValue) || 1));
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                updateStoredFile(fileData, {
                    cd_type: fileData.cdType,
                    cd_copies: fileData.cdCopies,
                    cd_price: price.cdPrice,
                    total_price: price.total
                });

                if (rerender) {
                    updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                } else {
                    refreshAcademicFilePriceDisplay(service, type, index);
                }
                updateAcademicPricingSummary(service);
            }

            function changeAcademicCdCopies(service, type, index, delta) {
                const fileData = uploadedFiles[service][type][index];
                if (!fileData.cdType || fileData.cdType === 'none') return;

                const currentCopies = Math.max(1, numericValue(fileData.cdCopies) || 1);
                const nextCopies = Math.min(999, Math.max(1, currentCopies + delta));
                const key = `cd-${service}-${type}-${index}`;
                const input = document.querySelector(`[data-cd-copies-input="${key}"]`);

                fileData.cdCopies = nextCopies;
                if (input) input.value = nextCopies;

                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                updateStoredFile(fileData, {
                    cd_type: fileData.cdType,
                    cd_copies: fileData.cdCopies,
                    cd_price: price.cdPrice,
                    total_price: price.total
                });
                refreshAcademicFilePriceDisplay(service, type, index);
                updateAcademicPricingSummary(service);
            }

            function academicUniversityIds(service, type, index) {
                return {
                    dropdownId: `universityDropdown-${service}-${type}-${index}`,
                    searchId: `universitySearch-${service}-${type}-${index}`,
                    resultsId: `universityResults-${service}-${type}-${index}`,
                };
            }

            function renderAcademicUniversityResults(service, type, index, search = '') {
                const { resultsId } = academicUniversityIds(service, type, index);
                const results = document.getElementById(resultsId);
                if (!results) return;

                const term = String(search || '').trim();
                const matches = saudiUniversitiesAndInstitutes
                    .filter(name => term === '' || name.includes(term))
                    .slice(0, 80);

                if (matches.length === 0) {
                    results.innerHTML = `<button class="university-result" type="button" onclick="setAcademicFileUniversity('${service}', '${type}', ${index}, '${escapeHtml(term)}')">استخدام: ${escapeHtml(term)}</button>`;
                    return;
                }

                results.innerHTML = matches
                    .map(name => {
                        const universityIndex = saudiUniversitiesAndInstitutes.indexOf(name);
                        return `<button class="university-result" type="button" onclick="chooseAcademicUniversity('${service}', '${type}', ${index}, ${universityIndex})">${escapeHtml(name)}</button>`;
                    })
                    .join('');
            }

            function chooseAcademicUniversity(service, type, index, universityIndex) {
                setAcademicFileUniversity(service, type, index, saudiUniversitiesAndInstitutes[universityIndex] || '');
            }

            function toggleAcademicUniversityDropdown(service, type, index) {
                const { dropdownId, searchId } = academicUniversityIds(service, type, index);
                const dropdown = document.getElementById(dropdownId);
                const search = document.getElementById(searchId);
                if (!dropdown || !search) return;

                const isOpen = dropdown.classList.toggle('active');
                if (isOpen) {
                    search.value = '';
                    renderAcademicUniversityResults(service, type, index);
                    search.focus();
                }
            }

            function setAcademicCoverColor(service, type, index, coverColor) {
                const fileData = uploadedFiles[service][type][index];
                fileData.coverColor = coverColor;

                if (fileData.writingColor === 'black' && !canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor)) {
                    fileData.writingColor = '';
                }

                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                updateStoredFile(fileData, {
                    cover_color: fileData.coverColor || null,
                    writing_color: fileData.writingColor || null,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                updateAcademicPricingSummary(service);
            }

            function setAcademicWritingColor(service, type, index, writingColor) {
                const fileData = uploadedFiles[service][type][index];

                if (writingColor === 'black' && !canUseAcademicWritingColor(fileData.coverColor, writingColor)) {
                    updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                    updateAcademicPricingSummary(service);
                    return;
                }

                fileData.writingColor = writingColor;
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor, fileData.cdType, fileData.cdCopies);
                updateStoredFile(fileData, {
                    cover_color: fileData.coverColor || null,
                    writing_color: fileData.writingColor || null,
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

                fileData.universityChoice = selectedValue;
                fileData.universityName = selectedValue;
                fileData.customUniversity = selectedValue;

                updateStoredFile(fileData, {
                    university_name: fileData.universityName || null
                });

                const { dropdownId } = academicUniversityIds(service, type, index);
                document.getElementById(dropdownId)?.classList.remove('active');

                if (rerender) {
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
                                    binding: response.binding_type || (config.service === 'books' ? 'normal' : ''),
                                    copies: 1,
                                    printSides: response.print_sides || (config.service === 'color_printing' ? 'one_side' : 'two_sides'),
                                    pageSize: response.page_size || 'A4',
                                    paperColor: response.paper_color || (config.service === 'color_printing' ? '' : 'white'),
                                    thesisProjectType: '',
                                    universityChoice: '',
                                    universityName: response.university_name || '',
                                    customUniversity: '',
                                    coverColor: response.cover_color || '',
                                    writingColor: response.writing_color || '',
                                    cdType: response.cd_type || 'none',
                                    cdCopies: Number(response.cd_copies || 0),
                                    cdPrice: Number(response.cd_price || 0)
                                });
                                updateFilesList(configKey);
                                if (config.service === 'notes' || config.service === 'books' || config.service === 'color_printing') {
                                    updatePrintProductPricingSummary(config.service);
                                } else if (config.service === 'thesis' || config.service === 'phd') {
                                    updateAcademicPricingSummary(config.service);
                                }
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

            function hydrateEditOrder(payload) {
                if (!payload || !payload.service_type || !uploadedFiles[payload.service_type]) {
                    return;
                }

                const service = payload.service_type;
                currentOrders[service] = payload.id;

                if (service === 'research') {
                    const researchFile = (payload.files || [])[0] || {};
                    savedResearchRequest.title = researchFile.research_title || researchFile.filename || '';
                    savedResearchRequest.studentName = researchFile.research_student_name || '';
                    savedResearchRequest.instructorName = researchFile.research_instructor_name || '';
                    savedResearchRequest.institutionName = researchFile.university_name || '';
                    savedResearchRequest.pages = Number(researchFile.pages || 0);
                    document.getElementById('researchTitle').value = savedResearchRequest.title;
                    document.getElementById('researchStudentName').value = savedResearchRequest.studentName;
                    document.getElementById('researchInstructorName').value = savedResearchRequest.instructorName;
                    document.getElementById('researchInstitutionName').value = savedResearchRequest.institutionName;
                    document.getElementById('researchPages').value = savedResearchRequest.pages || 1;
                    updateResearchPricingSummary();
                    return;
                }

                uploadedFiles[service].word = [];
                uploadedFiles[service].pdf = [];

                (payload.files || []).forEach((file) => {
                    const type = file.file_type;
                    if (!uploadedFiles[service][type]) {
                        return;
                    }

                    uploadedFiles[service][type].push({
                        id: file.id,
                        filename: file.filename,
                        pages: Number(file.pages || 1),
                        size: file.size || '0 Bytes',
                        binding: file.binding_type || (service === 'books' ? 'normal' : ''),
                        copies: Number(file.copies || 1),
                        printSides: file.print_sides || (service === 'color_printing' ? 'one_side' : 'two_sides'),
                        pageSize: file.page_size || 'A4',
                        paperColor: file.paper_color || (service === 'color_printing' ? '' : 'white'),
                        thesisProjectType: file.thesis_project_type || '',
                        universityChoice: '',
                        universityName: file.university_name || '',
                        customUniversity: '',
                        coverColor: file.cover_color || '',
                        writingColor: file.writing_color || '',
                        cdType: file.cd_type || 'none',
                        cdCopies: Number(file.cd_copies || 0),
                        cdPrice: Number(file.cd_price || 0),
                    });
                });

                Object.keys(uploadedFiles[service]).forEach((type) => {
                    const configKey = getConfigKey(service, type);
                    if (configKey) {
                        updateFilesList(configKey);
                    }
                });
            }

            bindEnglishNumberWarnings();

            const editOrderPayload = @json($editOrderPayload ?? null);
            const requestedService = new URLSearchParams(window.location.search).get('service') || editOrderPayload?.service_type;
            const editableServices = ['notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research'];
            if (requestedService && editableServices.includes(requestedService)) {
                selectService(requestedService);
            }
            hydrateEditOrder(editOrderPayload);

            // Load JSZip library
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            document.head.appendChild(script);
        </script>

        @include('shared.chat-widget')
        @include('shared.language-tools')

        <footer class="page-footer" id="info">
            <div class="footer-content">
                <p>منصة متخصصة في خدمات الطباعة والتجليد للمذكرات والأبحاث والرسائل العلمية.</p>
                <p>&copy; 2026 خدمات الطباعة والتجليد. جميع الحقوق محفوظة.</p>
            </div>
        </footer>
    </body>
</html>
