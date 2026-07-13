@extends('admin.layout')

@section('title', 'المستخدمين - لوحة المدير')

@section('content')
    <div class="page-title">
        <div>
            <h1>المستخدمين</h1>
            <p class="subtitle">إدارة المستخدمين الذين يعملون على الطلبات داخل لوحة النظام.</p>
        </div>
        @if (auth()->user()->hasAdminPermission('users_create'))
            <button class="save" type="button" onclick="openAdminModal('إضافة مستخدم', 'create-admin-template')">إضافة مستخدم</button>
        @endif
    </div>

    <div class="panel">
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
                        <th>الطلبات</th>
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
                            <td data-label="الطلبات">{{ $user->orders_count }}</td>
                            <td data-label="الصلاحية"><span class="badge">مستخدم إداري</span></td>
                            <td data-label="الإجراءات">
                                <div class="actions">
                                    @if (!auth()->user()->is($user) && auth()->user()->hasAdminPermission('users_update'))
                                        <button class="ghost" type="button" onclick="openAdminModal('تعديل بيانات المستخدم', 'edit-admin-{{ $user->id }}')">تعديل البيانات</button>
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
                        <tr><td colspan="5" class="empty">لا يوجد مستخدمين.</td></tr>
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
                <div><label>الاسم</label><input name="name" required></div>
                <div><label>رقم الجوال</label><input name="phone" required></div>
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
        @endphp
        <template id="edit-admin-{{ $user->id }}">
            <form method="post" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('patch')
                <input type="hidden" name="role" value="admin">
                <div class="form-section">
                    <h3 class="form-section-title">بيانات المستخدم</h3>
                    <div class="form-grid">
                        <div><label>الاسم</label><input name="name" value="{{ $user->name }}" required></div>
                        <div><label>رقم الجوال</label><input name="phone" value="{{ $user->phone }}" required></div>
                    </div>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">تغيير كلمة المرور</h3>
                    <p class="form-note">اضغط الزر إذا كنت تريد تغيير كلمة مرور هذا المستخدم.</p>
                    <button class="ghost" type="button" onclick="toggleInlinePasswordPanel(this)">تغيير كلمة المرور</button>
                    <div class="form-grid inline-password-panel" style="display: none; margin-top: 12px;">
                        <div><label>كلمة المرور الجديدة</label><input name="password" type="password" placeholder="كلمة مرور جديدة"></div>
                        <div><label>تأكيد كلمة المرور الجديدة</label><input name="password_confirmation" type="password" placeholder="تأكيد كلمة المرور"></div>
                    </div>
                </div>
                <button class="save" type="submit">حفظ التعديل</button>
            </form>
        </template>

        <template id="permissions-admin-{{ $user->id }}">
            <form method="post" action="{{ route('admin.users.permissions.update', $user) }}">
                @csrf
                @method('patch')
                <div class="form-section">
                    <h3 class="form-section-title">صلاحيات المستخدم</h3>
                    <p class="form-note">حدد العمليات التي يستطيع {{ $user->name }} تنفيذها داخل لوحة النظام.</p>
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
