<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('shared.tab-brand')
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3f4f6;color:#0f172a;font-family:Arial,sans-serif}.top{background:#0f172a;color:#fff;padding:16px 22px}.top-inner{width:min(1080px,100%);margin:auto;display:flex;align-items:center;justify-content:space-between;gap:16px}.brand{font-size:23px;font-weight:900}.nav{display:flex;gap:8px;flex-wrap:wrap}.nav a{color:#fff;text-decoration:none;background:#1e293b;border-radius:9px;padding:10px 14px;font-weight:800}.page{width:min(1080px,calc(100% - 28px));margin:32px auto}.hero{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);overflow:hidden;background:#fff;border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 24px 65px rgba(15,23,42,.1)}.copy{padding:clamp(28px,6vw,64px)}.eyebrow{display:inline-flex;padding:7px 12px;border-radius:999px;background:#e0f2fe;color:#075985;font-weight:900}.copy h1{font-size:clamp(32px,5vw,54px);line-height:1.25;margin:20px 0 14px}.copy p{font-size:18px;line-height:2;color:#475569}.price{font-size:30px;font-weight:900;color:#047857;margin:22px 0}.start{border:0;border-radius:12px;background:#0f4c81;color:#fff;padding:15px 24px;font-size:17px;font-weight:900;cursor:pointer}.visual{min-height:440px;padding:34px;background:linear-gradient(145deg,#0f172a,#0f4c81);display:flex;align-items:center;justify-content:center}.paper{width:250px;aspect-ratio:210/297;background:#fff;border-radius:5px;padding:22px;box-shadow:0 28px 65px rgba(0,0,0,.3)}.line{height:7px;background:#cbd5e1;border-radius:8px;margin:10px 0}.line.dark{height:18px;width:65%;background:#0f4c81}.line.short{width:55%}.resume{margin-top:15px;display:grid;grid-template-columns:1fr 1.7fr;gap:13px}.block{height:110px;border-radius:6px;background:#f1f5f9}.block.tall{height:230px}.continue{margin-top:12px;color:#475569;font-weight:800}@media(max-width:760px){.hero{grid-template-columns:1fr}.visual{min-height:320px}.top-inner{align-items:flex-start;flex-direction:column}.copy{padding:28px 20px}}
    </style>
</head>
<body>
<header class="top"><div class="top-inner"><div class="brand">الورّاق</div><nav class="nav"><a href="{{ route('home') }}">الرئيسية</a><a href="{{ route('cart.index') }}">السلة</a><a href="{{ route('orders.index') }}">طلباتي</a></nav></div></header>
<main class="page">
    <section class="hero">
        <div class="copy">
            <span class="eyebrow">خدمة رقمية احترافية</span>
            <h1>إنشاء سيرة ذاتية احترافية</h1>
            <p>أنشئ سيرتك الذاتية بتصميم فاخر واحترافي، أدخل بياناتك وشاهد النتيجة مباشرة، ثم ادفع وحمّل سيرتك الذاتية بصيغة PDF أو صورة عالية الجودة.</p>
            <div class="price">5 ريالات</div>
            <form method="post" action="{{ route('resume.start') }}">
                @csrf
                <button class="start" type="submit">{{ $draft ? 'متابعة تعديل سيرتك الذاتية' : 'ابدأ إنشاء سيرتك الذاتية' }}</button>
            </form>
            @if($draft)<div class="continue">تم حفظ بياناتك السابقة تلقائيًا.</div>@endif
        </div>
        <div class="visual" aria-hidden="true"><div class="paper"><div class="line dark"></div><div class="line short"></div><div class="resume"><div><div class="block tall"></div></div><div><div class="block"></div><div class="line"></div><div class="line short"></div><div class="block"></div></div></div></div></div>
    </section>
</main>
@include('shared.language-tools')
</body>
</html>
