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
            .header-inner { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 0; }
            .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; margin-bottom: 4px; }
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
            .files-list-header.has-formatting-price,
            .files-list-item.has-formatting-price { min-width: 820px; grid-template-columns: 2fr 0.8fr 0.8fr 1fr 1fr 0.7fr 0.45fr; }
            .files-list { border: 0; background: transparent; overflow: visible; }
            .files-list-header { display: none; }
            .files-list-item,
            .files-list-item.has-price,
            .files-list-item.has-copies-price,
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
            .research-form-grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(130px, 0.6fr); gap: 14px; align-items: end; }
            .research-field { display: flex; flex-direction: column; gap: 8px; }
            .research-field label { color: #111827; font-size: 13px; font-weight: 900; }
            .research-input { width: 100%; padding: 12px 13px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; color: #111827; font-weight: 700; }
            .research-input:focus { outline: 2px solid rgba(14, 165, 233, 0.18); border-color: #38bdf8; }
            .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
            .english-number-warning.active { display: block; }
            .checkout-row { margin-top: 14px; display: flex; justify-content: flex-end; }
            .checkout-button { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; background: #047857; color: #ffffff; border-radius: 8px; text-decoration: none; font-weight: 800; border: 0; cursor: pointer; }
            .checkout-button:hover { background: #065f46; }
            .checkout-button.disabled { background: #cbd5e1; color: #64748b; pointer-events: none; }
            .delivery-box { margin: 14px 0; padding: 14px; border: 1px solid #dbe4ef; border-radius: 12px; background: #f8fafc; text-align: right; }
            .delivery-title { margin: 0 0 10px; color: #0f172a; font-weight: 900; }
            .delivery-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
            .delivery-option { display: flex; align-items: flex-start; gap: 8px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 10px; background: #ffffff; cursor: pointer; color: #1f2937; font-weight: 800; line-height: 1.5; }
            .delivery-option small { display: block; color: #64748b; font-weight: 700; margin-top: 3px; }
            .delivery-fields { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 10px 0; }
            .delivery-fields.address-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .delivery-input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; color: #111827; font-weight: 700; }
            .delivery-note { margin: 8px 0 10px; padding: 10px 12px; border-radius: 10px; background: #ecfdf5; color: #047857; font-weight: 900; line-height: 1.6; }
            .delivery-save { padding: 10px 14px; border: 0; border-radius: 8px; background: #0f172a; color: #ffffff; font-weight: 900; cursor: pointer; }
            .delivery-save:disabled { background: #94a3b8; cursor: not-allowed; }
            .delivery-status { margin-top: 8px; color: #047857; font-weight: 800; line-height: 1.6; }
            .delivery-status.error { color: #b91c1c; }
            @media (max-width: 700px) {
                .delivery-options,
                .delivery-fields,
                .delivery-fields.address-fields { grid-template-columns: 1fr; }
            }
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
                .files-list-item.has-formatting-price,
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
                .copies-stepper,
                .university-input,
                .university-custom-input,
                .university-picker-button {
                    width: min(100%, 220px);
                    min-width: 0;
                }
                .research-form-grid { grid-template-columns: 1fr; }
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
                    @php
                        $hasCustomerOrderNotice = \App\Models\Order::query()
                            ->where('user_id', auth()->id())
                            ->whereNull('customer_notification_seen_at')
                            ->whereHas('deliveredFiles', fn ($query) => $query->whereNull('customer_downloaded_at'))
                            ->exists();
                    @endphp
                    <span class="header-user">👤 {{ auth()->user()->name }}</span>
                    <a class="header-link" href="{{ route('orders.index') }}">
                        🧾 طلباتي
                        @if ($hasCustomerOrderNotice)
                            <span class="customer-notice-dot" data-customer-orders-dot aria-label="تحديث جديد في طلباتك"></span>
                        @endif
                    </a>
                    <a class="header-link settings-link" href="{{ route('account.settings') }}">⚙️ إعداداتي</a>
                    @if (auth()->user()->role === 'admin')
                        <a class="header-link" href="{{ route('admin.orders') }}">لوحة المدير</a>
                    @endif
                    <form class="header-form" method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">🚪 خروج</button>
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
                    <h3 class="service-title">طباعة المذكرات وملفات ال بي دي اف</h3>
                    <p class="service-description">طباعة جميع المذكرات بجميع أحجامها وتغليفها.</p>
                    <button class="service-entry" type="button" onclick="selectService('notes')">الدخول للخدمة</button>
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
                    <p class="service-description">تجهيز ملفات الدكتوراه للطباعة والتجليد مع عرض كامل للتكاليف قبل الدفع.</p>
                    <button class="service-entry" type="button" onclick="selectService('phd')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">✍️</div>
                    <h3 class="service-title">تنسيق الرسائل الجامعية</h3>
                    <p class="service-description">رفع ملف Word فقط واحتساب سعر التنسيق تلقائيًا حسب عدد الصفحات.</p>
                    <button class="service-entry" type="button" onclick="selectService('formatting')">الدخول للخدمة</button>
                </article>

                <article class="service-card">
                    <div class="service-icon">🔎</div>
                    <h3 class="service-title">إنشاء بحث</h3>
                    <p class="service-description">اكتب اسم البحث وعدد الصفحات المطلوبة، ويتم احتساب سعر الخدمة تلقائيًا.</p>
                    <button class="service-entry" type="button" onclick="selectService('research')">الدخول للخدمة</button>
                </article>
            </div>

            <!-- Upload Section for Notes -->
            <div id="uploadNotes" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>تحميل ملفات مذكرات</h2>
                
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
                        <p class="binding-required">ملف PDF إجباري لإتمام الطلب.</p>
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
                        <p class="binding-required">ملف PDF إجباري لإتمام الطلب.</p>
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
                <h2>تنسيق الرسائل الجامعية</h2>

                <div class="upload-section">
                    <div class="upload-box" id="formattingWordBox">
                        <div class="file-icon">📄</div>
                        <h3>تحميل ملف Word</h3>
                        <input type="file" id="formattingWordFile" accept=".doc,.docx" multiple />
                        <p class="file-info">صيغ مدعومة: .doc, .docx</p>
                        <p class="file-info">سعر التنسيق: 10 ريال لكل صفحة</p>
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
                    <h3>إجمالي تنسيق الرسائل الجامعية</h3>
                    <div id="formattingPricingSummary" class="pricing-summary empty">ارفع ملفات Word لعرض الإجمالي.</div>
                </div>
            </div>

            <!-- Research Creation Service -->
            <div id="uploadResearch" class="upload-content">
                <button class="back-button" onclick="backToServices()">← العودة للخدمات</button>
                <h2>إنشاء بحث</h2>

                <div class="binding-section">
                    <h3>تفاصيل البحث</h3>
                    <div class="research-form-grid">
                        <div class="research-field">
                            <label for="researchTitle">اسم البحث المطلوب</label>
                            <input class="research-input" id="researchTitle" type="text" maxlength="255" placeholder="مثال: أثر التقنية في التعليم" oninput="updateResearchPricingSummary()" />
                        </div>
                        <div class="research-field">
                            <label for="researchPages">عدد الصفحات</label>
                            <input class="research-input" id="researchPages" type="text" inputmode="numeric" placeholder="اكتب عدد الصفحات" oninput="updateResearchPricingSummary()" />
                        </div>
                    </div>
                    <button class="upload-button" id="researchSaveButton" type="button" onclick="saveResearchRequest()">حفظ الطلب</button>
                    <div id="researchError" class="error-msg" style="display: none;"></div>
                </div>

                <div class="binding-section">
                    <h3>إجمالي إنشاء البحث</h3>
                    <div id="researchPricingSummary" class="pricing-summary empty">اكتب اسم البحث وعدد الصفحات لعرض الإجمالي.</div>
                </div>
            </div>
        </main>

        <datalist id="saudiUniversitiesList"></datalist>

        <script>
            // Store uploaded files for each service
            const uploadedFiles = {
                notes: { word: [], pdf: [] },
                books: { word: [], pdf: [] },
                thesis: { word: [], pdf: [] },
                phd: { word: [], pdf: [] },
                formatting: { word: [], pdf: [] },
                research: { word: [], pdf: [] }
            };
            const currentOrders = {
                notes: null,
                books: null,
                thesis: null,
                phd: null,
                formatting: null,
                research: null
            };
            const deliveryServices = ['notes', 'books', 'thesis', 'phd'];
            const deliverySelections = {
                notes: { method: '', fee: 0, saved: false, unit: '', floor: '', room: '', city: '', district: '', street: '', mapUrl: '' },
                books: { method: '', fee: 0, saved: false, unit: '', floor: '', room: '', city: '', district: '', street: '', mapUrl: '' },
                thesis: { method: '', fee: 0, saved: false, unit: '', floor: '', room: '', city: '', district: '', street: '', mapUrl: '' },
                phd: { method: '', fee: 0, saved: false, unit: '', floor: '', room: '', city: '', district: '', street: '', mapUrl: '' }
            };
            const savedResearchRequest = {
                title: '',
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

            function selectService(service) {
                initializeSaudiUniversitiesList();
                document.getElementById('servicesScreen').style.display = 'none';
                document.getElementById('upload' + service.charAt(0).toUpperCase() + service.slice(1)).classList.add('active');
                initializeService(service);
            }

            function backToServices() {
                document.getElementById('servicesScreen').style.display = 'flex';
                document.getElementById('uploadNotes').classList.remove('active');
                document.getElementById('uploadBooks').classList.remove('active');
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

            function bindEnglishNumberWarnings(root = document) {
                const selector = '#researchPages, .copies-input';
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

            function calculateNotesFilePrice(pages, binding, paperColor = 'white', service = 'notes', pageSize = 'A4') {
                if (service === 'books') {
                    const printPrice = paperColor === 'yellow' ? Math.ceil(pages / 10) : Math.ceil(pages / 15);
                    const bindingPrice = pageSize === 'A4' ? 55 : 45;

                    return {
                        printPrice,
                        bindingPrice,
                        total: printPrice + bindingPrice,
                        note: ''
                    };
                }

                const printPrice = paperColor === 'yellow' ? Math.ceil(pages / 6) : Math.ceil(pages / 15);
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

            function canUseAcademicWritingColor(coverColor, writingColor) {
                return writingColor !== 'black' || blackWritingAllowedCovers.includes(coverColor);
            }

            function calculateAcademicFilePrice(service, pages, copies, writingColor = '') {
                const copyCount = Math.max(1, numericValue(copies) || 1);
                const printPrice = getPrintPrice(pages) * copyCount;
                if (!writingColor) {
                    return {
                        printPrice,
                        bindingPrice: 0,
                        total: printPrice
                    };
                }

                const bindingSinglePrice = writingColor === 'gold' ? 90 : 60;
                const bindingMultiPrice = writingColor === 'gold' ? 75 : 45;
                const bindingPrice = copyCount === 1 ? bindingSinglePrice : bindingMultiPrice * copyCount;

                return {
                    printPrice,
                    bindingPrice,
                    total: printPrice + bindingPrice
                };
            }

            function calculateFormattingFilePrice(pages) {
                const formattingPrice = (numericValue(pages) || 1) * 10;

                return {
                    printPrice: 0,
                    bindingPrice: formattingPrice,
                    total: formattingPrice
                };
            }

            function calculateResearchPrice(pages) {
                const researchPrice = Math.max(1, numericValue(pages) || 1) * 10;

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
                            <span class="checkout-button disabled">إتمام الطلب</span>
                        </div>
                    `;
                    return;
                }

                const noPrintServiceLabels = {
                    formatting: 'سعر التنسيق',
                    research: 'سعر إنشاء البحث'
                };
                const productBindingLabel = service === 'books'
                    ? 'سعر التجليد'
                    : (service === 'notes' ? 'سعر التغليف' : (noPrintServiceLabels[service] || 'سعر التجليد'));

                const totalsText = noPrintServiceLabels[service]
                    ? `${noPrintServiceLabels[service]}: ${totals.binding} ريال | الإجمالي: ${totals.total} ريال`
                    : `سعر الطباعة: ${totals.print} ريال | ${productBindingLabel}: ${totals.binding} ريال | الإجمالي: ${totals.total} ريال`;
                const deliveryHtml = deliveryServices.includes(service)
                    ? renderDeliveryBox(service, totals.total)
                    : '';
                const delivery = deliverySelections[service];
                const deliveryFee = deliveryServices.includes(service) && delivery?.saved ? estimateDeliveryFee(delivery.method, totals.total) : 0;
                const grandTotalText = deliveryServices.includes(service) && delivery?.saved
                    ? `<div>رسوم التوصيل: ${deliveryFee} ريال | الإجمالي بعد التوصيل: ${totals.total + deliveryFee} ريال</div>`
                    : '';
                const canProceed = !deliveryServices.includes(service) || delivery?.saved;
                const deliveryNoticeMessages = {
                    formatting: 'سيتم إرسال الملف بعد الانتهاء داخل التطبيق في صفحة طلباتي فور الانتهاء من التنسيق إن شاء الله.',
                    research: 'سيتم إرسال الملف بعد الانتهاء داخل التطبيق في صفحة طلباتي خلال ٢٤ ساعة إلى ٤٨ ساعة إن شاء الله.'
                };
                const deliveryNotice = deliveryNoticeMessages[service]
                    ? `<div class="delivery-notice">${deliveryNoticeMessages[service]}</div>`
                    : '';

                summary.innerHTML = `
                    <div>${totalsText}</div>
                    ${deliveryHtml}
                    ${grandTotalText}
                    ${deliveryNotice}
                    <div class="checkout-row">
                        ${canProceed
                            ? '<a class="checkout-button" href="/my-orders">إتمام الطلب</a>'
                            : '<span class="checkout-button disabled">احفظ طريقة الاستلام أو التوصيل</span>'}
                    </div>
                `;
            }

            function renderDeliveryBox(service, subtotal) {
                const delivery = deliverySelections[service] || {};
                const method = delivery.method || '';
                const campusFee = subtotal >= 35 ? 0 : 5;
                const status = delivery.saved
                    ? `تم حفظ طريقة التوصيل. الرسوم: ${delivery.fee} ريال.`
                    : 'اختر طريقة الاستلام أو التوصيل ثم اضغط حفظ.';

                return `
                    <div class="delivery-box">
                        <div class="delivery-title">الاستلام والتوصيل</div>
                        <div class="delivery-options">
                            <label class="delivery-option">
                                <input type="radio" name="${service}Delivery" value="branch_pickup" ${method === 'branch_pickup' ? 'checked' : ''} onchange="setDeliveryMethod('${service}', this.value)">
                                <span>استلام من الفرع <small>مجانًا</small></span>
                            </label>
                            <label class="delivery-option">
                                <input type="radio" name="${service}Delivery" value="islamic_university_delivery" ${method === 'islamic_university_delivery' ? 'checked' : ''} onchange="setDeliveryMethod('${service}', this.value)">
                                <span>توصيل داخل الجامعة الإسلامية <small>${campusFee === 0 ? 'مجانًا للطلبات 35 ريال فأكثر' : '5 ريال'}</small></span>
                            </label>
                            <label class="delivery-option">
                                <input type="radio" name="${service}Delivery" value="madinah_delivery" ${method === 'madinah_delivery' ? 'checked' : ''} onchange="setDeliveryMethod('${service}', this.value)">
                                <span>توصيل داخل المدينة المنورة <small>20 ريال</small></span>
                            </label>
                            <label class="delivery-option">
                                <input type="radio" name="${service}Delivery" value="redbox_delivery" ${method === 'redbox_delivery' ? 'checked' : ''} onchange="setDeliveryMethod('${service}', this.value)">
                                <span>خارج المدينة المنورة عبر RedBox <small>30 ريال إلى أقرب نقطة شحن</small></span>
                            </label>
                        </div>
                        <div class="delivery-fields" style="${method === 'islamic_university_delivery' ? '' : 'display:none;'}">
                            <input class="delivery-input" id="${service}DeliveryUnit" value="${escapeHtml(delivery.unit || '')}" placeholder="رقم الوحدة" oninput="setDeliveryField('${service}', 'unit', this.value)">
                            <input class="delivery-input" id="${service}DeliveryFloor" value="${escapeHtml(delivery.floor || '')}" placeholder="رقم الدور" oninput="setDeliveryField('${service}', 'floor', this.value)">
                            <input class="delivery-input" id="${service}DeliveryRoom" value="${escapeHtml(delivery.room || '')}" placeholder="رقم الغرفة" oninput="setDeliveryField('${service}', 'room', this.value)">
                        </div>
                        <div class="delivery-note" style="${method === 'islamic_university_delivery' ? '' : 'display:none;'}">تنبيه: إذا طلبك فوق 35 ريال يكون التوصيل داخل الجامعة الإسلامية مجانًا.</div>
                        <div class="delivery-fields address-fields" style="${['madinah_delivery', 'redbox_delivery'].includes(method) ? '' : 'display:none;'}">
                            <input class="delivery-input" value="${escapeHtml(delivery.city || '')}" placeholder="اسم المدينة" ${method === 'madinah_delivery' ? 'readonly' : ''} oninput="setDeliveryField('${service}', 'city', this.value)">
                            <input class="delivery-input" value="${escapeHtml(delivery.district || '')}" placeholder="اسم الحي" oninput="setDeliveryField('${service}', 'district', this.value)">
                            <input class="delivery-input" value="${escapeHtml(delivery.street || '')}" placeholder="اسم الشارع" oninput="setDeliveryField('${service}', 'street', this.value)">
                            <input class="delivery-input" value="${escapeHtml(delivery.mapUrl || '')}" placeholder="انسخ رابط موقعك من Google Maps" oninput="setDeliveryField('${service}', 'mapUrl', this.value)">
                        </div>
                        <button class="delivery-save" type="button" onclick="saveDelivery('${service}')">حفظ طريقة التوصيل</button>
                        <div id="${service}DeliveryStatus" class="delivery-status">${status}</div>
                    </div>
                `;
            }

            function estimateDeliveryFee(method, subtotal) {
                if (method === 'islamic_university_delivery') {
                    return subtotal >= 35 ? 0 : 5;
                }
                if (method === 'madinah_delivery') {
                    return 20;
                }
                if (method === 'redbox_delivery') {
                    return 30;
                }

                return 0;
            }

            function setDeliveryMethod(service, method) {
                deliverySelections[service].method = method;
                if (method === 'madinah_delivery') {
                    deliverySelections[service].city = 'المدينة المنورة';
                } else if (method === 'redbox_delivery' && isMadinahCity(deliverySelections[service].city)) {
                    deliverySelections[service].city = '';
                } else if (!['madinah_delivery', 'redbox_delivery'].includes(method)) {
                    deliverySelections[service].city = '';
                    deliverySelections[service].district = '';
                    deliverySelections[service].street = '';
                    deliverySelections[service].mapUrl = '';
                }
                deliverySelections[service].saved = false;
                updatePrintProductPricingSummary(service);
                if (service === 'thesis' || service === 'phd') {
                    updateAcademicPricingSummary(service);
                }
            }

            function setDeliveryField(service, field, value) {
                deliverySelections[service][field] = value.trim();
                deliverySelections[service].saved = false;
            }

            async function saveDelivery(service) {
                const orderId = currentOrders[service];
                const delivery = deliverySelections[service];
                const status = document.getElementById(`${service}DeliveryStatus`);

                if (!orderId) return;
                if (!delivery.method) {
                    if (status) {
                        status.textContent = 'اختر طريقة الاستلام أو التوصيل أولًا.';
                        status.classList.add('error');
                    }
                    return;
                }
                if (delivery.method === 'islamic_university_delivery' && (!delivery.unit || !delivery.floor || !delivery.room)) {
                    if (status) {
                        status.textContent = 'اكتب رقم الوحدة ورقم الدور ورقم الغرفة.';
                        status.classList.add('error');
                    }
                    return;
                }
                if (delivery.method === 'madinah_delivery' && (!delivery.district || !delivery.street || !delivery.mapUrl)) {
                    if (status) {
                        status.textContent = 'اكتب اسم الحي والشارع وأضف رابط موقعك من Google Maps.';
                        status.classList.add('error');
                    }
                    return;
                }
                if (delivery.method === 'redbox_delivery' && (!delivery.city || !delivery.district || !delivery.street || !delivery.mapUrl)) {
                    if (status) {
                        status.textContent = 'اكتب اسم المدينة والحي والشارع وأضف رابط موقعك من Google Maps.';
                        status.classList.add('error');
                    }
                    return;
                }
                if (delivery.method === 'redbox_delivery' && isMadinahCity(delivery.city)) {
                    if (status) {
                        status.textContent = 'خيار خارج المدينة لا يقبل المدينة المنورة. اكتب اسم المدينة خارج المدينة المنورة.';
                        status.classList.add('error');
                    }
                    return;
                }

                try {
                    const response = await fetch(`/cart/${orderId}/delivery`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            delivery_method: delivery.method,
                            delivery_unit: delivery.unit,
                            delivery_floor: delivery.floor,
                            delivery_room: delivery.room,
                            delivery_city: delivery.city,
                            delivery_district: delivery.district,
                            delivery_street: delivery.street,
                            delivery_map_url: delivery.mapUrl
                        })
                    });
                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'تعذر حفظ طريقة التوصيل');
                    }

                    delivery.fee = Number(result.delivery_fee || 0);
                    delivery.saved = true;
                    if (service === 'notes' || service === 'books') {
                        updatePrintProductPricingSummary(service);
                    } else {
                        updateAcademicPricingSummary(service);
                    }
                } catch (error) {
                    if (status) {
                        status.textContent = error.message || 'تعذر حفظ طريقة التوصيل';
                        status.classList.add('error');
                    }
                }
            }

            function isMadinahCity(city) {
                const normalized = String(city || '')
                    .trim()
                    .replace(/\s+/g, '')
                    .replace(/[ة]/g, 'ه')
                    .replace(/[أإآ]/g, 'ا');

                return ['المدينهالمنوره', 'مدينهالمنوره', 'المدينه', 'طيبه'].includes(normalized);
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

                const files = service === 'books' ? getAllBookFiles() : getAllNotesFiles();

                if (files.length === 0) {
                    renderCheckoutSummary(summary, service, 'ارفع الملفات لعرض الإجمالي.');
                    return;
                }

                if (service !== 'books' && files.some(fileData => !fileData.binding)) {
                    renderCheckoutSummary(summary, service, 'اختر نوع التغليف لكل ملف قبل إتمام الطلب.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize);
                    sum.print += price.printPrice;
                    sum.binding += price.bindingPrice;
                    sum.total += price.total;
                    return sum;
                }, { print: 0, binding: 0, total: 0 });

                renderCheckoutSummary(summary, service, '', totals, true);
            }

            function updateAcademicPricingSummary(service) {
                const summary = document.getElementById(`${service}PricingSummary`);
                if (!summary) return;

                const files = getAcademicPrintableFiles(service);

                if (files.length === 0) {
                    renderCheckoutSummary(summary, service, 'ارفع ملف PDF لإتمام الطلب وعرض الإجمالي.');
                    return;
                }

                if (service === 'thesis' && uploadedFiles.thesis.pdf.some(fileData => !fileData.thesisProjectType)) {
                    renderCheckoutSummary(summary, service, 'اختر نوع مشروع الرسالة لكل ملف PDF قبل إتمام الطلب.');
                    return;
                }

                if (files.some(fileData => !fileData.coverColor || !fileData.writingColor)) {
                    renderCheckoutSummary(summary, service, 'اختر لون الرسالة ولون الكتابة لكل ملف قبل إتمام الطلب.');
                    return;
                }

                if (files.some(fileData => !canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor))) {
                    renderCheckoutSummary(summary, service, 'الكتابة باللون الأسود متاحة فقط مع البيج أو الأزرق الفاتح أو الأخضر الفاتح أو الأبيض.');
                    return;
                }

                const totals = files.reduce((sum, fileData) => {
                    const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor);
                    sum.print += price.printPrice;
                    sum.binding += price.bindingPrice;
                    sum.total += price.total;
                    return sum;
                }, { print: 0, binding: 0, total: 0 });

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
                const pagesValue = document.getElementById('researchPages')?.value.trim() || '';
                const pages = /^[0-9]+$/.test(pagesValue) ? Math.max(1, numericValue(pagesValue) || 0) : 0;

                if (!title) {
                    renderCheckoutSummary(summary, 'research', 'اكتب اسم البحث المطلوب أولًا.');
                    return;
                }

                if (!pages) {
                    renderCheckoutSummary(summary, 'research', 'حدد عدد الصفحات المطلوبة.');
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
                    && savedResearchRequest.pages === pages;

                if (!hasSavedCurrentRequest) {
                    summary.classList.remove('empty');
                    summary.innerHTML = `
                        <div>سعر إنشاء البحث: ${totals.binding} ريال | الإجمالي: ${totals.total} ريال</div>
                        <div class="checkout-row">
                            <span class="checkout-button disabled">احفظ الطلب أولًا</span>
                        </div>
                    `;
                    return;
                }

                renderCheckoutSummary(summary, 'research', '', totals, true);
            }

            async function saveResearchRequest() {
                const titleInput = document.getElementById('researchTitle');
                const pagesInput = document.getElementById('researchPages');
                const button = document.getElementById('researchSaveButton');
                const errorDiv = document.getElementById('researchError');
                const title = titleInput.value.trim();
                const pagesValue = pagesInput.value.trim();
                const pages = /^[0-9]+$/.test(pagesValue) ? Math.max(1, numericValue(pagesValue) || 0) : 0;

                if (!title || !pages) {
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = 'اكتب اسم البحث وحدد عدد الصفحات بالأرقام الإنجليزية فقط.';
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
                            pages
                        })
                    });
                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'تعذر حفظ طلب إنشاء البحث');
                    }

                    currentOrders.research = result.order_id;
                    savedResearchRequest.title = title;
                    savedResearchRequest.pages = pages;
                    updateResearchPricingSummary();
                } catch (error) {
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = error.message || 'تعذر حفظ طلب إنشاء البحث';
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
                const showPrice = config.service === 'notes' || config.service === 'books';
                const showAcademicPrice = (config.service === 'thesis' || config.service === 'phd') && config.type === 'pdf';
                const showFormattingPrice = config.service === 'formatting';
                const showThesisProject = config.service === 'thesis' && config.type === 'pdf';
                const showPrintSides = (['notes', 'books'].includes(config.service) && config.type === 'pdf') || showAcademicPrice;
                const showPageSize = ['notes', 'books'].includes(config.service) && config.type === 'pdf';

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
                    const price = showPrice && (fileData.binding || config.service === 'books') ? calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, config.service, fileData.pageSize) : null;
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
                                    <option value="none" ${fileData.binding === 'none' ? 'selected' : ''}>بدون أي تغليف</option>
                                </select>`}
                            </div>
                        `
                        : '';
                    const notesPrintPriceHtml = showPrice
                        ? `<div class="file-price" data-label="سعر الطباعة">${price ? `${price.printPrice} ريال` : '-'}</div>`
                        : '';
                    const notesBindingPriceHtml = showPrice
                        ? `<div class="file-price" data-label="${config.service === 'books' ? 'سعر التجليد' : 'سعر التغليف'}">${price ? `${price.bindingPrice} ريال` : '-'}</div>`
                        : '';
                    const notesTotalPriceHtml = showPrice
                        ? `<div class="file-price" data-label="الإجمالي">${price ? `${price.total} ريال` : 'اختر التغليف'}${price?.note ? `<span class="file-price-note">${price.note}</span>` : ''}</div>`
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
                    const printSidesHtml = showPrintSides
                        ? `
                            <div data-label="نوع الطباعة">
                                <select class="binding-select" onchange="setFilePrintSides('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="two_sides" ${(fileData.printSides || 'two_sides') === 'two_sides' ? 'selected' : ''}>وجهين</option>
                                    <option value="one_side" ${fileData.printSides === 'one_side' ? 'selected' : ''}>وجه واحد</option>
                                </select>
                            </div>
                        `
                        : '';
                    const pageSizeHtml = showPageSize
                        ? `
                            <div data-label="حجم الصفحة">
                                <select class="binding-select" onchange="setFilePageSize('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="A4" ${(fileData.pageSize || 'A4') === 'A4' ? 'selected' : ''}>A4</option>
                                    <option value="A5" ${fileData.pageSize === 'A5' ? 'selected' : ''}>A5</option>
                                    <option value="B5" ${fileData.pageSize === 'B5' ? 'selected' : ''}>B5</option>
                                </select>
                            </div>
                        `
                        : '';
                    const hasAcademicColors = showAcademicPrice && fileData.coverColor && fileData.writingColor && canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor);
                    const academicPrice = showAcademicPrice ? calculateAcademicFilePrice(config.service, fileData.pages, fileData.copies, fileData.writingColor) : null;
                    const formattingPrice = showFormattingPrice ? calculateFormattingFilePrice(fileData.pages) : null;
                    const copiesKey = `${config.service}-${config.type}-${index}`;
                    const copiesHtml = showAcademicPrice
                        ? `<div data-label="عدد النسخ">
                            <div class="copies-stepper">
                                <button class="copies-stepper-button" type="button" onclick="changeAcademicFileCopies('${config.service}', '${config.type}', ${index}, -1)">-</button>
                                <input class="copies-input" data-copies-input="${copiesKey}" type="number" inputmode="numeric" min="1" max="999" step="1" value="${fileData.copies || 1}" oninput="setAcademicFileCopies('${config.service}', '${config.type}', ${index}, this.value, false)" onchange="setAcademicFileCopies('${config.service}', '${config.type}', ${index}, this.value, true)" />
                                <button class="copies-stepper-button" type="button" onclick="changeAcademicFileCopies('${config.service}', '${config.type}', ${index}, 1)">+</button>
                            </div>
                        </div>`
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
                    const coverColorHtml = showAcademicPrice
                        ? `
                            <div data-label="لون الرسالة">
                                <select class="binding-select" onchange="setAcademicCoverColor('${config.service}', '${config.type}', ${index}, this.value)">
                                    <option value="" ${!fileData.coverColor ? 'selected' : ''} disabled>اختر لون الرسالة</option>
                                    ${Object.entries(academicCoverColors).map(([value, label]) => `<option value="${value}" ${fileData.coverColor === value ? 'selected' : ''}>${label}</option>`).join('')}
                                </select>
                            </div>
                        `
                        : '';
                    const writingColorHtml = showAcademicPrice
                        ? `
                            <div data-label="لون الكتابة">
                                <select class="binding-select" onchange="setAcademicWritingColor('${config.service}', '${config.type}', ${index}, this.value)">
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
                        <div class="files-list-item${showPrice ? ' has-price' : ''}${showAcademicPrice ? ' has-academic-university' : ''}${showThesisProject ? ' has-thesis-project' : ''}${showFormattingPrice ? ' has-formatting-price' : ''}">
                            <div class="file-name-cell" data-label="اسم الملف">${fileData.filename}</div>
                            <div class="file-pages" data-label="الصفحات">${fileData.pages} صفحة</div>
                            <div class="file-size" data-label="الحجم">${fileData.size}</div>
                            ${printSidesHtml}
                            ${pageSizeHtml}
                            ${paperColorHtml}
                            ${bindingHtml}
                            ${notesPrintPriceHtml}
                            ${notesBindingPriceHtml}
                            ${notesTotalPriceHtml}
                            ${copiesHtml}
                            ${thesisProjectHtml}
                            ${universityHtml}
                            ${coverColorHtml}
                            ${writingColorHtml}
                            ${academicPrintPriceHtml}
                            ${academicBindingPriceHtml}
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
                if (service === 'notes' || service === 'books') {
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
                const price = calculateNotesFilePrice(fileData.pages, binding, fileData.paperColor, service, fileData.pageSize);
                updateStoredFile(fileData, {
                    binding_type: binding,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                updatePrintProductPricingSummary(service);
            }

            function setPrintProductPaperColor(service, type, index, paperColor) {
                const fileData = uploadedFiles[service][type][index];
                fileData.paperColor = paperColor || 'white';
                const price = calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize);
                updateStoredFile(fileData, {
                    paper_color: fileData.paperColor,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
                    total_price: price.total
                });
                updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                updatePrintProductPricingSummary(service);
            }

            function setFilePrintSides(service, type, index, printSides) {
                const fileData = uploadedFiles[service][type][index];
                fileData.printSides = printSides || 'two_sides';
                updateStoredFile(fileData, {
                    print_sides: fileData.printSides
                });
            }

            function setFilePageSize(service, type, index, pageSize) {
                const fileData = uploadedFiles[service][type][index];
                fileData.pageSize = pageSize || 'A4';
                const payload = {
                    page_size: fileData.pageSize
                };

                if (service === 'books') {
                    const price = calculateNotesFilePrice(fileData.pages, fileData.binding, fileData.paperColor, service, fileData.pageSize);
                    payload.print_price = price.printPrice;
                    payload.binding_price = price.bindingPrice;
                    payload.total_price = price.total;
                }

                updateStoredFile(fileData, payload);
                if (service === 'books') {
                    updateFilesList(`${service}${type.charAt(0).toUpperCase() + type.slice(1)}`);
                    updatePrintProductPricingSummary(service);
                }
            }

            function refreshAcademicFilePriceDisplay(service, type, index) {
                const fileData = uploadedFiles[service][type][index];
                const key = `${service}-${type}-${index}`;
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor);
                const hasAcademicColors = fileData.coverColor && fileData.writingColor && canUseAcademicWritingColor(fileData.coverColor, fileData.writingColor);
                const printCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="print"]`);
                const bindingCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="binding"]`);
                const totalCell = document.querySelector(`[data-academic-price="${key}"][data-price-kind="total"]`);

                if (printCell) {
                    printCell.textContent = `${price.printPrice} ريال`;
                }

                if (bindingCell) {
                    bindingCell.textContent = hasAcademicColors ? `${price.bindingPrice} ريال` : 'اختر الألوان';
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
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor);
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

                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor);
                updateStoredFile(fileData, {
                    copies: fileData.copies,
                    print_price: price.printPrice,
                    binding_price: price.bindingPrice,
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

                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor);
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
                const price = calculateAcademicFilePrice(service, fileData.pages, fileData.copies, fileData.writingColor);
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
                                    printSides: response.print_sides || 'two_sides',
                                    pageSize: response.page_size || 'A4',
                                    paperColor: response.paper_color || 'white',
                                    thesisProjectType: '',
                                    universityChoice: '',
                                    universityName: response.university_name || '',
                                    customUniversity: '',
                                    coverColor: response.cover_color || '',
                                    writingColor: response.writing_color || ''
                                });
                                updateFilesList(configKey);
                                if (config.service === 'notes' || config.service === 'books') {
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
            bindEnglishNumberWarnings();

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
