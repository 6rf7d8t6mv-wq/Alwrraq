@extends('admin.layout')

@section('title', 'الإعدادات - لوحة المدير')

@section('content')
    <div class="page-title">
        <div>
            <h1>الإعدادات</h1>
            <p class="subtitle">تعديل بيانات حساب المدير الحالي.</p>
        </div>
    </div>

    <div class="panel">
        <form method="post" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('patch')
            <div class="form-section">
                <h3 class="form-section-title">بيانات المستخدم</h3>
                <div class="form-grid">
                    <div><label>اسمك</label><input name="name" value="{{ auth()->user()->name }}" required></div>
                    <div><label>رقم هاتفك</label><input name="phone" value="{{ auth()->user()->phone }}" required></div>
                </div>
            </div>
            <div class="form-section">
                <h3 class="form-section-title">تغيير كلمة المرور</h3>
                <p class="form-note">يمكنك فتح حقول كلمة المرور فقط عند الحاجة.</p>
                <button class="ghost" type="button" onclick="togglePasswordPanel()">تغيير كلمة المرور</button>
                <div id="passwordPanel" class="form-grid" style="display: none; margin-top: 12px;">
                    @if (auth()->user()->admin_permissions !== null)
                        <div class="full"><label>كلمة المرور القديمة</label><input name="current_password" type="password" placeholder="مطلوبة عند تغيير كلمة المرور"></div>
                    @endif
                    <div><label>كلمة المرور الجديدة</label><input name="password" type="password" placeholder="كلمة مرور جديدة"></div>
                    <div><label>تأكيد كلمة المرور الجديدة</label><input name="password_confirmation" type="password" placeholder="تأكيد كلمة المرور"></div>
                </div>
            </div>
            <button class="save" type="submit">حفظ الإعدادات</button>
        </form>
    </div>

    @if (auth()->user()->admin_permissions !== null)
        <div class="panel">
            <div class="form-section">
                <h3 class="form-section-title">حذف الحساب</h3>
                <p class="form-note">يمكن حذف حساب المستخدم الحالي نهائيًا عند الحاجة.</p>
                <form method="post" action="{{ route('account.profile.destroy') }}" onsubmit="return confirm('هل أنت متأكد من حذف حسابك نهائيًا؟');">
                    @csrf
                    @method('delete')
                    <button class="danger" type="submit">حذف حسابي</button>
                </form>
            </div>
        </div>
    @endif

    <script>
        function togglePasswordPanel() {
            const panel = document.getElementById('passwordPanel');
            panel.style.display = panel.style.display === 'none' ? 'grid' : 'none';
        }
    </script>
@endsection
