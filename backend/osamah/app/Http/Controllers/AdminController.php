<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDeliveredFile;
use App\Models\OrderFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    private const ADMIN_PERMISSION_KEYS = [
        'reports_view',
        'users_view',
        'users_create',
        'users_update',
        'users_delete',
        'users_status',
        'users_login_block',
        'users_password_reset',
        'users_phone_update',
        'users_email_update',
        'users_verify',
        'users_permissions_manage',
        'users_permissions_copy',
        'customers_view',
        'customers_create',
        'customers_update',
        'customers_delete',
        'customers_status',
        'customers_login_block',
        'customers_password_reset',
        'customers_phone_update',
        'customers_email_update',
        'customers_verify',
        'orders_view',
        'orders_delete',
        'files_download',
        'delivered_files_upload',
        'delivered_files_download',
        'delivered_files_delete',
        'invoices_view',
        'payments_view',
        'discounts_apply',
    ];

    public function dashboard()
    {
        $this->ensureAdmin();

        $orders = Order::query()
            ->with(['user', 'files', 'deliveredFiles'])
            ->latest()
            ->get();

        $users = User::query()
            ->withCount('orders')
            ->latest()
            ->get();

        $stats = [
            'orders' => $orders->count(),
            'new_orders' => $orders
                ->whereNull('admin_opened_at')
                ->filter(fn ($order) => ! ($order->payment_status === 'paid' && in_array($order->status, ['completed', 'finished'], true)))
                ->count(),
            'in_progress_orders' => $orders
                ->whereNotNull('admin_opened_at')
                ->filter(fn ($order) => ! ($order->payment_status === 'paid' && in_array($order->status, ['completed', 'finished'], true)))
                ->count(),
            'completed_orders' => $orders
                ->where('payment_status', 'paid')
                ->whereIn('status', ['completed', 'finished'])
                ->count(),
            'paid_orders' => $orders->where('payment_status', 'paid')->count(),
            'unpaid_orders' => $orders->where('payment_status', '!=', 'paid')->count(),
            'customers' => $users->where('role', 'customer')->count(),
            'admins' => $users->where('role', 'admin')->whereNotNull('admin_permissions')->count(),
            'print_total' => $orders->sum('print_total'),
            'binding_total' => $orders->sum('binding_total'),
            'grand_total' => $orders->sum('grand_total'),
        ];

        return view('admin.dashboard', compact('orders', 'users', 'stats'));
    }

    public function orders(Request $request)
    {
        $this->ensureAdmin();
        $this->ensurePermission('orders_view');

        Order::query()
            ->whereNull('admin_notification_seen_at')
            ->update(['admin_notification_seen_at' => now()]);

        $search = trim((string) $request->query('search', ''));
        $statusFilter = (string) $request->query('status_filter', '');

        if (! in_array($statusFilter, ['new', 'in_progress', 'completed'], true)) {
            $statusFilter = '';
        }

        $orders = Order::query()
            ->with(['user', 'files', 'deliveredFiles'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                });
            })
            ->when($statusFilter === 'new', function ($query) {
                $query->whereNull('admin_opened_at')
                    ->where(function ($statusQuery) {
                        $statusQuery->whereNotIn('status', ['completed', 'finished'])
                            ->orWhere('payment_status', '!=', 'paid');
                    });
            })
            ->when($statusFilter === 'in_progress', function ($query) {
                $query->whereNotNull('admin_opened_at')
                    ->where(function ($statusQuery) {
                        $statusQuery->whereNotIn('status', ['completed', 'finished'])
                            ->orWhere('payment_status', '!=', 'paid');
                    });
            })
            ->when($statusFilter === 'completed', function ($query) {
                $query->where('payment_status', 'paid')
                    ->whereIn('status', ['completed', 'finished']);
            })
            ->latest()
            ->get();

        return view('admin.orders', compact('orders', 'search', 'statusFilter'));
    }

    public function applyOrderDiscount(Request $request, Order $order)
    {
        $this->ensureAdmin();
        $this->ensurePermission('discounts_apply');

        if ($order->payment_status === 'paid') {
            return back()->withErrors(['discount' => 'لا يمكن إضافة خصم بعد دفع الطلب.']);
        }

        $data = $request->validate([
            'discount_code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            'discount_amount' => ['required', 'integer', 'min:1'],
        ]);

        $baseTotal = $order->baseTotal();
        if ((int) $data['discount_amount'] >= $baseTotal) {
            return back()->withErrors(['discount' => 'قيمة الخصم يجب أن تكون أقل من إجمالي الطلب قبل الخصم.']);
        }

        $discountAmount = (int) $data['discount_amount'];
        $subtotal = max(0, $baseTotal - $discountAmount);
        $deliveryFee = match ($order->delivery_method) {
            'islamic_university_delivery' => $subtotal >= 35 ? 0 : 5,
            'madinah_delivery' => 20,
            'redbox_delivery' => 30,
            default => 0,
        };

        $order->update([
            'discount_code' => strtoupper($data['discount_code']),
            'discount_amount' => $discountAmount,
            'discount_applied_by' => Auth::id(),
            'discount_applied_at' => now(),
            'delivery_fee' => $deliveryFee,
            'grand_total' => $subtotal + $deliveryFee,
        ]);

        return back()->with('status', 'تم تطبيق كود الخصم على الطلب.');
    }

    public function users(Request $request)
    {
        $this->ensureAdmin();
        abort_unless(Auth::user()->hasAnyAdminPermission([
            'users_view',
            'users_create',
            'users_update',
            'users_delete',
            'users_permissions_manage',
        ]), 403);

        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->where('role', 'admin')
            ->whereNotNull('admin_permissions')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->latest()
            ->get();

        $permissionOptions = $this->permissionOptions();

        $copyableUsers = User::query()
            ->where('role', 'admin')
            ->whereNotNull('admin_permissions')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.users', compact('users', 'search', 'permissionOptions', 'copyableUsers'));
    }

    public function customers(Request $request)
    {
        $this->ensureAdmin();
        abort_unless(Auth::user()->hasAnyAdminPermission([
            'customers_view',
            'customers_create',
            'customers_update',
            'customers_delete',
        ]), 403);

        $search = trim((string) $request->query('search', ''));

        $customers = User::query()
            ->where('role', 'customer')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->latest()
            ->get();

        return view('admin.customers', compact('customers', 'search'));
    }

    public function settings()
    {
        $this->ensureAdmin();

        return view('admin.settings');
    }

    public function storeUser(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255', 'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', 'unique:users,email'],
            'password' => ['required', Password::min(6), 'regex:/^[A-Za-z0-9]+$/', 'confirmed'],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'admin_permissions' => ['nullable', 'array'],
            'admin_permissions.*' => ['string', Rule::in(self::ADMIN_PERMISSION_KEYS)],
        ]);

        $this->ensurePermission($data['role'] === 'admin' ? 'users_create' : 'customers_create');

        if ($data['role'] === 'admin') {
            $data['admin_permissions'] = $this->adminPermissionsFromRequest($request);
        } else {
            $data['admin_permissions'] = null;
        }

        unset($data['password_confirmation']);

        $user = User::query()->create($data);

        return redirect()
            ->route($user->role === 'admin' ? 'admin.users' : 'admin.customers')
            ->with('status', 'تم إضافة المستخدم بنجاح.');
    }

    public function updateUser(Request $request, User $user)
    {
        $this->ensureAdmin();

        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => 'عدّل حسابك من صفحة الإعدادات فقط.']);
        }

        if ($user->role === 'admin' && $user->admin_permissions === null) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', Password::min(6), 'regex:/^[A-Za-z0-9]+$/', 'confirmed'],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'is_active' => ['nullable', 'boolean'],
            'login_blocked' => ['nullable', 'boolean'],
            'account_verified' => ['nullable', 'boolean'],
        ]);

        $prefix = $user->role === 'admin' ? 'users' : 'customers';

        if (($data['name'] ?? null) !== $user->name) {
            $this->ensurePermission($prefix . '_update');
        }

        if (($data['phone'] ?? null) !== $user->phone) {
            $this->ensurePermission($prefix . '_phone_update');
        }

        if (filled($data['password'] ?? null)) {
            $this->ensurePermission($prefix . '_password_reset');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        if ($request->has('is_active')) {
            $this->ensurePermission($prefix . '_status');
            $data['is_active'] = $request->boolean('is_active');
        } else {
            unset($data['is_active']);
        }

        if ($request->has('login_blocked')) {
            $this->ensurePermission($prefix . '_login_block');
            $data['login_blocked'] = $request->boolean('login_blocked');
        } else {
            unset($data['login_blocked']);
        }

        if ($request->has('account_verified')) {
            $this->ensurePermission($prefix . '_verify');
            $data['account_verified_at'] = $request->boolean('account_verified') ? now() : null;
        }
        unset($data['account_verified']);

        $user->update($data);

        return redirect()
            ->route($user->role === 'admin' ? 'admin.users' : 'admin.customers')
            ->with('status', 'تم تحديث بيانات المستخدم.');
    }

    public function updateUserEmail(Request $request, User $user)
    {
        $this->ensureAdmin();

        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => 'عدّل بريد حسابك من صفحة الإعدادات فقط.']);
        }

        if ($user->role === 'admin' && $user->admin_permissions === null) {
            abort(403);
        }

        $prefix = $user->role === 'admin' ? 'users' : 'customers';
        $this->ensurePermission($prefix . '_email_update');

        $data = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255', 'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update(['email' => $data['email']]);

        return redirect()
            ->route($user->role === 'admin' ? 'admin.users' : 'admin.customers')
            ->with('status', 'تم حفظ البريد الإلكتروني.');
    }

    public function updateUserPermissions(Request $request, User $user)
    {
        $this->ensureAdmin();
        $this->ensurePermission('users_permissions_manage');

        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => 'عدّل صلاحيات حسابك من مدير النظام الأساسي فقط.']);
        }

        if ($user->role !== 'admin' || $user->admin_permissions === null) {
            abort(403);
        }

        $request->validate([
            'copy_permissions_from' => ['nullable', 'integer', 'exists:users,id'],
            'admin_permissions' => ['nullable', 'array'],
            'admin_permissions.*' => ['string', Rule::in(self::ADMIN_PERMISSION_KEYS)],
        ]);

        if ($request->filled('copy_permissions_from')) {
            $this->ensurePermission('users_permissions_copy');

            $sourceUser = User::query()
                ->where('role', 'admin')
                ->findOrFail($request->integer('copy_permissions_from'));

            $user->update([
                'admin_permissions' => $sourceUser->admin_permissions ?? self::ADMIN_PERMISSION_KEYS,
            ]);

            return redirect()->route('admin.users')->with('status', 'تم نسخ صلاحيات المستخدم.');
        }

        $user->update([
            'admin_permissions' => $this->adminPermissionsFromRequest($request),
        ]);

        return redirect()->route('admin.users')->with('status', 'تم تحديث صلاحيات المستخدم.');
    }

    public function destroyUser(User $user)
    {
        $this->ensureAdmin();

        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => 'لا يمكنك حذف حسابك الحالي.']);
        }

        if ($user->role === 'admin' && $user->admin_permissions === null) {
            return back()->withErrors(['user' => 'لا يمكن حذف مدير النظام الأساسي من صفحة المستخدمين.']);
        }

        $this->ensurePermission($user->role === 'admin' ? 'users_delete' : 'customers_delete');

        $route = $user->role === 'admin' ? 'admin.users' : 'admin.customers';
        $user->delete();

        return redirect()->route($route)->with('status', 'تم حذف المستخدم.');
    }

    public function updateSettings(Request $request)
    {
        $this->ensureAdmin();

        $user = Auth::user();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', Password::min(6), 'regex:/^[A-Za-z0-9]+$/', 'confirmed'],
        ];

        if ($user->admin_permissions !== null) {
            $rules['current_password'] = ['nullable', 'required_with:password', 'current_password'];
        }

        $data = $request->validate($rules);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        unset($data['current_password'], $data['password_confirmation']);

        $user->update($data);

        return redirect()->route('admin.settings')->with('status', 'تم تحديث إعدادات حسابك.');
    }

    public function download(OrderFile $file)
    {
        $this->ensureAdmin();
        $this->ensurePermission('files_download');

        $absolutePath = storage_path('app/' . $file->path);

        abort_unless(is_file($absolutePath), 404);

        if ($file->order->payment_status === 'paid' && ! in_array($file->order->service_type, ['formatting', 'research'], true)) {
            $file->order->update([
                'status' => 'completed',
                'customer_notification_seen_at' => null,
            ]);
        }

        return Response::download($absolutePath, $file->original_name);
    }

    public function uploadDeliveredFile(Request $request, Order $order)
    {
        $this->ensureAdmin();
        $this->ensurePermission('delivered_files_upload');
        abort_unless(in_array($order->service_type, ['formatting', 'research'], true), 404);

        $data = $request->validate([
            'delivered_file' => ['required', 'file', 'max:51200'],
        ]);

        $file = $data['delivered_file'];
        $storagePath = 'delivered/orders/' . $order->id;
        $fullPath = storage_path('app/' . $storagePath);

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = 'delivered_' . now()->format('YmdHis') . '_' . $order->id . ($extension ? '.' . $extension : '');
        $file->move($fullPath, $storedName);

        $path = $storagePath . '/' . $storedName;

        $order->deliveredFiles()->create([
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => filesize($fullPath . '/' . $storedName) ?: 0,
            'uploaded_by' => Auth::id(),
        ]);

        $order->update([
            'status' => $order->payment_status === 'paid' ? 'completed' : 'processing',
            'customer_notification_seen_at' => null,
            'delivered_file_original_name' => $file->getClientOriginalName(),
            'delivered_file_stored_name' => $storedName,
            'delivered_file_path' => $path,
            'delivered_file_mime' => $file->getClientMimeType(),
            'delivered_file_size' => filesize($fullPath . '/' . $storedName) ?: 0,
            'delivered_file_uploaded_at' => now(),
        ]);

        return back()->with('status', 'تم إرفاق ملف التسليم للعميل بنجاح.');
    }

    public function openOrder(Order $order)
    {
        $this->ensureAdmin();

        if (blank($order->admin_opened_at)) {
            $order->update(['admin_opened_at' => now()]);
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.orders')->with('status', 'تم فتح الطلب.');
    }

    public function downloadDeliveredFile(OrderDeliveredFile $deliveredFile)
    {
        $this->ensureAdmin();
        $this->ensurePermission('delivered_files_download');

        $absolutePath = storage_path('app/' . $deliveredFile->path);

        abort_unless(File::isFile($absolutePath), 404);

        if (request()->boolean('view')) {
            return response()->file($absolutePath, [
                'Content-Type' => $deliveredFile->mime ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($deliveredFile->original_name) . '"',
            ]);
        }

        return Response::download($absolutePath, $deliveredFile->original_name);
    }

    public function destroyDeliveredFile(OrderDeliveredFile $deliveredFile)
    {
        $this->ensureAdmin();
        $this->ensurePermission('delivered_files_delete');

        $absolutePath = storage_path('app/' . $deliveredFile->path);
        if (File::isFile($absolutePath)) {
            File::delete($absolutePath);
        }

        $order = $deliveredFile->order;
        $deliveredFile->delete();

        $latestFile = $order->deliveredFiles()->first();
        $order->update([
            'delivered_file_original_name' => $latestFile?->original_name,
            'delivered_file_stored_name' => $latestFile?->stored_name,
            'delivered_file_path' => $latestFile?->path,
            'delivered_file_mime' => $latestFile?->mime,
            'delivered_file_size' => $latestFile?->size,
            'delivered_file_uploaded_at' => $latestFile?->created_at,
        ]);

        return back()->with('status', 'تم حذف ملف التسليم بنجاح.');
    }

    public function destroyOrder(Order $order)
    {
        $this->ensureAdmin();
        $this->ensurePermission('orders_delete');

        $order->load(['files', 'deliveredFiles']);

        foreach ($order->files as $file) {
            $absolutePath = storage_path('app/' . $file->path);
            if (File::isFile($absolutePath)) {
                File::delete($absolutePath);
            }
        }

        foreach ($order->deliveredFiles as $deliveredFile) {
            $absolutePath = storage_path('app/' . $deliveredFile->path);
            if (File::isFile($absolutePath)) {
                File::delete($absolutePath);
            }
        }

        if (filled($order->delivered_file_path)) {
            $deliveredPath = storage_path('app/' . $order->delivered_file_path);
            if (File::isFile($deliveredPath)) {
                File::delete($deliveredPath);
            }
        }

        $order->delete();

        return redirect()->route('admin.orders')->with('status', 'تم حذف الطلب بنجاح.');
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(Auth::user()?->hasAdminPermission($permission), 403);
    }

    private function adminPermissionsFromRequest(Request $request): array
    {
        return collect($request->input('admin_permissions', []))
            ->filter(fn ($permission) => in_array($permission, self::ADMIN_PERMISSION_KEYS, true))
            ->values()
            ->all();
    }

    private function permissionOptions(): array
    {
        return [
            'reports_view' => 'التقارير: مشاهدة لوحة الأرقام والإيرادات',
            'users_view' => 'المستخدمين: مشاهدة المستخدمين',
            'users_create' => 'المستخدمين: إنشاء مستخدم جديد',
            'users_update' => 'المستخدمين: تعديل بيانات المستخدم',
            'users_delete' => 'المستخدمين: حذف المستخدم',
            'users_status' => 'المستخدمين: إيقاف أو تفعيل الحساب',
            'users_login_block' => 'المستخدمين: منع أو السماح بتسجيل الدخول',
            'users_password_reset' => 'المستخدمين: إعادة تعيين كلمة المرور',
            'users_phone_update' => 'المستخدمين: تغيير رقم الجوال',
            'users_email_update' => 'المستخدمين: تغيير البريد الإلكتروني',
            'users_verify' => 'المستخدمين: توثيق الحساب',
            'users_permissions_manage' => 'الصلاحيات: إعطاء أو إزالة صلاحيات المستخدم',
            'users_permissions_copy' => 'الصلاحيات: نسخ صلاحيات من مستخدم آخر',
            'customers_view' => 'العملاء: مشاهدة العملاء',
            'customers_create' => 'العملاء: إنشاء عميل جديد',
            'customers_update' => 'العملاء: تعديل بيانات العميل',
            'customers_delete' => 'العملاء: حذف العميل',
            'customers_status' => 'العملاء: إيقاف أو تفعيل الحساب',
            'customers_login_block' => 'العملاء: منع أو السماح بتسجيل الدخول',
            'customers_password_reset' => 'العملاء: إعادة تعيين كلمة المرور',
            'customers_phone_update' => 'العملاء: تغيير رقم الجوال',
            'customers_email_update' => 'العملاء: تغيير البريد الإلكتروني',
            'customers_verify' => 'العملاء: توثيق الحساب',
            'orders_view' => 'الطلبات: مشاهدة جميع الطلبات',
            'orders_delete' => 'الطلبات: حذف الطلب',
            'files_download' => 'الملفات: تحميل ملفات العملاء',
            'delivered_files_upload' => 'ملفات التسليم: إرفاق ملف للعميل',
            'delivered_files_download' => 'ملفات التسليم: عرض أو تحميل الملفات المرسلة',
            'delivered_files_delete' => 'ملفات التسليم: حذف ملف مرسل للعميل',
            'invoices_view' => 'المالية: مشاهدة وإصدار الفاتورة',
            'payments_view' => 'المالية: مشاهدة حالة المدفوعات والمبالغ',
            'discounts_apply' => 'المالية: منح كود خصم قبل الدفع',
        ];
    }

    private function firstAllowedAdminRoute(): string
    {
        $user = Auth::user();

        if ($user?->hasAdminPermission('orders_view')) {
            return 'admin.orders';
        }

        if ($user?->hasAnyAdminPermission(['users_view', 'users_create', 'users_update', 'users_delete'])) {
            return 'admin.users';
        }

        if ($user?->hasAnyAdminPermission(['customers_view', 'customers_create', 'customers_update', 'customers_delete'])) {
            return 'admin.customers';
        }

        return 'admin.settings';
    }
}
