<header class="page-header">
    <div class="header-inner">
        <div class="header-brand">
            <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
            <div class="brand">الورّاق</div>
            <p class="brand-subtitle">خدمات النسخ والتصوير</p>
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
                <a class="header-link" href="{{ route('admin.orders') }}">لوحة المدير</a>
            @endif
            <form class="header-form" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">🚪 خروج</button>
            </form>
            @include('shared.language-switcher')
        </div>
    </div>
</header>
