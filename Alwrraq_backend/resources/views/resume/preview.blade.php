<!DOCTYPE html>
<html lang="{{ $draft->language }}" dir="{{ $draft->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('shared.tab-brand')
    <style>
        *{box-sizing:border-box}body{margin:0;background:#cbd5e1;color:#0f172a;font-family:Arial,sans-serif}.toolbar{position:sticky;top:0;z-index:30;display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;background:#0f172a;padding:12px}.toolbar a,.toolbar button{border:0;border-radius:9px;background:#0f4c81;color:#fff;text-decoration:none;padding:10px 14px;font:inherit;font-weight:900;cursor:pointer}.toolbar .download{background:#047857}.notice{color:#fff;font-weight:800}.stage{padding:20px;user-select:none;-webkit-user-select:none}.cv-sheet{position:relative;width:794px;min-height:1123px;margin:0 auto;background:#fff;box-shadow:0 18px 55px rgba(15,23,42,.28);overflow:hidden}.cv-layout{display:grid;grid-template-columns:31% 69%;min-height:1123px}.cv-side{background:#102a43;color:#fff;padding:34px 24px}.cv-main{padding:38px 34px}.cv-photo{display:block;width:132px;height:132px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.72);margin:0 auto 24px}.cv-main h1{font-size:31px;color:#102a43;margin:0}.cv-job{font-size:18px;color:#0f4c81;font-weight:800;margin:7px 0 20px}.cv-section{break-inside:avoid;margin:0 0 22px}.cv-section h3{font-size:16px;color:#0f4c81;border-bottom:2px solid #dbeafe;padding-bottom:6px;margin:0 0 10px}.cv-side .cv-section h3{color:#fff;border-color:#486581}.cv-item{margin-bottom:12px}.cv-item strong{display:block;font-size:13px}.cv-item small{display:block;color:#64748b;margin-top:3px}.cv-side .cv-item small{color:#cbd5e1}.cv-body,.cv-contact{font-size:12px;line-height:1.75;white-space:pre-line;overflow-wrap:anywhere}.cv-contact{margin-bottom:6px}.cv-watermark{position:absolute;inset:-120px;z-index:20;pointer-events:none;display:grid;grid-template-columns:repeat(3,1fr);align-content:space-around;transform:rotate(-18deg);opacity:.48}.cv-watermark span{font-size:34px;font-weight:900;color:#b91c1c;text-align:center;white-space:nowrap;text-shadow:0 1px #fff}.capture-guard{display:none;position:fixed;inset:0;background:#0f172a;z-index:999;color:#fff;align-items:center;justify-content:center;font-size:20px;font-weight:900}.capturing .capture-guard{display:flex}@media(max-width:850px){.stage{padding:8px;overflow:hidden}.cv-sheet{width:100%;min-height:auto}.cv-layout{min-height:calc((100vw - 16px)*1.414)}.cv-side{padding:4.2vw 2.8vw}.cv-main{padding:4.8vw 4vw}.cv-photo{width:16vw;height:16vw}.cv-main h1{font-size:4vw}.cv-job{font-size:2.4vw}.cv-section h3{font-size:2.2vw}.cv-item strong{font-size:1.8vw}.cv-item small,.cv-body,.cv-contact{font-size:1.55vw}.cv-watermark span{font-size:4vw}}@media print{body{display:none!important}}
    </style>
</head>
<body>
<div class="toolbar">
    <a href="{{ route('resume.edit', $draft) }}">العودة للسيرة الذاتية</a>
    @if($paid)
        <a class="download" href="{{ route('resume.download.pdf', $draft) }}">تحميل السيرة الذاتية PDF</a>
        @if($draft->image_path)
            <a class="download" href="{{ route('resume.download.image', $draft) }}">تحميل السيرة الذاتية كصورة</a>
        @else
            <button class="download" id="imageButton" type="button">إنشاء وتحميل السيرة كصورة</button>
        @endif
    @else
        <span class="notice">معاينة محمية — التنزيل والتصوير متاحان بعد الدفع</span>
    @endif
</div>
<div class="stage" id="stage">@include('resume._document', ['pdfMode' => false])</div>
<div class="capture-guard">المعاينة محمية حتى إتمام الدفع</div>
@if($paid)
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.getElementById('imageButton')?.addEventListener('click',async function(){
    this.disabled=true;this.textContent='جارٍ إنشاء الصورة...';
    try{
        const canvas=await html2canvas(document.querySelector('.cv-sheet'),{scale:3,backgroundColor:'#ffffff',useCORS:true});
        const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/png',1));
        const form=new FormData();form.append('image',blob,'resume.png');
        const response=await fetch(@json(route('resume.final-image.store',$draft)),{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:form});
        const data=await response.json();if(!response.ok)throw data;location.href=data.download_url;
    }catch(e){this.disabled=false;this.textContent='إعادة محاولة إنشاء الصورة';alert('تعذر إنشاء الصورة، حاول مرة أخرى.')}
});
try{ResumeSecurity.postMessage('open')}catch(e){}
</script>
@else
<script>
try{ResumeSecurity.postMessage('secure')}catch(e){}
document.addEventListener('contextmenu',e=>e.preventDefault());
document.addEventListener('dragstart',e=>e.preventDefault());
document.addEventListener('keydown',e=>{if(((e.ctrlKey||e.metaKey)&&['s','p','u','c'].includes(e.key.toLowerCase()))||e.key==='PrintScreen')e.preventDefault()});
document.addEventListener('visibilitychange',()=>document.body.classList.toggle('capturing',document.hidden));
window.addEventListener('beforeunload',()=>{try{ResumeSecurity.postMessage('open')}catch(e){}});
</script>
@endif
</body>
</html>
