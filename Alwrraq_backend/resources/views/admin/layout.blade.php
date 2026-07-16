<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة المدير')</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .layout { min-height: 100vh; display: grid; grid-template-columns: var(--sidebar-width) minmax(0, 1fr); }
        aside { background: #0f172a; color: #f8fafc; padding: clamp(16px, 2vw, 24px) clamp(12px, 1.6vw, 18px); position: sticky; top: 0; height: 100vh; overflow-y: auto; box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15); }
        .brand { font-size: clamp(18px, 2vw, 24px); font-weight: 700; letter-spacing: 0.02em; overflow-wrap: anywhere; margin-bottom: 4px; }
        .brand-logo { width: 46px; height: 46px; border-radius: 14px; object-fit: cover; background: #ffffff; border: 1px solid rgba(255,255,255,0.18); box-shadow: 0 12px 26px rgba(0,0,0,0.18); margin-bottom: 10px; display: block; }
        .admin-name { color: #cbd5e1; font-size: clamp(12px, 1.15vw, 14px); margin: 0 0 24px; line-height: 1.6; }
        nav { display: flex; flex-direction: column; align-items: stretch; gap: clamp(8px, 1.2vw, 12px); }
        nav a, .logout { display: flex; align-items: center; gap: 8px; width: 100%; color: #f8fafc; text-decoration: none; border: 1px solid rgba(148, 163, 184, 0.14); border-radius: 10px; padding: 9px 10px; background: rgba(255, 255, 255, 0.055); text-align: right; font: inherit; font-size: clamp(12px, 1.15vw, 14px); font-weight: 800; line-height: 1.45; cursor: pointer; box-sizing: border-box; white-space: normal; transition: background 160ms ease, border-color 160ms ease, transform 160ms ease, box-shadow 160ms ease; }
        nav a { position: relative; }
        nav a:hover, nav a.active, .logout:hover { background: #1e293b; border-color: #475569; transform: translateX(-2px); box-shadow: 0 10px 22px rgba(0, 0, 0, 0.14); }
        .nav-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 26px; width: 26px; height: 26px; border-radius: 8px; background: rgba(255, 255, 255, 0.10); font-size: 14px; line-height: 1; }
        .nav-text { min-width: 0; flex: 1; }
        .nav-notice-dot { position: absolute; top: 8px; left: 9px; width: 7px; height: 7px; border-radius: 999px; background: #dc2626; box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.95); }
        nav a.settings-link { background: #0f4c81; border-color: rgba(96, 165, 250, 0.35); }
        nav a.settings-link:hover, nav a.settings-link.active { background: #1d6fa5; border-color: #60a5fa; }
        .logout { margin-top: 0; justify-content: center; background: #b91c1c; border-color: rgba(248, 113, 113, 0.5); font-weight: 800; }
        .logout:hover { background: #dc2626; border-color: #f87171; }
        .logout .nav-text { flex: 0 1 auto; }
        .mobile-menu-toggle { display: none; align-items: center; justify-content: center; gap: 7px; border: 1px solid rgba(148, 163, 184, 0.28); border-radius: 10px; background: rgba(255, 255, 255, 0.08); color: #ffffff; padding: 9px 11px; font-weight: 900; font-family: inherit; cursor: pointer; }
        main { min-width: 0; padding: clamp(16px, 3vw, 28px); overflow: auto; }
        .page-title { display: flex; justify-content: space-between; align-items: end; gap: 16px; margin-bottom: 20px; }
        h1 { margin: 0; font-size: clamp(24px, 4vw, 30px); }
        h2 { margin: 0 0 14px; font-size: clamp(19px, 3vw, 21px); }
        .subtitle { color: #64748b; margin: 6px 0 0; }
        .notice, .errors { margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; }
        .notice { background: #ecfdf5; color: #047857; }
        .errors { background: #fef2f2; color: #b91c1c; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
        .stat, .panel, .order { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); }
        .stat { padding: 16px; }
        .stat span { display: block; color: #64748b; font-size: 12px; margin-bottom: 8px; }
        .stat strong { font-size: 24px; }
        .panel { padding: 18px; margin-bottom: 18px; }
        .order { margin-bottom: 16px; overflow: hidden; }
        .order-head { padding: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .label { color: #64748b; display: block; margin-bottom: 4px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 9px 10px; border-bottom: 1px solid #e5e7eb; text-align: right; vertical-align: middle; }
        th { background: #ffffff; color: #334155; }
        a { color: #0369a1; font-weight: 700; text-decoration: none; }
        .empty { padding: 20px; color: #64748b; }
        .forms-grid { display: grid; grid-template-columns: 0.85fr 1.15fr; gap: 18px; align-items: start; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .form-section { margin-top: 14px; padding-top: 14px; border-top: 1px solid #e5e7eb; }
        .form-section:first-child { margin-top: 0; padding-top: 0; border-top: 0; }
        .form-section-title { margin: 0 0 10px; color: #0f172a; font-size: 16px; font-weight: 900; }
        .form-note { margin: 0 0 10px; color: #64748b; font-size: 12px; line-height: 1.7; }
        .permissions-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .permission-option { display: flex; align-items: center; gap: 8px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; color: #0f172a; font-size: 13px; font-weight: 800; }
        .permission-option input { width: auto; }
        label { display: block; color: #475569; font-weight: 700; font-size: 12px; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; font-size: 16px; }
        .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
        .english-number-warning.active { display: block; }
        .save { margin-top: 10px; padding: 10px 14px; border: 0; border-radius: 8px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .danger { margin-top: 10px; padding: 10px 14px; border: 0; border-radius: 8px; background: #b91c1c; color: #ffffff; font-weight: 800; cursor: pointer; }
        .toolbar { display: flex; justify-content: space-between; gap: 12px; align-items: end; margin-bottom: 16px; }
        .order-filter-bar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: clamp(6px, 1.5vw, 12px); margin-bottom: 16px; }
        .order-filter-button { display: flex; align-items: center; justify-content: center; min-width: 0; min-height: clamp(48px, 8vw, 64px); padding: clamp(8px, 2vw, 14px) clamp(6px, 1.8vw, 14px); border-radius: 10px; color: #ffffff; text-decoration: none; font-size: clamp(11px, 2.4vw, 18px); font-weight: 900; line-height: 1.35; text-align: center; box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12); border: 2px solid transparent; white-space: normal; overflow-wrap: anywhere; }
        .order-filter-button.red { background: #dc2626; }
        .order-filter-button.yellow { background: #facc15; color: #422006; }
        .order-filter-button.green { background: #16a34a; }
        .order-filter-button.active { border-color: #0f172a; transform: translateY(-1px); }
        .search-form { display: flex; gap: 10px; align-items: end; width: 100%; min-width: 0; }
        .search-form > div { flex: 1; min-width: 0; }
        .search-form input { min-width: 0; width: 100%; }
        .management-table-wrap { width: 100%; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; }
        .management-table { table-layout: fixed; }
        .management-table th,
        .management-table td { white-space: normal; overflow-wrap: anywhere; }
        .management-table th:last-child,
        .management-table td:last-child { width: 32%; }
        .actions { display: flex; gap: 8px; align-items: center; }
        .small-button { margin-top: 0; padding: 7px 10px; border-radius: 7px; font-size: 12px; line-height: 1; }
        .ghost { margin-top: 0; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 7px; background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 800; cursor: pointer; }
        .badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 800; }
        .tiny-status-dot { display: inline-flex; width: 8px; height: 8px; border-radius: 999px; vertical-align: middle; margin-inline-start: 6px; box-shadow: 0 0 0 2px #ffffff; }
        .tiny-status-dot.red { background: #dc2626; }
        .tiny-status-dot.yellow { background: #facc15; }
        .tiny-status-dot.green { background: #16a34a; }
        .summary-action { display: flex; align-items: end; justify-content: flex-start; }
        .order-detail-modal { min-width: 0; }
        .order-detail-section { margin-bottom: 16px; }
        .order-detail-section:last-child { margin-bottom: 0; }
        .order-detail-table-wrap { width: 100%; overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; }
        .order-detail-table-wrap table { width: 100%; min-width: 760px; }
        .order-detail-table-wrap.research table { min-width: 0; table-layout: fixed; }
        .order-detail-table-wrap.research th,
        .order-detail-table-wrap.research td { width: 25%; white-space: normal; word-break: break-word; }
        .order-files-cards { display: flex; flex-direction: column; gap: 12px; }
        .order-file-card { display: grid; grid-template-columns: 1fr; gap: 0; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06); }
        .order-file-field { min-width: 0; padding: 12px 14px; border-bottom: 1px solid #edf2f7; border-inline-start: 1px solid #edf2f7; }
        .order-file-field,
        .order-file-field:nth-child(3n) { border-inline-start: 0; }
        .order-file-field span { display: block; margin-bottom: 5px; color: #64748b; font-size: 12px; font-weight: 900; line-height: 1.5; }
        .order-file-field strong { display: block; color: #0f172a; font-size: 14px; font-weight: 900; line-height: 1.7; word-break: break-word; }
        .order-file-field.file-name { grid-column: 1 / -1; background: #f8fafc; }
        .order-file-field.file-name strong { font-size: 15px; }
        .order-file-field.price strong { color: #047857; }
        .order-file-field.total { background: #f0fdf4; }
        .order-file-field.actions-field { grid-column: 1 / -1; background: #f8fafc; }
        .order-file-field.actions-field .file-action-buttons { max-width: 360px; }
        .id-badge { display: inline-flex; align-items: center; margin-inline-start: 8px; padding: 2px 7px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 800; }
        .identity { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
        .muted { color: #64748b; font-size: 12px; }
        .delivered-files-list { display: flex; flex-direction: column; gap: 8px; margin: 10px 0 14px; }
        .delivered-file-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; }
        .delivered-file-name { color: #0f172a; font-weight: 900; line-height: 1.6; word-break: break-word; }
        .delivered-file-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .delivered-file-actions .ghost,
        .delivered-file-actions .save,
        .delivered-file-actions .danger { min-width: 110px; justify-content: center; text-align: center; }
        .file-action-buttons { display: grid; grid-template-columns: repeat(2, minmax(92px, 1fr)); gap: 8px; min-width: 200px; }
        .file-action-button { min-height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 8px 10px; border-radius: 8px; color: #ffffff; font-size: 12px; font-weight: 900; text-decoration: none; text-align: center; white-space: nowrap; }
        .file-action-button.view { background: #2563eb; }
        .file-action-button.view:hover { background: #1d4ed8; }
        .file-action-button.download { background: #16a34a; }
        .file-action-button.download:hover { background: #15803d; }
        .invoice-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .invoice-toolbar .action { display: inline-flex; min-width: 142px; justify-content: center; padding: 12px 18px; border-radius: 10px; border: 0; background: #047857; color: #ffffff; font-size: 15px; font-weight: 900; cursor: pointer; font-family: inherit; text-decoration: none; }
        .invoice-admin-button { display: inline-flex; align-items: center; justify-content: center; padding: 7px 10px; border: 1px solid #2563eb; border-radius: 7px; background: #0f4c81; color: #ffffff; font-size: 12px; font-weight: 900; cursor: pointer; font-family: inherit; text-decoration: none; }
        .invoice-admin-button:hover { background: #1d6fa5; }
        .compact-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
        .compact-actions form { margin: 0; }
        .invoice-document { color: #111827; background: #ffffff; border: 1px solid #dbe3ef; border-radius: 14px; padding: clamp(14px, 3vw, 24px); box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08); }
        .invoice-head { display: flex; justify-content: space-between; gap: 16px; align-items: stretch; padding: 18px; border-radius: 12px; background: #0f172a; color: #ffffff; margin-bottom: 18px; }
        .invoice-brand { display: flex; align-items: center; gap: 12px; }
        .invoice-logo { width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; background: #ffffff; color: #0f172a; font-size: 24px; font-weight: 900; }
                        .invoice-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; display: block; }
        .invoice-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; display: block; }
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
        .modal-backdrop { position: fixed; inset: 0; display: none; place-items: center; padding: clamp(10px, 3vw, 20px); background: rgba(15, 23, 42, 0.55); z-index: 40; overflow-y: auto; }
        .modal-backdrop.active { display: grid; }
        .modal { width: min(1120px, 100%); max-height: calc(100vh - 20px); background: #ffffff; border-radius: 12px; box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28); overflow: hidden; display: flex; flex-direction: column; }
        .modal-head { padding: 18px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .modal-head h2 { margin: 0; }
        .modal-body { padding: clamp(14px, 3vw, 20px); overflow-y: auto; }
        .modal-close { border: 0; background: #f1f5f9; border-radius: 8px; padding: 7px 10px; cursor: pointer; font-weight: 800; }
        .full { grid-column: 1 / -1; }
        @media (max-width: 980px) {
            :root { --sidebar-width: 0px; --page-gap: 10px; }
            .layout { grid-template-columns: minmax(0, 1fr); }
            aside { position: sticky; top: 0; height: auto; max-height: none; padding: 12px; z-index: 30; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16); display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 10px; }
            .admin-name { grid-column: 1 / -1; display: none; margin: 0 0 2px; }
            .mobile-menu-toggle { display: inline-flex; }
            nav { grid-column: 1 / -1; display: none; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            aside.menu-open .admin-name { display: block; }
            aside.menu-open nav { display: grid; }
            main { padding: 14px 10px 24px; }
            .stats, .forms-grid, .form-grid, .permissions-grid { grid-template-columns: 1fr; }
            .toolbar, .search-form { align-items: stretch; flex-direction: column; }
            .order-head { grid-template-columns: 1fr; }
            table { width: 100%; }
            .management-table-wrap { border: 0; background: transparent; overflow: visible; }
            .management-table { display: block; table-layout: auto; }
            .management-table thead { display: none; }
            .management-table tbody { display: grid; gap: 12px; }
            .management-table tr { display: grid; gap: 8px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
            .management-table td { display: grid; grid-template-columns: minmax(86px, 35%) minmax(0, 1fr); align-items: center; gap: 8px; padding: 0; border-bottom: 0; white-space: normal; }
            .management-table td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 900; }
            .management-table td:last-child { width: auto; }
            .management-table .actions { justify-content: flex-start; flex-wrap: wrap; }
            .management-table .empty { display: block; }
            .management-table .empty::before { display: none; }
            .order-detail-table-wrap table { display: table; overflow: visible; white-space: normal; }
            .order-file-card { grid-template-columns: 1fr; }
            .order-file-field,
            .order-file-field:nth-child(3n) { border-inline-start: 0; }
            .order-detail-table-wrap.research th,
            .order-detail-table-wrap.research td { font-size: 12px; padding: 9px 7px; }
            .search-form input { min-width: 0; }
            .actions, nav form { width: 100%; }
            nav a, .logout, .save, .danger, .ghost { width: 100%; text-align: center; justify-content: center; }
            .delivered-file-item { align-items: stretch; flex-direction: column; }
            .delivered-file-actions { width: 100%; }
            .file-action-buttons { grid-template-columns: 1fr; min-width: 0; }
            .invoice-head, .invoice-grid, .invoice-totals, .invoice-summary { grid-template-columns: 1fr; }
            .invoice-head { flex-direction: column; }
            .compact-actions { width: 100%; }
            .compact-actions .invoice-admin-button,
            .compact-actions .danger { width: auto; }
            .invoice-table-wrap { border: 0; overflow: visible; }
            .invoice-table-wrap table { min-width: 0; display: block; }
            .invoice-table-wrap thead { display: none; }
            .invoice-table-wrap tbody { display: grid; gap: 10px; }
            .invoice-table-wrap tr { display: grid; gap: 9px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #ffffff; }
            .invoice-table-wrap td { display: grid; grid-template-columns: minmax(100px, 38%) minmax(0, 1fr); align-items: start; gap: 8px; padding: 0; border-bottom: 0; white-space: normal; word-break: break-word; }
            .invoice-table-wrap td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 900; }
            .invoice-table-wrap td:last-child { padding: 9px; border-radius: 8px; }
        }
        @media (max-width: 560px) {
            .page-title { align-items: stretch; flex-direction: column; }
            .panel { padding: 14px; }
            nav { grid-template-columns: 1fr; }
            .order-filter-bar { gap: 7px; }
            .order-filter-button { min-height: 50px; font-size: 11px; padding: 8px 5px; }
            .management-table td { grid-template-columns: minmax(82px, 34%) minmax(0, 1fr); }
            .modal-backdrop { padding: 8px; }
            .modal { max-height: calc(100vh - 16px); border-radius: 10px; }
            .modal-head { padding: 12px 14px; }
            .modal-body { padding: 12px; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside>
            @php
                $hasOrdersAccess = auth()->user()->hasAdminPermission('orders_view');
                $hasUnopenedOrdersForAdmin = $hasOrdersAccess && \App\Models\Order::query()
                    ->whereNull('admin_notification_seen_at')
                    ->where('status', '!=', 'completed')
                    ->exists();
            @endphp
            <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
            <div class="brand">الورّاق</div>
            <button class="mobile-menu-toggle" type="button" onclick="toggleAdminHeader(this, event)" aria-expanded="false">☰ القائمة</button>
            <div class="admin-name">👤 {{ auth()->user()->name }}</div>
            <nav>
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-icon" aria-hidden="true">🏠</span><span class="nav-text">الرئيسية</span></a>
                @if ($hasOrdersAccess)
                    <a class="{{ request()->routeIs('admin.orders') ? 'active' : '' }}" href="{{ route('admin.orders') }}">
                        <span class="nav-icon" aria-hidden="true">🧾</span>
                        <span class="nav-text">الطلبات</span>
                        @if ($hasUnopenedOrdersForAdmin)
                            <span class="nav-notice-dot" aria-label="طلبات جديدة"></span>
                        @endif
                    </a>
                @endif
                @if (auth()->user()->hasAnyAdminPermission(['users_view', 'users_create', 'users_update', 'users_delete', 'users_permissions_manage']))
                    <a class="{{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}"><span class="nav-icon" aria-hidden="true">👥</span><span class="nav-text">المستخدمين</span></a>
                @endif
                @if (auth()->user()->hasAnyAdminPermission(['customers_view', 'customers_create', 'customers_update', 'customers_delete']))
                    <a class="{{ request()->routeIs('admin.customers') ? 'active' : '' }}" href="{{ route('admin.customers') }}"><span class="nav-icon" aria-hidden="true">👤</span><span class="nav-text">العملاء</span></a>
                @endif
                <a class="settings-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><span class="nav-icon" aria-hidden="true">⚙️</span><span class="nav-text">الإعدادات</span></a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit"><span class="nav-icon" aria-hidden="true">🚪</span><span class="nav-text">تسجيل الخروج</span></button>
                </form>
                @include('shared.language-switcher')
            </nav>
        </aside>

        <main>
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="errors">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <div class="modal-backdrop" id="adminModal" onclick="closeAdminModal(event)">
        <div class="modal" role="dialog" aria-modal="true" onclick="event.stopPropagation()">
            <div class="modal-head">
                <h2 id="adminModalTitle">تعديل</h2>
                <button class="modal-close" type="button" onclick="closeAdminModal()">إغلاق</button>
            </div>
            <div class="modal-body" id="adminModalBody"></div>
        </div>
    </div>

    <script>
        function toggleAdminHeader(button, event) {
            event?.stopPropagation();
            const sidebar = button.closest('aside');
            const isOpen = sidebar.classList.toggle('menu-open');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        document.addEventListener('click', (event) => {
            const sidebar = document.querySelector('aside.menu-open');
            if (!sidebar || sidebar.contains(event.target)) return;

            sidebar.classList.remove('menu-open');
            sidebar.querySelector('.mobile-menu-toggle')?.setAttribute('aria-expanded', 'false');
        });

        function openAdminModal(title, templateId) {
            const modal = document.getElementById('adminModal');
            const body = document.getElementById('adminModalBody');
            const template = document.getElementById(templateId);

            document.getElementById('adminModalTitle').textContent = title;
            body.innerHTML = '';
            body.appendChild(template.content.cloneNode(true));
            localizeDateTimes(body);
            bindEnglishNumberWarnings(body);
            modal.classList.add('active');
            markVisibleOrdersAsOpened(body);
        }

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

        function markVisibleOrdersAsOpened(root) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;

            root.querySelectorAll('[data-open-order-url]').forEach((orderElement) => {
                if (orderElement.dataset.openedSent === 'true') return;

                orderElement.dataset.openedSent = 'true';

                fetch(orderElement.dataset.openOrderUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                }).catch(() => {});

                const orderId = orderElement.dataset.orderId;
                document.querySelectorAll(`[data-order-id="${orderId}"] [data-order-status-dot]`).forEach((dot) => {
                    if (dot.classList.contains('green')) return;
                    dot.classList.remove('red');
                    dot.classList.add('yellow');
                });
            });
        }

        function closeAdminModal(event) {
            if (event && event.target.id !== 'adminModal') return;
            document.getElementById('adminModal').classList.remove('active');
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

        function toggleInlinePasswordPanel(button) {
            const panel = button.closest('.form-section')?.querySelector('.inline-password-panel');
            if (!panel) return;

            panel.style.display = panel.style.display === 'none' ? 'grid' : 'none';
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAdminModal();
        });

        let activeSearchRequest = null;

        function bindAutoSearchForms(root = document) {
            root.querySelectorAll('.auto-search-form').forEach((form) => {
                if (form.dataset.searchBound === 'true') return;

                const input = form.querySelector('input[name="search"]');
                let timeoutId;

                const runSearch = () => {
                    if (!input) return;

                    const caretPosition = input.selectionStart ?? input.value.length;
                    const params = new URLSearchParams(new FormData(form));
                    const url = `${form.action}?${params.toString()}`;

                    activeSearchRequest?.abort();
                    activeSearchRequest = new AbortController();

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: activeSearchRequest.signal,
                    })
                        .then((response) => response.text())
                        .then((html) => {
                            const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                            const nextMain = nextDocument.querySelector('main');
                            const currentMain = document.querySelector('main');

                            if (!nextMain || !currentMain) {
                                window.location.href = url;
                                return;
                            }

                            currentMain.innerHTML = nextMain.innerHTML;
                            window.history.replaceState({}, '', url);
                            localizeDateTimes(currentMain);
                            bindAutoSearchForms(currentMain);
                            bindEnglishNumberWarnings(currentMain);

                            const nextInput = currentMain.querySelector('.auto-search-form input[name="search"]');
                            if (nextInput) {
                                nextInput.focus();
                                const nextCaretPosition = Math.min(caretPosition, nextInput.value.length);
                                nextInput.setSelectionRange(nextCaretPosition, nextCaretPosition);
                            }
                        })
                        .catch((error) => {
                            if (error.name !== 'AbortError') form.submit();
                        });
                };

                input?.addEventListener('input', () => {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(runSearch, 450);
                });

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    clearTimeout(timeoutId);
                    runSearch();
                });

                form.dataset.searchBound = 'true';
            });
        }

        function bindEnglishNumberWarnings(root = document) {
            const rules = [
                { selector: 'input[name="phone"]', pattern: /^05[0-9]{8}$/, message: 'تنبيه: رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام إنجليزية فقط.' },
                { selector: 'input[name="password"], input[name="password_confirmation"]', pattern: /^[A-Za-z0-9]+$/, message: 'تنبيه: كلمة المرور تقبل حروف وأرقام إنجليزية فقط.' },
                { selector: 'input[name="postal_code"], input[name="card_cvc"], input[name="pages"], input[name="discount_amount"], #researchPages, .copies-input', pattern: /^[0-9]+$/, message: 'تنبيه: لا يقبل هذا الحقل إلا الأرقام الإنجليزية فقط 0-9.' },
                { selector: 'input[name="card_number"]', pattern: /^[0-9 ]+$/, message: 'تنبيه: رقم البطاقة يقبل الأرقام الإنجليزية والمسافات فقط.' },
                { selector: 'input[name="card_expiry"]', pattern: /^(0[1-9]|1[0-2])\/[0-9]{2}$/, message: 'تنبيه: اكتب تاريخ الانتهاء بالأرقام الإنجليزية بصيغة MM/YY.' },
                { selector: 'input[name="email"]', pattern: /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/, message: 'تنبيه: اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com.' },
                { selector: 'input[name="discount_code"]', pattern: /^[A-Za-z0-9_-]+$/, message: 'تنبيه: كود الخصم يقبل حروف وأرقام إنجليزية فقط.' },
            ];
            const selector = rules.map((rule) => rule.selector).join(', ');
            root.querySelectorAll(selector).forEach((input) => {
                if (input.dataset.englishNumberBound === 'true') return;

                const showWarning = () => {
                    const rule = rules.find((item) => input.matches(item.selector));
                    if (!rule) return;

                    let warning = input.nextElementSibling;
                    if (!warning || !warning.classList.contains('english-number-warning')) {
                        warning = document.createElement('div');
                        warning.className = 'english-number-warning';
                        input.insertAdjacentElement('afterend', warning);
                    }

                    const invalid = input.value !== '' && !rule.pattern.test(input.value);
                    warning.textContent = rule.message;
                    warning.classList.toggle('active', invalid);
                    input.setCustomValidity(invalid ? rule.message : '');
                };

                input.addEventListener('input', showWarning);
                showWarning();
                input.dataset.englishNumberBound = 'true';
            });
        }

        localizeDateTimes();
        bindAutoSearchForms();
        bindEnglishNumberWarnings();

        document.addEventListener('click', (event) => {
            const link = event.target.closest('[data-complete-order-download]');
            if (!link) return;

            const orderElement = link.closest('[data-order-id]');
            const orderId = orderElement?.dataset.orderId;
            if (!orderId || orderElement?.dataset.orderPaid !== '1') return;

            document.querySelectorAll(`[data-order-id="${orderId}"] [data-order-status-dot]`).forEach((dot) => {
                dot.classList.remove('red', 'yellow');
                dot.classList.add('green');
            });
        });
    </script>
    @include('shared.chat-widget')
    @include('shared.language-tools')
</body>
</html>
