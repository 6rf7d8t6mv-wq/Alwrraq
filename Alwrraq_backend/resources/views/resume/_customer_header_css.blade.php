:root{--sidebar-width:clamp(180px,20vw,240px);--page-gap:clamp(14px,3vw,40px)}
body{padding:0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap)}
.page-header{width:var(--sidebar-width);min-height:100vh;max-height:100vh;overflow-y:auto;background:#0f172a;color:#f8fafc;padding:clamp(16px,2vw,24px) clamp(12px,1.6vw,18px);box-shadow:-10px 0 30px rgba(15,23,42,.15);position:fixed;top:0;right:0;z-index:80}
.header-inner{height:100%;display:flex;flex-direction:column;justify-content:flex-start;align-items:stretch;gap:0}
.header-brand .brand{font-size:clamp(18px,2vw,24px);font-weight:700;letter-spacing:.02em;overflow-wrap:anywhere;margin-bottom:4px}
.brand-logo{width:46px;height:46px;border-radius:14px;object-fit:cover;background:#fff;border:1px solid rgba(255,255,255,.18);box-shadow:0 12px 26px rgba(0,0,0,.18);margin-bottom:10px;display:block}
.brand-subtitle{margin:4px 0 0;color:#cbd5e1;font-size:clamp(11px,1.2vw,14px)}
.header-identity{display:grid;gap:2px;margin-top:16px;color:#f8fafc;font-size:clamp(12px,1.15vw,14px);line-height:1.5}
.header-identity small{color:#cbd5e1}
.header-actions{display:flex;flex-direction:column;align-items:stretch;gap:clamp(8px,1.2vw,12px);color:#cbd5e1;font-size:clamp(12px,1.15vw,14px);margin-top:24px}
.header-actions a{color:#f8fafc;text-decoration:none}
.header-link{display:flex;align-items:center;gap:8px;width:100%;padding:10px 12px;border-radius:10px;background:rgba(255,255,255,.06);box-sizing:border-box;white-space:normal;line-height:1.5;border:1px solid transparent;position:relative;font-weight:800}
.header-link:hover{background:#1e293b;border-color:#334155}
.header-link.settings-link{background:#0f4c81;border-color:rgba(96,165,250,.35)}
.header-link.settings-link:hover{background:#1d6fa5;border-color:#60a5fa}
.customer-notice-dot{position:absolute;top:8px;left:9px;width:7px;height:7px;border-radius:999px;background:#dc2626;box-shadow:0 0 0 2px rgba(15,23,42,.95)}
.header-form{margin:0}.logout-button{width:100%;background:#b91c1c;color:#f8fafc;border:1px solid rgba(248,113,113,.5);border-radius:10px;padding:10px 12px;cursor:pointer;text-align:center;font-weight:800}.logout-button:hover{background:#dc2626;border-color:#f87171}
@media(max-width:768px){
    :root{--sidebar-width:0px;--page-gap:10px}
    body{padding:0}
    .page-header{position:sticky;top:0;width:100%;min-height:0;max-height:none;padding:8px 10px;box-shadow:0 10px 24px rgba(15,23,42,.16)}
    .header-inner{height:auto;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:8px}
    .header-inner>div:first-child{display:flex;align-items:center;gap:8px;min-width:0}
    .brand-logo{width:34px;height:34px;border-radius:10px;margin:0;flex:0 0 auto}
    .header-brand .brand{margin:0;font-size:17px;line-height:1.2;white-space:nowrap}
    .brand-subtitle{display:none}
    .header-identity{margin:0;text-align:end;font-size:11px}
    .header-actions{grid-column:1/-1;margin-top:0;display:none;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .header-form{margin:0}.header-link,.logout-button{padding:9px 8px}
}
