<!DOCTYPE html>
<html lang="{{ $draft->language }}" dir="{{ $draft->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<style>
    @page{margin:0;size:A4}*{box-sizing:border-box}body{margin:0;font-family:"DejaVu Sans",sans-serif;color:#0f172a}.cv-sheet{width:210mm;min-height:297mm;background:#fff}.cv-layout{display:block;width:100%;min-height:297mm}.cv-side{position:fixed;top:0;bottom:0;width:31%;background:#102a43;color:#fff;padding:12mm 7mm}.cv-main{width:69%;padding:13mm 10mm;min-height:297mm}.cv-sheet[dir=rtl] .cv-side{right:0}.cv-sheet[dir=rtl] .cv-main{margin-right:31%}.cv-sheet[dir=ltr] .cv-side{left:0}.cv-sheet[dir=ltr] .cv-main{margin-left:31%}.cv-photo{display:block;width:35mm;height:35mm;border-radius:50%;object-fit:cover;border:1.2mm solid #cbd5e1;margin:0 auto 7mm}.cv-main h1{font-size:23pt;color:#102a43;margin:0}.cv-job{font-size:13pt;color:#0f4c81;font-weight:bold;margin:2mm 0 6mm}.cv-section{page-break-inside:avoid;margin:0 0 6mm}.cv-section h3{font-size:11pt;color:#0f4c81;border-bottom:1px solid #bfdbfe;padding-bottom:2mm;margin:0 0 3mm}.cv-side .cv-section h3{color:#fff;border-color:#486581}.cv-item{page-break-inside:avoid;margin-bottom:3mm}.cv-item strong{display:block;font-size:9.5pt}.cv-item small{display:block;color:#64748b;font-size:8pt;margin-top:1mm}.cv-side .cv-item small{color:#cbd5e1}.cv-body,.cv-contact{font-size:8.5pt;line-height:1.65;white-space:pre-line;overflow-wrap:anywhere}.cv-contact{margin-bottom:1.5mm}.cv-watermark{display:none}
</style>
</head>
<body>@include('resume._document', ['pdfMode' => true, 'paid' => true])</body>
</html>
