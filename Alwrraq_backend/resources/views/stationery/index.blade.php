<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>القرطاسية</title>
    <style>
        * { box-sizing: border-box; }
        :root { --sidebar-width: clamp(180px, 20vw, 240px); --page-gap: clamp(14px, 3vw, 40px); }
        body { margin: 0; padding: 0 calc(var(--sidebar-width) + var(--page-gap)) 0 var(--page-gap); background: #f3f4f6; color: #0f172a; font-family: Arial, sans-serif; }
        .header { position: fixed; top: 0; right: 0; z-index: 20; width: var(--sidebar-width); min-height: 100vh; max-height: 100vh; overflow-y: auto; padding: 18px 14px; background: #0f172a; color: #fff; box-shadow: -10px 0 30px rgba(15,23,42,.15); }
        .header-inner { height: 100%; display: flex; flex-direction: column; }
        .header-brand { display: flex; flex-direction: column; align-items: flex-start; }
        .brand-logo { width: 46px; height: 46px; margin-bottom: 8px; border-radius: 14px; object-fit: cover; background: #fff; }
        .brand { font-size: 23px; font-weight: 900; }
        .header-actions { display: flex; flex-direction: column; gap: 9px; margin-top: 22px; }
        .home-button, .settings-button { display: flex; align-items: center; width: 100%; padding: 10px 12px; border-radius: 10px; color: #fff; background: rgba(255,255,255,.06); text-decoration: none; font-size: 13px; font-weight: 900; }
        .settings-button { background: #0f4c81; }
        .header-form { margin: 0; }
        .logout-button { width: 100%; margin: 0; padding: 10px 12px; border: 0; border-radius: 10px; background: #b91c1c; color: #fff; font-weight: 900; cursor: pointer; }
        main { width: min(980px, 100%); margin: 22px auto; padding: 0 12px; }
        .store-shell { padding: 16px; border: 1px solid #e2e8f0; border-radius: 16px; background: #fff; box-shadow: 0 16px 40px rgba(15,23,42,.07); }
        .back-button { display: inline-flex; align-items: center; justify-content: center; align-self: flex-start; margin-bottom: 20px; padding: 12px 19px; border: 0; border-radius: 7px; background: #16a34a; color: #fff; font-size: 15px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all .3s; }
        .back-button:hover { background: #15803d; }
        .store-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .store-title h1 { margin: 0 0 3px; font-size: 25px; }
        .store-title p { margin: 0; color: #64748b; font-size: 12px; }
        .cart-link { display: inline-flex; align-items: center; gap: 6px; padding: 9px 12px; border-radius: 9px; background: #047857; color: #fff; text-decoration: none; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .cart-count { min-width: 20px; padding: 2px 5px; border-radius: 999px; background: #fff; color: #047857; text-align: center; }
        .search-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 7px; margin-bottom: 14px; padding: 7px; border: 1px solid #dbe3ef; border-radius: 11px; background: #f8fafc; }
        .search-form input { min-width: 0; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-size: 16px; }
        .search-form button, .search-clear { display: inline-flex; align-items: center; justify-content: center; margin: 0; padding: 8px 14px; border: 0; border-radius: 8px; background: #0f172a; color: #fff; font-size: 12px; font-weight: 900; text-decoration: none; cursor: pointer; }
        .products-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .product-card { min-width: 0; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 8px 20px rgba(15,23,42,.06); }
        .product-image-wrap { position: relative; aspect-ratio: 1 / 1; overflow: hidden; background: #f8fafc; }
        .product-image { width: 100%; height: 100%; display: block; object-fit: cover; }
        .product-placeholder { width: 100%; height: 100%; display: grid; place-items: center; color: #94a3b8; font-size: 38px; }
        .quantity-badge { position: absolute; top: 6px; left: 6px; min-width: 23px; padding: 4px 6px; border-radius: 999px; background: #0f172a; color: #fff; font-size: 10px; font-weight: 900; text-align: center; }
        .product-body { padding: 8px; }
        .product-company { overflow: hidden; color: #64748b; font-size: 9px; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
        .product-name { display: -webkit-box; min-height: 31px; margin: 3px 0; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; color: #0f172a; font-size: 11px; font-weight: 900; line-height: 1.4; }
        .product-meta { display: flex; align-items: center; justify-content: space-between; gap: 5px; margin-bottom: 7px; }
        .product-type { min-width: 0; overflow: hidden; color: #64748b; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
        .product-price { color: #047857; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .product-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .product-actions button { width: 100%; min-width: 0; min-height: 29px; margin: 0; padding: 5px 3px; border: 0; border-radius: 7px; color: #fff; font-size: 9px; font-weight: 900; cursor: pointer; }
        .add-product { background: #2563eb; }
        .remove-product { background: #b91c1c; }
        .remove-product:disabled { background: #cbd5e1; color: #64748b; cursor: default; }
        .empty { grid-column: 1 / -1; padding: 30px 12px; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; text-align: center; }
        .notice { margin-bottom: 10px; padding: 9px 11px; border-radius: 8px; background: #ecfdf5; color: #047857; font-size: 12px; font-weight: 800; }
        @media (max-width: 820px) {
            :root { --sidebar-width: 0px; --page-gap: 0px; }
            body { padding: 0; }
            .header { width: 100%; min-height: 0; max-height: none; }
            .header-brand { flex-direction: row; align-items: center; gap: 6px; }
            main { width: calc(100% - 12px); margin: 8px auto 20px; padding: 0; }
            .store-shell { padding: 9px; border-radius: 12px; }
            .back-button { margin: 0 0 8px; padding: 9px 12px; border-radius: 8px; font-size: 12px; white-space: nowrap; }
            .store-head { margin-bottom: 8px; }
            .store-title h1 { font-size: 18px; }
            .store-title p { font-size: 9px; }
            .cart-link { padding: 7px 9px; font-size: 10px; }
            .search-form { margin-bottom: 9px; padding: 5px; }
            .search-form input { padding: 7px 8px; }
            .search-form button { padding: 6px 10px; font-size: 10px; }
            .products-grid { gap: 6px; }
            .product-card { border-radius: 9px; }
            .product-body { padding: 6px; }
            .product-company, .product-type { font-size: 8px; }
            .product-name { min-height: 27px; font-size: 9.5px; line-height: 1.4; }
            .product-price { font-size: 10px; }
            .product-actions { gap: 3px; }
            .product-actions button { min-height: 26px; padding: 4px 2px; font-size: 8px; }
        }
    </style>
</head>
<body class="customer-app-page">
    <header class="header">
        <div class="header-inner">
            <div class="header-brand">
                <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                <div class="brand">الورّاق</div>
            </div>
            <div class="header-identity">
                <strong>{{ auth()->user()->name }}</strong>
                <small>{{ auth()->user()->role === 'admin' ? 'المدير' : 'العميل' }}</small>
            </div>
            <div class="header-actions">
                <a class="home-button" href="{{ route('home') }}">🏠 الرئيسية</a>
                <a class="home-button" href="{{ route('orders.index') }}">🧾 طلباتي</a>
                <a class="home-button" href="{{ route('cart.index') }}">🛒 السلة</a>
                <a class="settings-button" href="{{ route('account.settings') }}">⚙️ الإعدادات</a>
                <form class="header-form" method="post" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">🚪 خروج</button></form>
                @include('shared.language-switcher')
            </div>
        </div>
    </header>

    <main>
        @if (session('status'))<div class="notice">{{ session('status') }}</div>@endif
        <section class="store-shell">
            <a class="back-button" href="{{ route('home') }}">← العودة للخدمات</a>
            <div class="store-head">
                <div class="store-title">
                    <h1>القرطاسية</h1>
                    <p>اختر المنتجات وأضفها أو أزلها من السلة مباشرة.</p>
                </div>
                <a class="cart-link" href="{{ route('cart.index') }}">🛒 السلة <span id="stationeryCartCount" class="cart-count">{{ $cartOrder?->productItems->sum('quantity') ?? 0 }}</span></a>
            </div>

            <form class="search-form" method="get" action="{{ route('stationery.index') }}">
                <input name="q" value="{{ $search }}" placeholder="ابحث باسم المنتج أو الشركة أو النوع" autocomplete="off">
                <button type="submit">بحث</button>
            </form>

            <div class="products-grid">
                @forelse ($products as $product)
                    @php($quantity = (int) ($cartQuantities[$product->id] ?? 0))
                    <article class="product-card" data-product-card="{{ $product->id }}">
                        <div class="product-image-wrap">
                            @if ($product->image_path)
                                <img class="product-image" src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <div class="product-placeholder">✏️</div>
                            @endif
                            <span class="quantity-badge" data-quantity style="{{ $quantity > 0 ? '' : 'display:none' }}">{{ $quantity }}</span>
                        </div>
                        <div class="product-body">
                            <div class="product-company">{{ $product->company_name }}</div>
                            <strong class="product-name">{{ $product->name }}</strong>
                            <div class="product-meta">
                                <span class="product-type">{{ $product->product_type }}</span>
                                <span class="product-price">{{ $product->price }} ر.س</span>
                            </div>
                            <div class="product-actions">
                                <button class="add-product" type="button" data-url="{{ route('stationery.products.add', $product) }}" onclick="changeStationeryCart(this, 'add')">إضافة للسلة</button>
                                <button class="remove-product" type="button" data-url="{{ route('stationery.products.remove', $product) }}" onclick="changeStationeryCart(this, 'remove')" {{ $quantity > 0 ? '' : 'disabled' }}>إزالة</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty">لا توجد منتجات مطابقة للبحث حاليًا.</div>
                @endforelse
            </div>
        </section>
    </main>

    <script>
        async function changeStationeryCart(button, action) {
            const card = button.closest('[data-product-card]');
            const buttons = card.querySelectorAll('button');
            buttons.forEach((item) => item.disabled = true);

            try {
                const response = await fetch(button.dataset.url, {
                    method: action === 'add' ? 'POST' : 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const payload = await response.json();
                if (!response.ok || !payload.success) throw new Error();

                const quantity = action === 'remove' ? 0 : Number(payload.quantity || 0);
                const badge = card.querySelector('[data-quantity]');
                badge.textContent = quantity;
                badge.style.display = quantity > 0 ? '' : 'none';
                document.getElementById('stationeryCartCount').textContent = payload.cart_count || 0;
                card.querySelector('.remove-product').disabled = quantity === 0;
                card.querySelector('.add-product').disabled = false;
            } catch (error) {
                buttons.forEach((item) => item.disabled = false);
                alert('تعذر تحديث السلة، حاول مرة أخرى.');
            }
        }
    </script>
    @include('shared.chat-widget')
    @include('shared.language-tools')
</body>
</html>
