@extends('admin.layout')

@section('title', 'منتجات القرطاسية - لوحة المدير')

@section('content')
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

        <div class="management-table-wrap">
            <table class="management-table">
                <thead><tr><th>الصورة</th><th>المنتج</th><th>الشركة</th><th>النوع</th><th>السعر</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td data-label="الصورة">
                                @if ($product->image_path)
                                    <img class="management-product-image" src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">
                                @else
                                    <span class="badge">بدون صورة</span>
                                @endif
                            </td>
                            <td data-label="المنتج"><strong>{{ $product->name }}</strong></td>
                            <td data-label="الشركة">{{ $product->company_name }}</td>
                            <td data-label="النوع">{{ $product->product_type }}</td>
                            <td data-label="السعر"><strong>{{ $product->price }} ريال</strong></td>
                            <td data-label="الحالة"><span class="badge">{{ $product->is_active ? 'ظاهر' : 'مخفي' }}</span></td>
                            <td data-label="الإجراءات">
                                <div class="actions">
                                    <button class="ghost" type="button" onclick="openAdminModal('تعديل المنتج', 'edit-stationery-product-{{ $product->id }}')">تعديل</button>
                                    <form method="post" action="{{ route('admin.stationery-products.destroy', $product) }}" onsubmit="return confirm('حذف هذا المنتج؟')">
                                        @csrf @method('delete')
                                        <button class="danger small-button" type="submit">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="7">لا توجد منتجات حاليًا.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
                    <div><label>تغيير الصورة</label><input name="image" type="file" accept="image/jpeg,image/png,image/webp"></div>
                    <label class="full"><input name="is_active" type="checkbox" value="1" {{ $product->is_active ? 'checked' : '' }} style="width:auto;"> إظهار المنتج للمستخدمين</label>
                </div>
                <button class="save" type="submit">حفظ التعديل</button>
            </form>
        </template>
    @endforeach
@endsection
