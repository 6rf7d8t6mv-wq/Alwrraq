@extends('admin.layout')

@section('title', 'المستخدمين - لوحة المدير')

@section('content')
    <div class="page-title compact-page-title">
        <div>
            <h1>المستخدمين</h1>
        </div>
        @if (auth()->user()->hasAdminPermission('users_create'))
            <button class="save" type="button" onclick="openAdminModal('إضافة مستخدم', 'create-admin-template')">إضافة مستخدم</button>
        @endif
    </div>

    <div class="panel compact-management-panel">
        <form class="search-form auto-search-form" method="get" action="{{ route('admin.users') }}" style="margin-bottom: 14px;">
            <div style="flex: 1;">
                <label>بحث باسم المستخدم أو رقم الجوال</label>
                <input name="search" value="{{ $search }}" placeholder="اكتب الاسم أو رقم الجوال">
            </div>
            @if ($search !== '')
                <a class="ghost" href="{{ route('admin.users') }}">مسح</a>
            @endif
        </form>

        <div class="management-table-wrap">
            <table class="management-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>رقم الجوال</th>
                        <th>البريد</th>
                        <th>الطلبات</th>
                        <th>الحالة</th>
                        <th>الصلاحية</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td data-label="المستخدم">
                                <div class="identity">
                                    <strong>{{ $user->name }}</strong>
                                    <span class="id-badge">{{ auth()->user()->is($user) ? 'حسابك الحالي' : 'مستخدم ' . $loop->iteration }}</span>
                                </div>
                            </td>
                            <td data-label="رقم الجوال">{{ $user->phone }}</td>
                            <td data-label="البريد">{{ $user->email ?: '-' }}</td>
                            <td data-label="الطلبات">{{ $user->orders_count }}</td>
                            <td data-label="الحالة">
                                <div class="compact-badges">
                                    <span class="badge">{{ $user->is_active ? 'نشط' : 'موقوف' }}</span>
                                    @if ($user->login_blocked)
                                        <span class="badge">ممنوع الدخول</span>
                                    @endif
                                    @if ($user->account_verified_at)
                                        <span class="badge">موثق</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="الصلاحية"><span class="badge">مستخدم إداري</span></td>
                            <td data-label="الإجراءات">
                                <div class="actions">
                                    @if (!auth()->user()->is($user) && auth()->user()->hasAnyAdminPermission(['users_update', 'users_status', 'users_login_block', 'users_password_reset', 'users_phone_update', 'users_email_update', 'users_verify']))
                                        <button class="ghost" type="button" onclick="openAdminModal('تعديل بيانات المستخدم', 'edit-admin-{{ $user->id }}')">تعديل البيانات</button>
                                    @endif
                                    @if (!auth()->user()->is($user) && auth()->user()->hasAdminPermission('users_email_update'))
                                        <button class="ghost" type="button" onclick="openAdminModal('حفظ بريد المستخدم', 'email-admin-{{ $user->id }}')">البريد</button>
                                    @endif
                                    @if (!auth()->user()->is($user) && auth()->user()->hasAdminPermission('users_permissions_manage'))
                                        <button class="ghost" type="button" onclick="openAdminModal('صلاحيات المستخدم', 'permissions-admin-{{ $user->id }}')">الصلاحيات</button>
                                    @elseif (auth()->user()->is($user))
                                        <span class="muted">تعديل حسابك من الإعدادات</span>
                                    @endif
                                    @if (auth()->user()->hasAdminPermission('users_delete') && !auth()->user()->is($user))
                                        <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('حذف هذا المستخدم؟')">
                                            @csrf
                                            @method('delete')
                                            <button class="danger small-button" type="submit">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">لا يوجد مستخدمين.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template id="create-admin-template">
        <form method="post" action="{{ route('admin.users.store') }}">
            @csrf
            <input type="hidden" name="role" value="admin">
            <div class="form-grid">
                <div><label>الاسم الأول</label><input name="first_name" required></div>
                <div><label>الاسم الثاني</label><input name="second_name"></div>
                <div><label>رقم الجوال</label><input name="phone" required></div>
                <div><label>البريد الإلكتروني</label><input name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com"></div>
                <div><label>كلمة المرور</label><input name="password" type="password" required></div>
                <div><label>تأكيد كلمة المرور</label><input name="password_confirmation" type="password" required></div>
            </div>
            <div class="form-section">
                <h3 class="form-section-title">صلاحيات المستخدم</h3>
                <div class="permissions-grid">
                    @foreach ($permissionOptions as $permissionKey => $permissionLabel)
                        <label class="permission-option">
                            <input type="checkbox" name="admin_permissions[]" value="{{ $permissionKey }}">
                            <span>{{ $permissionLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button class="save" type="submit">إضافة مستخدم</button>
        </form>
    </template>

    @foreach ($users as $user)
        @php
            $selectedPermissions = $user->admin_permissions ?? array_keys($permissionOptions);
            $userNameParts = preg_split('/\s+/', trim($user->name), 2);
            $userFirstName = $userNameParts[0] ?? '';
            $userSecondName = $userNameParts[1] ?? '';
        @endphp
        <template id="edit-admin-{{ $user->id }}">
            <form method="post" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('patch')
                <input type="hidden" name="role" value="admin">
                <div class="form-section">
                    <h3 class="form-section-title">بيانات المستخدم</h3>
                    <div class="form-grid">
                        @if (auth()->user()->hasAdminPermission('users_update'))
                            <div><label>الاسم الأول</label><input name="first_name" value="{{ $userFirstName }}" required></div>
                            <div><label>الاسم الثاني</label><input name="second_name" value="{{ $userSecondName }}"></div>
                        @else
                            <input type="hidden" name="name" value="{{ $user->name }}">
                        @endif
                        @if (auth()->user()->hasAdminPermission('users_phone_update'))
                            <div><label>رقم الجوال</label><input name="phone" value="{{ $user->phone }}" required></div>
                        @else
                            <input type="hidden" name="phone" value="{{ $user->phone }}">
                        @endif
                        @if (auth()->user()->hasAdminPermission('users_email_update'))
                            <div><label>البريد الإلكتروني</label><input name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com" value="{{ $user->email }}"></div>
                        @else
                            <input type="hidden" name="email" value="{{ $user->email }}">
                        @endif
                    </div>
                </div>
                @if (auth()->user()->hasAnyAdminPermission(['users_status', 'users_login_block', 'users_verify']))
                    <div class="form-section">
                        <h3 class="form-section-title">حالة الحساب</h3>
                        <div class="permissions-grid">
                            @if (auth()->user()->hasAdminPermission('users_status'))
                                <label class="permission-option">
                                    <input type="checkbox" name="is_active" value="1" @checked($user->is_active)>
                                    <span>الحساب نشط</span>
                                </label>
                            @endif
                            @if (auth()->user()->hasAdminPermission('users_login_block'))
                                <label class="permission-option">
                                    <input type="checkbox" name="login_blocked" value="1" @checked($user->login_blocked)>
                                    <span>منع تسجيل الدخول</span>
                                </label>
                            @endif
                            @if (auth()->user()->hasAdminPermission('users_verify'))
                                <label class="permission-option">
                                    <input type="checkbox" name="account_verified" value="1" @checked(filled($user->account_verified_at))>
                                    <span>حساب موثق</span>
                                </label>
                            @endif
                        </div>
                    </div>
                @endif
                @if (auth()->user()->hasAdminPermission('users_password_reset'))
                <div class="form-section">
                    <h3 class="form-section-title">تغيير كلمة المرور</h3>
                    <p class="form-note">اضغط الزر إذا كنت تريد تغيير كلمة مرور هذا المستخدم.</p>
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

        <template id="email-admin-{{ $user->id }}">
            <form method="post" action="{{ route('admin.users.email.update', $user) }}">
                @csrf
                @method('patch')
                <div class="form-section">
                    <h3 class="form-section-title">البريد الإلكتروني</h3>
                    <p class="form-note">لن يتم حفظ البريد إلا بعد الضغط على زر حفظ البريد.</p>
                    <label>البريد الإلكتروني</label>
                    <input name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com" value="{{ $user->email }}" required>
                </div>
                <button class="save" type="submit">حفظ البريد</button>
            </form>
        </template>

        <template id="permissions-admin-{{ $user->id }}">
            <form method="post" action="{{ route('admin.users.permissions.update', $user) }}">
                @csrf
                @method('patch')
                <div class="form-section">
                    <h3 class="form-section-title">صلاحيات المستخدم</h3>
                    <p class="form-note">حدد العمليات التي يستطيع {{ $user->name }} تنفيذها داخل لوحة النظام.</p>
                    @if (auth()->user()->hasAdminPermission('users_permissions_copy'))
                        <label>نسخ صلاحيات من مستخدم آخر</label>
                        <select name="copy_permissions_from">
                            <option value="">بدون نسخ</option>
                            @foreach ($copyableUsers->where('id', '!=', $user->id) as $copyableUser)
                                <option value="{{ $copyableUser->id }}">{{ $copyableUser->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-note">إذا اخترت مستخدمًا هنا سيتم نسخ صلاحياته مباشرة بدل الاختيارات اليدوية.</p>
                    @endif
                    <div class="permissions-grid">
                        @foreach ($permissionOptions as $permissionKey => $permissionLabel)
                            <label class="permission-option">
                                <input type="checkbox" name="admin_permissions[]" value="{{ $permissionKey }}" @checked(in_array($permissionKey, $selectedPermissions, true))>
                                <span>{{ $permissionLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <button class="save" type="submit">حفظ الصلاحيات</button>
            </form>
        </template>
    @endforeach
@endsection
