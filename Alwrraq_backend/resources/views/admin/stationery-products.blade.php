@extends('admin.layout')

@section('title', 'منتجات القرطاسية - لوحة المدير')

@section('content')
    <style>
        .admin-products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
        .admin-product-card { min-width: 0; overflow: hidden; border: 1px solid #dbe3ef; border-radius: 12px; background: #ffffff; box-shadow: 0 9px 22px rgba(15, 23, 42, 0.07); }
        .admin-product-image-wrap { position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .admin-product-image { width: 100%; height: 100%; display: block; object-fit: cover; }
        .admin-product-image-empty { width: 100%; height: 100%; display: grid; place-items: center; color: #94a3b8; font-size: 12px; font-weight: 900; }
        .admin-product-status { position: absolute; top: 7px; left: 7px; padding: 4px 7px; border-radius: 999px; background: #0f172a; color: #ffffff; font-size: 9px; font-weight: 900; }
        .admin-product-status.hidden { background: #64748b; }
        .admin-product-body { display: grid; gap: 6px; padding: 9px; }
        .admin-product-company { overflow: hidden; color: #64748b; font-size: 10px; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
        .admin-product-name { min-height: 38px; margin: 0; overflow: hidden; color: #0f172a; font-size: 13px; font-weight: 900; line-height: 1.45; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
        .admin-product-meta { display: flex; align-items: center; justify-content: space-between; gap: 6px; min-width: 0; }
        .admin-product-type { min-width: 0; overflow: hidden; color: #64748b; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .admin-product-price { flex: 0 0 auto; color: #047857; font-size: 13px; font-weight: 900; white-space: nowrap; }
        .admin-product-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px; }
        .admin-product-actions form { margin: 0; }
        .admin-product-actions .ghost,
        .admin-product-actions .danger { width: 100%; min-width: 0; min-height: 30px; margin: 0; padding: 6px 5px; font-size: 10px; text-align: center; justify-content: center; }
        .admin-products-empty { grid-column: 1 / -1; padding: 30px 12px; border: 1px dashed #cbd5e1; border-radius: 10px; color: #64748b; text-align: center; font-weight: 800; }
        .stationery-edit-image { display: grid; gap: 7px; }
        .stationery-edit-image-preview { position: relative; width: min(220px, 100%); aspect-ratio: 1 / 1; overflow: hidden; border: 1px solid #dbe3ef; border-radius: 12px; background: #f8fafc; }
        .stationery-edit-image-preview img { width: 100%; height: 100%; display: block; object-fit: contain; background: #ffffff; }
        .stationery-edit-image-placeholder { position: absolute; inset: 0; display: grid; place-items: center; padding: 12px; color: #94a3b8; font-size: 11px; font-weight: 900; text-align: center; }
        .stationery-edit-image-note { margin: 0; color: #64748b; font-size: 10px; line-height: 1.5; }
        @media (max-width: 980px) {
            .admin-products-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
            .admin-product-card { border-radius: 9px; }
            .admin-product-body { gap: 4px; padding: 6px; }
            .admin-product-status { top: 5px; left: 5px; padding: 3px 5px; font-size: 7px; }
            .admin-product-company,
            .admin-product-type { font-size: 8px; }
            .admin-product-name { min-height: 30px; font-size: 10px; line-height: 1.4; }
            .admin-product-price { font-size: 10px; }
            .admin-product-actions { gap: 3px; }
            .admin-product-actions .ghost,
            .admin-product-actions .danger { min-height: 26px; padding: 4px 2px; font-size: 8px; }
        }
        @media (max-width: 420px) {
            .admin-products-grid { gap: 5px; }
            .admin-product-name { min-height: 28px; font-size: 9px; }
            .admin-product-meta { gap: 3px; }
            .admin-product-company,
            .admin-product-type { font-size: 7.5px; }
            .admin-product-price { font-size: 9px; }
            .admin-product-actions .ghost,
            .admin-product-actions .danger { min-height: 24px; font-size: 7px; }
        }
        @media (min-width: 1100px) {
            .admin-product-company,
            .admin-product-type { font-size: 12px; }
            .admin-product-name { min-height: 44px; font-size: 15px; }
            .admin-product-price { font-size: 15px; }
            .admin-product-actions .ghost,
            .admin-product-actions .danger { font-size: 12px; }
        }
    </style>

    <div class="page-title compact-page-title">
        <div>
            <h1>منتجات القرطاسية</h1>
        </div>
        <button class="save" type="button" onclick="openAdminModal('إضافة منتج', 'create-stationery-product')">إضافة منتج</button>
    </div>

    <div class="panel compact-management-panel">
        <form class="search-form" method="get" action="{{ route('admin.stationery-products.index') }}" style="margin-bottom:14px;">
            <div style="flex:1;">
                <label>البحث في المنتجات</label>
                <input name="q" value="{{ $search }}" placeholder="اسم المنتج أو الشركة أو النوع">
            </div>
            <button class="save" type="submit">بحث</button>
        </form>

        <div class="admin-products-grid">
            @forelse ($products as $product)
                <article class="admin-product-card">
                    <div class="admin-product-image-wrap">
                        @if ($product->image_path)
                            <img class="admin-product-image" src="{{ route('stationery.image', ['filename' => basename($product->image_path)], false) }}" alt="" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
                            <div class="admin-product-image-empty" style="display:none">تعذر عرض الصورة</div>
                        @else
                            <div class="admin-product-image-empty">بدون صورة</div>
                        @endif
                        <span class="admin-product-status {{ $product->is_active ? '' : 'hidden' }}">{{ $product->is_active ? 'ظاهر' : 'مخفي' }}</span>
                    </div>
                    <div class="admin-product-body">
                        <div class="admin-product-company">{{ $product->company_name }}</div>
                        <h2 class="admin-product-name">{{ $product->name }}</h2>
                        <div class="admin-product-meta">
                            <span class="admin-product-type">{{ $product->product_type }}</span>
                            <strong class="admin-product-price">{{ $product->price }} ريال</strong>
                        </div>
                        <div class="admin-product-actions">
                            <button class="ghost" type="button" onclick="openAdminModal('تعديل المنتج', 'edit-stationery-product-{{ $product->id }}')">تعديل</button>
                            <form method="post" action="{{ route('admin.stationery-products.destroy', $product) }}" onsubmit="return confirm('حذف هذا المنتج؟')">
                                @csrf @method('delete')
                                <button class="danger" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="admin-products-empty">لا توجد منتجات حاليًا.</div>
            @endforelse
        </div>
    </div>

    <template id="create-stationery-product">
        <form method="post" action="{{ route('admin.stationery-products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div><label>اسم المنتج</label><input name="name" required></div>
                <div><label>اسم الشركة</label><input name="company_name" required></div>
                <div><label>نوع المنتج</label><input name="product_type" required></div>
                <div><label>السعر</label><input name="price" type="number" min="0.01" step="0.01" required></div>
                <div><label>صورة المنتج</label><input name="image" type="file" accept="image/jpeg,image/png,image/webp" required></div>
                <label class="full"><input name="is_active" type="checkbox" value="1" checked style="width:auto;"> إظهار المنتج للمستخدمين</label>
            </div>
            <button class="save" type="submit">حفظ المنتج</button>
        </form>
    </template>

    @foreach ($products as $product)
        <template id="edit-stationery-product-{{ $product->id }}">
            <form method="post" action="{{ route('admin.stationery-products.update', $product) }}" enctype="multipart/form-data">
                @csrf @method('patch')
                <div class="form-grid">
                    <div><label>اسم المنتج</label><input name="name" value="{{ $product->name }}" required></div>
                    <div><label>اسم الشركة</label><input name="company_name" value="{{ $product->company_name }}" required></div>
                    <div><label>نوع المنتج</label><input name="product_type" value="{{ $product->product_type }}" required></div>
                    <div><label>السعر</label><input name="price" type="number" min="0.01" step="0.01" value="{{ $product->price }}" required></div>
                    <div class="full stationery-edit-image">
                        <label>صورة المنتج</label>
                        <div class="stationery-edit-image-preview">
                            <img
                                src="{{ $product->image_path ? route('stationery.image', ['filename' => basename($product->image_path)], false) : '' }}"
                                alt="صورة {{ $product->name }} الحالية"
                                data-stationery-image-preview
                                @if (! $product->image_path) style="display:none" @endif
                            >
                            <div class="stationery-edit-image-placeholder" data-stationery-image-placeholder @if ($product->image_path) style="display:none" @endif>
                                {{ $product->image_path ? 'تعذر عرض الصورة الحالية' : 'لا توجد صورة حالية' }}
                            </div>
                        </div>
                        <p class="stationery-edit-image-note">هذه هي الصورة الحالية. اترك الحقل التالي فارغًا للاحتفاظ بها، أو اختر صورة جديدة لاستبدالها.</p>
                        <input name="image" type="file" accept="image/jpeg,image/png,image/webp" data-stationery-image-input>
                    </div>
                    <label class="full"><input name="is_active" type="checkbox" value="1" {{ $product->is_active ? 'checked' : '' }} style="width:auto;"> إظهار المنتج للمستخدمين</label>
                </div>
                <button class="save" type="submit">حفظ التعديل</button>
            </form>
        </template>
    @endforeach

    <script>
        document.addEventListener('change', (event) => {
            const input = event.target.closest('[data-stationery-image-input]');
            if (!input) return;

            const container = input.closest('.stationery-edit-image');
            const preview = container?.querySelector('[data-stationery-image-preview]');
            const placeholder = container?.querySelector('[data-stationery-image-placeholder]');
            const file = input.files?.[0];
            if (!preview || !placeholder || !file) return;

            const objectUrl = URL.createObjectURL(file);
            preview.onload = () => {
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                URL.revokeObjectURL(objectUrl);
            };
            preview.onerror = () => {
                preview.style.display = 'none';
                placeholder.style.display = 'grid';
                placeholder.textContent = 'تعذر معاينة الصورة المختارة';
                URL.revokeObjectURL(objectUrl);
            };
            preview.src = objectUrl;
            preview.alt = `معاينة ${file.name}`;
        });

        document.addEventListener('error', (event) => {
            const preview = event.target.closest?.('[data-stationery-image-preview]');
            if (!preview) return;

            preview.style.display = 'none';
            const placeholder = preview.parentElement?.querySelector('[data-stationery-image-placeholder]');
            if (placeholder) placeholder.style.display = 'grid';
        }, true);
    </script>
@endsection
