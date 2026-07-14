<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public function orders()
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->withCount('files')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
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

        $order->load('files');

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'تم دفع هذا الطلب مسبقًا.',
            ], 422);
        }

        if ($order->files->isEmpty() || $order->grand_total <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إتمام طلب بدون ملفات أو إجمالي.',
            ], 422);
        }

        if (in_array($order->service_type, ['notes', 'books'], true) && $order->files->contains(fn ($file) => blank($file->binding_type))) {
            return response()->json([
                'success' => false,
                'message' => 'اختر نوع التغليف لكل ملف قبل الدفع.',
            ], 422);
        }

        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['apple_pay', 'card'])],
            'card_name' => ['required_if:payment_method,card', 'nullable', 'string', 'max:255'],
            'card_number' => ['required_if:payment_method,card', 'nullable', 'string', 'regex:/^[0-9 ]{12,23}$/'],
            'card_expiry' => ['required_if:payment_method,card', 'nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/'],
            'card_cvc' => ['required_if:payment_method,card', 'nullable', 'string', 'regex:/^[0-9]{3,4}$/'],
        ]);

        $order->update([
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => $data['payment_method'],
            'payment_reference' => 'PAY-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
            'paid_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الدفع واعتماد الطلب بنجاح.',
            'order' => $order->fresh('files'),
        ]);
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
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'role' => $user->role,
            'address' => $user->address,
            'city' => $user->city,
            'district' => $user->district,
            'street' => $user->street,
            'postal_code' => $user->postal_code,
        ];
    }
}
