.cv-sheet{position:relative;width:794px;max-width:100%;min-height:1123px;margin:0 auto;background:#fff;color:#172033;box-shadow:0 18px 55px rgba(15,23,42,.24);overflow:hidden;font-family:Arial,sans-serif}
.cv-layout{display:grid;grid-template-columns:30% 70%;min-height:1123px}
.cv-side{background:#456f9f;color:#fff;padding:38px 25px;min-width:0}
.cv-main{display:flex;flex-direction:column;padding:43px 38px;min-width:0}
.cv-profile{display:flex;justify-content:center;margin-bottom:28px}
.cv-photo{display:grid;place-items:center;width:148px;height:148px;border-radius:50%;object-fit:cover;border:5px solid rgba(255,255,255,.92);background:#dbeafe;color:#456f9f;font-size:48px;font-weight:900;box-shadow:0 10px 25px rgba(15,23,42,.22)}
.cv-heading{padding-bottom:24px;margin-bottom:25px;border-bottom:2px solid #d9dee5}
.cv-heading h1{margin:0;color:#172033;font-size:34px;line-height:1.35;font-weight:900;letter-spacing:-.4px}
.cv-job{margin-top:7px;color:#456f9f;font-size:17px;font-weight:800}
.cv-section{break-inside:avoid;margin:0 0 25px}
.cv-section h3{display:flex;align-items:center;gap:9px;margin:0 0 13px;color:#243449;font-size:18px;line-height:1.4;font-weight:900}
.cv-section-mark{width:27px;height:27px;flex:0 0 27px;border:2px solid currentColor;border-radius:50%;position:relative}
.cv-section-mark:after{content:"";position:absolute;inset:7px;border-radius:50%;background:currentColor}
.cv-side .cv-section h3{color:#fff;font-size:16px;border-bottom:1px solid rgba(255,255,255,.34);padding-bottom:8px}
.cv-side .cv-section-mark{display:none}
.cv-personal-list{display:grid;gap:13px}
.cv-personal-row{display:grid;grid-template-columns:25px minmax(0,1fr);align-items:start;gap:9px}
.cv-personal-icon{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;font-size:10px;font-weight:900}
.cv-personal-row strong{display:block;margin-bottom:2px;font-size:10px;line-height:1.35;color:#fff}
.cv-personal-row span:not(.cv-personal-icon){display:block;font-size:10px;line-height:1.55;color:#eef6ff;overflow-wrap:anywhere}
.cv-section-items{display:grid;gap:16px}
.cv-item{break-inside:avoid;position:relative}
.cv-main .cv-item{padding-inline-start:21px}
.cv-main .cv-item:before{content:"";position:absolute;inset-inline-start:2px;top:7px;width:7px;height:7px;border:2px solid #456f9f;border-radius:50%;background:#fff;z-index:1}
.cv-main .cv-item:not(:last-child):after{content:"";position:absolute;inset-inline-start:6px;top:18px;bottom:-18px;width:1px;background:#aab7c7}
.cv-item-heading{display:flex;align-items:baseline;justify-content:space-between;gap:12px}
.cv-item-heading strong{color:inherit;font-size:14px;line-height:1.55}
.cv-item-heading time{flex:0 0 auto;color:#526174;font-size:10px;font-weight:800}
.cv-organization{display:block;margin-top:2px;color:#456f9f;font-size:11px;font-weight:800}
.cv-side .cv-organization,.cv-side .cv-item small{color:#dcecff}
.cv-item-meta{display:flex;flex-wrap:wrap;gap:4px 12px;margin-top:5px;color:#64748b;font-size:10px;line-height:1.55}
.cv-body{margin-top:6px;color:#425168;font-size:10.5px;line-height:1.75;white-space:pre-line;overflow-wrap:anywhere}
.cv-side .cv-body{color:#f0f7ff}
.cv-link{margin-top:4px;color:#456f9f;font-size:9px;overflow-wrap:anywhere}
.cv-level{display:inline-flex;padding:3px 7px;border-radius:999px;background:rgba(255,255,255,.15);font-size:9px;font-weight:800}
.content-sparse .cv-main,.content-sparse .cv-side{justify-content:space-evenly}
.content-sparse .cv-heading h1{font-size:42px}
.content-sparse .cv-job{font-size:21px}
.content-sparse .cv-section h3{font-size:21px}
.content-sparse .cv-item-heading strong{font-size:16px}
.content-sparse .cv-body{font-size:12.5px;line-height:1.9}
.content-sparse .cv-personal-row strong{font-size:12px}
.content-sparse .cv-personal-row span:not(.cv-personal-icon){font-size:12px}
.content-dense .cv-side{padding:28px 20px}.content-dense .cv-main{padding:30px 28px}.content-dense .cv-section{margin-bottom:17px}.content-dense .cv-section-items{gap:10px}.content-dense .cv-body{font-size:9px;line-height:1.55}.content-dense .cv-item-heading strong{font-size:12px}

/* 1: Reference-inspired professional timeline with a full-height profile rail. */
.template-executive_classic .cv-side{background:#456f9f}
.template-executive_classic .cv-main .cv-section h3{justify-content:flex-start;border-bottom:1px solid #d9dee5;padding-bottom:10px}

/* 2: Editorial ivory layout with a centered masthead and gold framing. */
.template-royal_gold{background:#fffdf8;border:10px solid #c5a55b}
.template-royal_gold .cv-layout{display:flex;flex-direction:column;padding:35px 46px;min-height:1103px}
.template-royal_gold .cv-main{order:1;padding:0;display:block}
.template-royal_gold .cv-side{order:2;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 36px;padding:24px 0 0;margin-top:8px;background:transparent;color:#29241d;border-top:1px solid #d6c294}
.template-royal_gold .cv-profile{position:absolute;top:43px;inset-inline-end:48px;margin:0}
.template-royal_gold .cv-photo{width:112px;height:112px;border:3px solid #c5a55b;background:#f4ead2;color:#866a2d}
.template-royal_gold .cv-heading{min-height:125px;padding:12px 135px 25px 0;text-align:center;border-bottom:3px double #c5a55b}
.template-royal_gold[dir=ltr] .cv-heading{padding:12px 0 25px 135px}
.template-royal_gold .cv-heading h1{font-family:Georgia,"Times New Roman",serif;font-size:38px;color:#29241d}
.template-royal_gold .cv-job,.template-royal_gold .cv-organization,.template-royal_gold .cv-link{color:#9a7628}
.template-royal_gold .cv-main .cv-section{padding:15px 4px}
.template-royal_gold .cv-main .cv-section h3{justify-content:center;font-family:Georgia,"Times New Roman",serif;color:#73591f}
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
.template-emerald_signature .cv-heading h1{color:#123b33}
.template-emerald_signature .cv-job,.template-emerald_signature .cv-organization,.template-emerald_signature .cv-link{color:#0b8065}
.template-emerald_signature .cv-main .cv-section{padding:17px 19px;background:#fff;border-radius:14px;box-shadow:0 8px 22px rgba(9,83,65,.08)}
.template-emerald_signature .cv-main .cv-section h3{color:#0b5d4b}

/* 5: Clean single-column portfolio layout with a compact contact ribbon. */
.template-modern_silk .cv-layout{display:flex;flex-direction:column;min-height:1123px;background:#fff}
.template-modern_silk .cv-side{display:grid;grid-template-columns:115px minmax(0,1fr);gap:20px;padding:28px 45px;background:#f2effc;color:#29224b;border-top:17px solid #6551a5;order:1}
.template-modern_silk .cv-profile{margin:0;grid-row:1/3}
.template-modern_silk .cv-photo{width:105px;height:105px;border-radius:12px;border-color:#fff;background:#ddd6fe;color:#6551a5}
.template-modern_silk .cv-personal-section{margin:0}
.template-modern_silk .cv-personal-list{grid-template-columns:repeat(3,minmax(0,1fr));gap:8px 16px}
.template-modern_silk .cv-personal-row{grid-template-columns:19px minmax(0,1fr)}
.template-modern_silk .cv-personal-icon{width:18px;height:18px;background:#6551a5}
.template-modern_silk .cv-personal-row strong,.template-modern_silk .cv-personal-row span:not(.cv-personal-icon),.template-modern_silk .cv-side .cv-body,.template-modern_silk .cv-side .cv-item small{color:#3c355a}
.template-modern_silk .cv-side>.cv-section:not(.cv-personal-section){grid-column:2;display:inline-block;margin:0}
.template-modern_silk .cv-side .cv-section h3{color:#6551a5;border-color:#d8d0ee}
.template-modern_silk .cv-level{background:#ddd6fe}
.template-modern_silk .cv-main{order:0;padding:40px 52px}
.template-modern_silk .cv-heading{padding:0 0 27px;border-bottom:6px solid #6551a5}
.template-modern_silk .cv-heading h1{font-size:40px;color:#29224b}
.template-modern_silk .cv-job,.template-modern_silk .cv-organization,.template-modern_silk .cv-link{color:#6551a5}
.template-modern_silk .cv-main .cv-section h3{color:#514184}

@media(max-width:850px){
    .cv-sheet{width:100%;min-height:calc((100vw - 16px)*1.414)}
    .cv-layout{min-height:inherit}
    .cv-side{padding:4.4vw 3vw}.cv-main{padding:5vw 4vw}
    .cv-photo{width:17vw;height:17vw;border-width:3px}.cv-heading h1{font-size:4.2vw}.cv-job{font-size:2.3vw}
    .cv-heading{padding-bottom:3vw;margin-bottom:3vw}.cv-section{margin-bottom:3vw}.cv-section h3{font-size:2.25vw;margin-bottom:1.5vw}.cv-section-mark{width:3.2vw;height:3.2vw;flex-basis:3.2vw}
    .cv-item-heading strong{font-size:1.8vw}.cv-item-heading time,.cv-organization,.cv-item-meta,.cv-body{font-size:1.4vw}.cv-personal-row strong,.cv-personal-row span:not(.cv-personal-icon){font-size:1.35vw}
    .template-royal_gold .cv-layout{padding:4vw 5vw}.template-royal_gold .cv-profile{top:5vw;inset-inline-end:6vw}.template-royal_gold .cv-heading{min-height:16vw;padding-inline-end:18vw}.template-royal_gold[dir=ltr] .cv-heading{padding-inline-start:18vw;padding-inline-end:0}
    .template-midnight_luxury .cv-side{grid-template-columns:16vw minmax(0,1.2fr) minmax(0,.8fr);padding:4vw 5vw}.template-midnight_luxury .cv-photo{width:14vw;height:14vw}.template-midnight_luxury .cv-main{padding:4vw 6vw}
    .template-emerald_signature .cv-side{padding:5vw 3vw}.template-emerald_signature .cv-main{padding:5vw 4vw}
    .template-modern_silk .cv-side{grid-template-columns:13vw minmax(0,1fr);padding:3vw 5vw}.template-modern_silk .cv-photo{width:12vw;height:12vw}.template-modern_silk .cv-main{padding:4vw 6vw}
}
