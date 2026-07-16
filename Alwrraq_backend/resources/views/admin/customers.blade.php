@extends('admin.layout')

@section('title', 'العملاء - لوحة المدير')

@section('content')
    <div class="page-title">
        <div>
            <h1>العملاء</h1>
            <p class="subtitle">قائمة العملاء مع تعديل من نافذة مستقلة.</p>
        </div>
        @if (auth()->user()->hasAdminPermission('customers_create'))
            <button class="save" type="button" onclick="openAdminModal('إضافة عميل', 'create-customer-template')">إضافة عميل</button>
        @endif
    </div>

    <div class="panel">
        <form class="search-form auto-search-form" method="get" action="{{ route('admin.customers') }}" style="margin-bottom: 14px;">
            <div style="flex: 1;">
                <label>بحث باسم العميل أو رقم الجوال</label>
                <input name="search" value="{{ $search }}" placeholder="اكتب الاسم أو رقم الجوال">
            </div>
            @if ($search !== '')
                <a class="ghost" href="{{ route('admin.customers') }}">مسح</a>
            @endif
        </form>

        <div class="management-table-wrap">
            <table class="management-table">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>رقم الجوال</th>
                        <th>البريد</th>
                        <th>الطلبات</th>
                        <th>الحالة</th>
                        <th>الصلاحية</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td data-label="العميل">
                                <div class="identity">
                                    <strong>{{ $customer->name }}</strong>
                                    <span class="id-badge">عميل {{ $loop->iteration }}</span>
                                </div>
                            </td>
                            <td data-label="رقم الجوال">{{ $customer->phone }}</td>
                            <td data-label="البريد">{{ $customer->email ?: '-' }}</td>
                            <td data-label="الطلبات">{{ $customer->orders_count }}</td>
                            <td data-label="الحالة">
                                <span class="badge">{{ $customer->is_active ? 'نشط' : 'موقوف' }}</span>
                                @if ($customer->login_blocked)
                                    <span class="badge">ممنوع الدخول</span>
                                @endif
                                @if ($customer->account_verified_at)
                                    <span class="badge">موثق</span>
                                @endif
                            </td>
                            <td data-label="الصلاحية"><span class="badge">عميل</span></td>
                            <td data-label="الإجراءات">
                                <div class="actions">
                                    @if (auth()->user()->hasAnyAdminPermission(['customers_update', 'customers_status', 'customers_login_block', 'customers_password_reset', 'customers_phone_update', 'customers_email_update', 'customers_verify']))
                                        <button class="ghost" type="button" onclick="openAdminModal('تعديل عميل', 'edit-customer-{{ $customer->id }}')">تعديل</button>
                                    @endif
                                    @if (auth()->user()->hasAdminPermission('customers_email_update'))
                                        <button class="ghost" type="button" onclick="openAdminModal('حفظ بريد العميل', 'email-customer-{{ $customer->id }}')">البريد</button>
                                    @endif
                                    @if (auth()->user()->hasAdminPermission('customers_delete'))
                                        <form method="post" action="{{ route('admin.users.destroy', $customer) }}" onsubmit="return confirm('حذف هذا العميل؟ سيتم حذف طلباته وملفاته من قاعدة البيانات.')">
                                            @csrf
                                            @method('delete')
                                            <button class="danger small-button" type="submit">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">لا يوجد عملاء.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template id="create-customer-template">
        <form method="post" action="{{ route('admin.users.store') }}">
            @csrf
            <input type="hidden" name="role" value="customer">
                <div class="form-grid">
                    <div><label>الاسم الأول</label><input name="first_name" required></div>
                    <div><label>الاسم الثاني</label><input name="second_name"></div>
                    <div><label>رقم الجوال</label><input name="phone" required></div>
                    <div><label>البريد الإلكتروني</label><input name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com"></div>
                    <div><label>كلمة المرور</label><input name="password" type="password" required></div>
                    <div><label>تأكيد كلمة المرور</label><input name="password_confirmation" type="password" required></div>
                </div>
            <button class="save" type="submit">إضافة عميل</button>
        </form>
    </template>

    @foreach ($customers as $customer)
        @php
            $customerNameParts = preg_split('/\s+/', trim($customer->name), 2);
            $customerFirstName = $customerNameParts[0] ?? '';
            $customerSecondName = $customerNameParts[1] ?? '';
        @endphp
        <template id="edit-customer-{{ $customer->id }}">
            <form method="post" action="{{ route('admin.users.update', $customer) }}">
                @csrf
                @method('patch')
                <input type="hidden" name="role" value="customer">
                <div class="form-section">
                    <h3 class="form-section-title">بيانات العميل</h3>
                    <div class="form-grid">
                        @if (auth()->user()->hasAdminPermission('customers_update'))
                            <div><label>الاسم الأول</label><input name="first_name" value="{{ $customerFirstName }}" required></div>
                            <div><label>الاسم الثاني</label><input name="second_name" value="{{ $customerSecondName }}"></div>
                        @else
                            <input type="hidden" name="name" value="{{ $customer->name }}">
                        @endif
                        @if (auth()->user()->hasAdminPermission('customers_phone_update'))
                            <div><label>رقم الجوال</label><input name="phone" value="{{ $customer->phone }}" required></div>
                        @else
                            <input type="hidden" name="phone" value="{{ $customer->phone }}">
                        @endif
                        @if (auth()->user()->hasAdminPermission('customers_email_update'))
                            <div><label>البريد الإلكتروني</label><input name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com" value="{{ $customer->email }}"></div>
                        @else
                            <input type="hidden" name="email" value="{{ $customer->email }}">
                        @endif
                    </div>
                </div>
                @if (auth()->user()->hasAnyAdminPermission(['customers_status', 'customers_login_block', 'customers_verify']))
                    <div class="form-section">
                        <h3 class="form-section-title">حالة الحساب</h3>
                        <div class="permissions-grid">
                            @if (auth()->user()->hasAdminPermission('customers_status'))
                                <label class="permission-option">
                                    <input type="checkbox" name="is_active" value="1" @checked($customer->is_active)>
                                    <span>الحساب نشط</span>
                                </label>
                            @endif
                            @if (auth()->user()->hasAdminPermission('customers_login_block'))
                                <label class="permission-option">
                                    <input type="checkbox" name="login_blocked" value="1" @checked($customer->login_blocked)>
                                    <span>منع تسجيل الدخول</span>
                                </label>
                            @endif
                            @if (auth()->user()->hasAdminPermission('customers_verify'))
                                <label class="permission-option">
                                    <input type="checkbox" name="account_verified" value="1" @checked(filled($customer->account_verified_at))>
                                    <span>حساب موثق</span>
                                </label>
                            @endif
                        </div>
                    </div>
                @endif
                @if (auth()->user()->hasAdminPermission('customers_password_reset'))
                <div class="form-section">
                    <h3 class="form-section-title">تغيير كلمة المرور</h3>
                    <p class="form-note">اضغط الزر إذا كنت تريد تغيير كلمة مرور هذا العميل.</p>
                    <button class="ghost" type="button" onclick="toggleInlinePasswordPanel(this)">تغيير كلمة المرور</button>
                    <div class="form-grid inline-password-panel" style="display: none; margin-top: 12px;">
                        <div><label>كلمة المرور الجديدة</label><input name="password" type="password" placeholder="كلمة مرور جديدة"></div>
                        <div><label>تأكيد كلمة المرور الجديدة</label><input name="password_confirmation" type="password" placeholder="تأكيد كلمة المرور"></div>
                    </div>
                </div>
                @endif
                <button class="save" type="submit">حفظ التعديل</button>
            </form>
        </template>

        <template id="email-customer-{{ $customer->id }}">
            <form method="post" action="{{ route('admin.users.email.update', $customer) }}">
                @csrf
                @method('patch')
                <div class="form-section">
                    <h3 class="form-section-title">البريد الإلكتروني</h3>
                    <p class="form-note">لن يتم حفظ البريد إلا بعد الضغط على زر حفظ البريد.</p>
                    <label>البريد الإلكتروني</label>
                    <input name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com" value="{{ $customer->email }}" required>
                </div>
                <button class="save" type="submit">حفظ البريد</button>
            </form>
        </template>
    @endforeach
@endsection
