<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\CartPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('phone', $credentials['phone'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الجوال أو كلمة المرور غير صحيحة',
            ], 422);
        }

        if (! $user->canLogin()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب موقوف أو ممنوع من تسجيل الدخول.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'token' => Crypt::encryptString((string) $user->id),
            'user' => $this->userPayload($user),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(6), 'regex:/^[A-Za-z0-9]+$/'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'token' => Crypt::encryptString((string) $user->id),
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'user' => $this->userPayload(Auth::user()),
        ]);
    }

    public function orders(CartPricingService $cartPricing)
    {
        $cartOrders = Order::query()
            ->where('user_id', Auth::id())
            ->where('payment_status', 'unpaid')
            ->withCount('files')
            ->with('files')
            ->latest()
            ->get();

        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);

        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->withCount('files')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'cart_orders' => $cartOrders,
            'cart_summary' => $cartSummary,
        ]);
    }

    public function cart(Order $order)
    {
        $this->authorizeOrder($order);

        return response()->json([
            'success' => true,
            'order' => $order->load('files'),
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        return response()->json([
            'success' => false,
            'message' => 'انتقل إلى صفحة الدفع الآمنة في الموقع أو التطبيق لإتمام الدفع عبر ميسر.',
            'checkout_url' => route('cart.payment', ['order_ids' => [$order->id]]),
        ], 410);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05[0-9]{8}$/', Rule::unique('users', 'phone')->ignore($user->id)],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بياناتك بنجاح.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function updateAddress(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'district' => ['required', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:180'],
            'postal_code' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ]);

        $data['address'] = implode(' - ', [
            'المملكة العربية السعودية',
            $data['city'],
            $data['district'],
            $data['street'],
            $data['postal_code'],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث عنوانك بنجاح.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6), 'regex:/^[A-Za-z0-9]+$/'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور القديمة غير صحيحة.',
            ], 422);
        }

        $user->update([
            'password' => $data['password'],
        ]);
        $user->biometricLoginTokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح.',
        ]);
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);
    }

    private function userPayload(User $user): array
    {
        $accountType = $user->role === 'customer'
            ? 'customer'
            : ($user->admin_permissions === null ? 'admin' : 'employee');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'role' => $user->role,
            'account_type' => $accountType,
            'permissions' => $user->role === 'admin' ? ($user->admin_permissions ?? []) : [],
            'has_full_admin_access' => $user->role === 'admin' && $user->admin_permissions === null,
            'address' => $user->address,
            'city' => $user->city,
            'district' => $user->district,
            'street' => $user->street,
            'postal_code' => $user->postal_code,
        ];
    }
}
