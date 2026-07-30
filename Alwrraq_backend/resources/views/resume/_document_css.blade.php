@font-face{font-family:Tajawal;src:url('/fonts/tajawal/Tajawal-Regular.ttf') format('truetype');font-style:normal;font-weight:400;font-display:swap}
@font-face{font-family:Tajawal;src:url('/fonts/tajawal/Tajawal-Bold.ttf') format('truetype');font-style:normal;font-weight:700 900;font-display:swap}
.cv-sheet{position:relative;width:794px;max-width:100%;min-height:1123px;margin:0 auto;background:#fff;color:#172033;box-shadow:0 18px 55px rgba(15,23,42,.24);overflow:hidden;font-family:Tajawal,Arial,sans-serif}
.cv-layout{display:grid;grid-template-columns:30% 70%;min-height:1123px}
.cv-side{background:#456f9f;color:#fff;padding:38px 25px;min-width:0}
.cv-main{display:flex;flex-direction:column;padding:43px 38px;min-width:0}
.cv-profile{display:flex;justify-content:center;margin-bottom:28px}
.cv-profile{order:-2}.cv-personal-section{order:-1}
.cv-photo{display:grid;place-items:center;width:148px;height:148px;border-radius:50%;object-fit:cover;border:5px solid rgba(255,255,255,.92);background:#dbeafe;color:#456f9f;font-size:48px;font-weight:900;box-shadow:0 10px 25px rgba(15,23,42,.22)}
.cv-personal-identity{margin:0 0 18px;text-align:center;overflow-wrap:anywhere}
.cv-personal-identity h1{margin:0;color:inherit;font-size:24px;line-height:1.4;font-weight:900}
.cv-personal-identity .cv-job{margin-top:5px;color:inherit;font-size:15px;line-height:1.5;opacity:.9}
.cv-heading{padding-bottom:24px;margin-bottom:25px;border-bottom:2px solid #d9dee5}
.cv-heading h1{margin:0;color:#172033;font-size:34px;line-height:1.35;font-weight:900;letter-spacing:-.4px}
.cv-job{margin-top:7px;color:#456f9f;font-size:20px;font-weight:800}
.cv-section{break-inside:avoid;margin:0 0 25px}
.cv-section h3{display:flex;align-items:center;gap:9px;margin:0 0 13px;color:#243449;font-size:21px;line-height:1.4;font-weight:900}
.cv-section-mark{width:27px;height:27px;flex:0 0 27px;border:2px solid currentColor;border-radius:50%;position:relative}
.cv-section-mark:after{content:"";position:absolute;inset:7px;border-radius:50%;background:currentColor}
.cv-side .cv-section h3{color:#fff;font-size:19px;border-bottom:1px solid rgba(255,255,255,.34);padding-bottom:8px}
.cv-side .cv-section-mark{display:none}
.cv-personal-list{display:grid;gap:13px}
.cv-personal-row{display:grid;grid-template-columns:25px minmax(0,1fr);align-items:start;gap:9px}
.cv-personal-icon{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;font-size:10px;font-weight:900}
.cv-personal-row strong{display:block;margin-bottom:2px;font-size:12px;line-height:1.35;color:#fff}
.cv-personal-row span:not(.cv-personal-icon){display:block;font-size:13px;line-height:1.55;color:#eef6ff;overflow-wrap:anywhere}
.cv-personal-row-email span:not(.cv-personal-icon){direction:ltr;text-align:left;font-size:.88em;word-break:break-all}
.cv-section-items{display:grid;gap:16px}
.cv-item{break-inside:avoid;position:relative}
.cv-main .cv-item{padding-inline-start:21px}
.cv-main .cv-item:before{content:"";position:absolute;inset-inline-start:2px;top:7px;width:7px;height:7px;border:2px solid #456f9f;border-radius:50%;background:#fff;z-index:1}
.cv-main .cv-item:not(:last-child):after{content:"";position:absolute;inset-inline-start:6px;top:18px;bottom:-18px;width:1px;background:#aab7c7}
.cv-item-heading{display:flex;align-items:baseline;justify-content:space-between;gap:12px;min-width:0;flex-wrap:wrap}
.cv-item-heading strong{color:inherit;font-size:17px;line-height:1.55;min-width:0;overflow-wrap:anywhere}
.cv-item-heading time{flex:0 0 auto;color:#526174;font-size:12px;font-weight:800}
.cv-organization{display:block;margin-top:2px;color:#456f9f;font-size:14px;font-weight:800}
.cv-side .cv-organization,.cv-side .cv-item small{color:#dcecff}
.cv-item-meta{display:flex;flex-wrap:wrap;gap:4px 12px;margin-top:5px;color:#64748b;font-size:12px;line-height:1.55}
.cv-body{margin-top:6px;color:#425168;font-size:13.5px;line-height:1.75;white-space:pre-line;overflow-wrap:anywhere}
.cv-side .cv-body{color:#f0f7ff}
.cv-link{margin-top:4px;color:#456f9f;font-size:11px;overflow-wrap:anywhere}
.cv-level{display:inline-flex;padding:3px 7px;border-radius:999px;background:rgba(255,255,255,.15);font-size:11px;font-weight:800}
.content-sparse .cv-main,.content-sparse .cv-side{justify-content:space-evenly}
.content-sparse .cv-heading h1{font-size:42px}
.content-sparse .cv-job{font-size:24px}
.content-sparse .cv-section h3{font-size:24px}
.content-sparse .cv-item-heading strong{font-size:19px}
.content-sparse .cv-body{font-size:15px;line-height:1.9}
.content-sparse .cv-personal-row strong{font-size:14px}
.content-sparse .cv-personal-row span:not(.cv-personal-icon){font-size:14px}
.content-sparse .cv-personal-row-email span:not(.cv-personal-icon){font-size:11px}
.content-sparse .cv-personal-identity h1{font-size:29px}.content-sparse .cv-personal-identity .cv-job{font-size:18px}
.content-dense .cv-side{padding:28px 20px}.content-dense .cv-main{padding:30px 28px}.content-dense .cv-section{margin-bottom:17px}.content-dense .cv-section-items{gap:10px}.content-dense .cv-body{font-size:11px;line-height:1.55}.content-dense .cv-item-heading strong{font-size:14px}
.content-dense .cv-personal-identity h1{font-size:19px}.content-dense .cv-personal-identity .cv-job{font-size:12px}
.cv-personal-identity h1.cv-name-long{font-size:17px!important;line-height:1.45;white-space:nowrap;letter-spacing:-.35px}
.cv-personal-identity h1.cv-name-extra-long{font-size:14px!important;line-height:1.45;white-space:nowrap;letter-spacing:-.55px}

/* 1: Reference-inspired professional timeline with a full-height profile rail. */
.template-executive_classic .cv-side{background:#456f9f}
.template-executive_classic .cv-personal-identity{text-align:center}
.template-executive_classic .cv-personal-identity h1{font-size:25px}
.template-executive_classic .cv-main .cv-section h3{justify-content:flex-start;border-bottom:1px solid #d9dee5;padding-bottom:10px}

/* 2: Editorial ivory layout with a centered masthead and gold framing. */
.template-royal_gold{background:#fffdf8;border:10px solid #c5a55b}
.template-royal_gold .cv-layout{display:flex;flex-direction:column;padding:35px 46px;min-height:1103px}
.template-royal_gold .cv-main{order:2;padding:0;display:block}
.template-royal_gold .cv-side{order:1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 36px;padding:24px 0;margin-bottom:18px;background:transparent;color:#29241d;border-bottom:1px solid #d6c294}
.template-royal_gold .cv-profile{position:absolute;top:43px;inset-inline-end:48px;margin:0}
.template-royal_gold .cv-photo{width:112px;height:112px;border:3px solid #c5a55b;background:#f4ead2;color:#866a2d}
.template-royal_gold .cv-personal-section{grid-column:1/-1;min-height:112px;padding-inline-end:135px}
.template-royal_gold .cv-personal-identity{text-align:start;color:#29241d}
.template-royal_gold .cv-personal-identity h1{font-family:Tajawal,Arial,sans-serif;font-size:36px}
.template-royal_gold .cv-personal-identity .cv-job{font-size:18px;color:#9a7628}
.template-royal_gold .cv-heading{padding:12px 0 25px;text-align:center;border-bottom:3px double #c5a55b}
.template-royal_gold .cv-heading h1{font-family:Tajawal,Arial,sans-serif;font-size:38px;color:#29241d}
.template-royal_gold .cv-job,.template-royal_gold .cv-organization,.template-royal_gold .cv-link{color:#9a7628}
.template-royal_gold .cv-main .cv-section{padding:15px 4px}
.template-royal_gold .cv-main .cv-section h3{justify-content:center;font-family:Tajawal,Arial,sans-serif;color:#73591f}
.template-royal_gold .cv-side .cv-section h3{color:#73591f;border-color:#d6c294}
.template-royal_gold .cv-personal-row strong,.template-royal_gold .cv-personal-row span:not(.cv-personal-icon),.template-royal_gold .cv-side .cv-body,.template-royal_gold .cv-side .cv-item small{color:#3f3729}
.template-royal_gold .cv-personal-icon{background:#c5a55b;color:#fff}
.template-royal_gold .cv-level{background:#efe3c7}

/* 3: Contemporary dark masthead with horizontal profile information. */
.template-midnight_luxury .cv-layout{display:flex;flex-direction:column;min-height:1123px}
.template-midnight_luxury .cv-side{display:grid;grid-template-columns:150px minmax(0,1.2fr) minmax(0,.8fr);gap:25px;align-items:start;padding:34px 42px;background:#07152d;color:#fff;border-bottom:7px solid #d1aa54}
.template-midnight_luxury .cv-profile{margin:0}
.template-midnight_luxury .cv-photo{width:128px;height:128px;border-color:#d1aa54;background:#172a49;color:#d1aa54}
.template-midnight_luxury .cv-personal-section{margin:0}
.template-midnight_luxury .cv-personal-identity{text-align:start}
.template-midnight_luxury .cv-personal-identity h1{font-size:32px;color:#fff}.template-midnight_luxury .cv-personal-identity .cv-job{font-size:18px;color:#d9bd7b}
.template-midnight_luxury .cv-side>.cv-section:not(.cv-personal-section){margin:0}
.template-midnight_luxury .cv-side .cv-section h3{color:#d9bd7b;border-color:#40506b}
.template-midnight_luxury .cv-main{flex:1;padding:38px 52px;background:#fff}
.template-midnight_luxury .cv-heading{text-align:center;border-bottom:0;margin-bottom:14px}
.template-midnight_luxury .cv-heading h1{font-size:39px;color:#07152d}
.template-midnight_luxury .cv-job,.template-midnight_luxury .cv-organization,.template-midnight_luxury .cv-link{color:#9a772c}
.template-midnight_luxury .cv-main .cv-section h3{color:#07152d;border-inline-start:5px solid #d1aa54;padding-inline-start:10px}
.template-midnight_luxury .cv-main .cv-section h3 .cv-section-mark{display:none}

/* 4: Asymmetric emerald layout with the profile column on the opposite side. */
.template-emerald_signature{background:#f7faf8}
.template-emerald_signature .cv-layout{grid-template-columns:68% 32%;direction:ltr}
.template-emerald_signature .cv-side{grid-column:2;grid-row:1;direction:rtl;background:#0b5d4b;padding:48px 27px;border-radius:80px 0 0 0}
.template-emerald_signature[dir=ltr] .cv-side{direction:ltr}
.template-emerald_signature .cv-main{grid-column:1;grid-row:1;direction:rtl;padding:48px 42px}
.template-emerald_signature[dir=ltr] .cv-main{direction:ltr}
.template-emerald_signature .cv-photo{border-radius:18px;transform:rotate(2deg);border-color:#bde9d8}
.template-emerald_signature .cv-heading{border-bottom:0;padding:20px 0 28px}
.template-emerald_signature .cv-personal-identity{padding:11px 8px;border-radius:12px;background:rgba(255,255,255,.1);text-align:center}
.template-emerald_signature .cv-personal-identity h1{font-size:23px}
.template-emerald_signature .cv-heading h1{color:#123b33}
.template-emerald_signature .cv-job,.template-emerald_signature .cv-organization,.template-emerald_signature .cv-link{color:#0b8065}
.template-emerald_signature .cv-main .cv-section{padding:17px 19px;background:#fff;border-radius:14px;box-shadow:0 8px 22px rgba(9,83,65,.08)}
.template-emerald_signature .cv-main .cv-section h3{color:#0b5d4b}

/* 5: Clean single-column portfolio layout with a compact contact ribbon. */
.template-modern_silk .cv-layout{display:flex;flex-direction:column;min-height:1123px;background:#fff}
.template-modern_silk .cv-side{display:grid;grid-template-columns:115px minmax(0,1fr);gap:20px;padding:28px 45px;background:#f2effc;color:#29224b;border-top:17px solid #6551a5;order:0}
.template-modern_silk .cv-profile{margin:0;grid-row:1/3}
.template-modern_silk .cv-photo{width:105px;height:105px;border-radius:12px;border-color:#fff;background:#ddd6fe;color:#6551a5}
.template-modern_silk .cv-personal-section{margin:0}
.template-modern_silk .cv-personal-identity{text-align:start;color:#29224b}
.template-modern_silk .cv-personal-identity h1{font-size:31px}.template-modern_silk .cv-personal-identity .cv-job{font-size:17px;color:#6551a5}
.template-modern_silk .cv-personal-list{grid-template-columns:repeat(3,minmax(0,1fr));gap:8px 16px}
.template-modern_silk .cv-personal-row{grid-template-columns:19px minmax(0,1fr)}
.template-modern_silk .cv-personal-icon{width:18px;height:18px;background:#6551a5}
.template-modern_silk .cv-personal-row strong,.template-modern_silk .cv-personal-row span:not(.cv-personal-icon),.template-modern_silk .cv-side .cv-body,.template-modern_silk .cv-side .cv-item small{color:#3c355a}
.template-modern_silk .cv-side>.cv-section:not(.cv-personal-section){grid-column:2;display:inline-block;margin:0}
.template-modern_silk .cv-side .cv-section h3{color:#6551a5;border-color:#d8d0ee}
.template-modern_silk .cv-level{background:#ddd6fe}
.template-modern_silk .cv-main{order:1;padding:40px 52px}
.template-modern_silk .cv-heading{padding:0 0 27px;border-bottom:6px solid #6551a5}
.template-modern_silk .cv-heading h1{font-size:40px;color:#29224b}
.template-modern_silk .cv-job,.template-modern_silk .cv-organization,.template-modern_silk .cv-link{color:#6551a5}
.template-modern_silk .cv-main .cv-section h3{color:#514184}

@media(max-width:850px){
    .cv-sheet{width:100%;min-height:calc((100vw - 16px)*1.414)}
    .cv-layout{min-height:inherit}
    .cv-side{padding:4.4vw 3vw}.cv-main{padding:5vw 4vw}
    .cv-photo{width:17vw;height:17vw;border-width:3px}.cv-heading h1{font-size:4.8vw}.cv-job{font-size:2.8vw}
    .cv-personal-identity{margin-bottom:2vw}.cv-personal-identity h1{font-size:3.1vw}.cv-personal-identity .cv-job{font-size:2vw}
    .cv-heading{padding-bottom:3vw;margin-bottom:3vw}.cv-section{margin-bottom:3vw}.cv-section h3{font-size:2.8vw;margin-bottom:1.5vw}.cv-section-mark{width:3.2vw;height:3.2vw;flex-basis:3.2vw}
    .cv-item-heading strong{font-size:2.35vw}.cv-item-heading time,.cv-organization,.cv-item-meta{font-size:1.9vw}.cv-body{font-size:2.05vw}.cv-personal-row strong{font-size:1.85vw}.cv-personal-row span:not(.cv-personal-icon){font-size:2vw}
    .template-executive_classic .cv-personal-identity h1{font-size:3.2vw}
    .template-royal_gold .cv-layout{padding:4vw 5vw}.template-royal_gold .cv-profile{top:5vw;inset-inline-end:6vw}.template-royal_gold .cv-personal-section{min-height:16vw;padding-inline-end:18vw}.template-royal_gold .cv-heading{padding-inline:0}
    .template-royal_gold .cv-personal-identity h1{font-size:4.4vw}.template-royal_gold .cv-personal-identity .cv-job{font-size:2.3vw}
    .template-midnight_luxury .cv-side{grid-template-columns:16vw minmax(0,1.2fr) minmax(0,.8fr);padding:4vw 5vw}.template-midnight_luxury .cv-photo{width:14vw;height:14vw}.template-midnight_luxury .cv-main{padding:4vw 6vw}
    .template-midnight_luxury .cv-personal-identity h1{font-size:4vw}.template-midnight_luxury .cv-personal-identity .cv-job{font-size:2.2vw}
    .template-emerald_signature .cv-side{padding:5vw 3vw}.template-emerald_signature .cv-main{padding:5vw 4vw}
    .template-emerald_signature .cv-personal-identity h1{font-size:3vw}
    .template-modern_silk .cv-side{grid-template-columns:13vw minmax(0,1fr);padding:3vw 5vw}.template-modern_silk .cv-personal-list{grid-template-columns:repeat(2,minmax(0,1fr))}.template-modern_silk .cv-photo{width:12vw;height:12vw}.template-modern_silk .cv-main{padding:4vw 6vw}
    .template-modern_silk .cv-personal-identity h1{font-size:3.8vw}.template-modern_silk .cv-personal-identity .cv-job{font-size:2.1vw}
}
@media(max-width:520px){
    .cv-sheet{box-shadow:none}
    .cv-layout{grid-template-columns:34% 66%}
    .cv-side{padding:4vw 2.4vw}.cv-main{padding:4.5vw 3.2vw}
    .cv-profile{margin-bottom:3vw}.cv-personal-list{gap:1.7vw}.cv-personal-row{grid-template-columns:4vw minmax(0,1fr);gap:1.2vw}.cv-personal-icon{width:4vw;height:4vw;font-size:1.8vw}
    .cv-personal-identity h1{font-size:3.4vw}.cv-personal-identity .cv-job{font-size:2.2vw}
    .cv-item-heading{gap:1vw}.cv-main .cv-item{padding-inline-start:3vw}.cv-main .cv-item:before{inset-inline-start:.2vw}.cv-main .cv-item:not(:last-child):after{inset-inline-start:1vw}
    .cv-personal-row span:not(.cv-personal-icon),.cv-link,.cv-body,.cv-item-meta,.cv-organization,.cv-item-heading strong{overflow-wrap:anywhere;word-break:break-word}
    .template-royal_gold{border-width:1.2vw}.template-royal_gold .cv-layout{padding:3vw 4vw}.template-royal_gold .cv-profile{top:4vw;inset-inline-end:4vw}.template-royal_gold .cv-personal-section{min-height:17vw;padding-inline-end:18vw}.template-royal_gold .cv-heading{padding-inline:0}.template-royal_gold .cv-side{gap:2vw 3vw;padding-top:3vw}.template-royal_gold .cv-photo{width:13vw;height:13vw;border-width:2px}
    .template-midnight_luxury .cv-side{grid-template-columns:15vw minmax(0,1fr);gap:2.5vw;padding:3vw 4vw}.template-midnight_luxury .cv-profile{grid-row:1/3}.template-midnight_luxury .cv-side>.cv-section:not(.cv-personal-section){grid-column:2}.template-midnight_luxury .cv-main{padding:4vw}
    .template-emerald_signature .cv-layout{grid-template-columns:64% 36%}.template-emerald_signature .cv-side{padding:4vw 2.4vw;border-radius:10vw 0 0}.template-emerald_signature .cv-main{padding:4vw 3vw}.template-emerald_signature .cv-main .cv-section{padding:2.4vw}
    .template-modern_silk .cv-side{grid-template-columns:14vw minmax(0,1fr);gap:2vw;padding:3vw 4vw}.template-modern_silk .cv-personal-list{grid-template-columns:repeat(2,minmax(0,1fr));gap:1vw 2vw}.template-modern_silk .cv-main{padding:4vw}
}
