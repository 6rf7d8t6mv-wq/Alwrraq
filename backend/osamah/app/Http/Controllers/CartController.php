<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load('files');

        return view('cart.show', compact('order'));
    }

    public function pay(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $order->load('files');
        if ($message = $this->orderPaymentBlockMessage($order)) {
            return back()->withErrors([
                'order' => $message,
            ]);
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
            'payment_reference' => 'PAY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'paid_at' => now(),
        ]);

        return redirect()->route('cart.show', $order)->with('status', 'تم الدفع واعتماد الطلب بنجاح.');
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);
    }

    private function orderPaymentBlockMessage(Order $order): ?string
    {
        if ($order->payment_status === 'paid') {
            return 'تم دفع هذا الطلب مسبقًا.';
        }

        if ($order->files->isEmpty()) {
            return 'لا يمكن إتمام طلب بدون ملفات.';
        }

        if ($order->grand_total <= 0) {
            return 'لا يمكن إتمام طلب بدون إجمالي.';
        }

        if ($order->service_type === 'notes') {
            if ($order->files->contains(fn ($file) => blank($file->binding_type))) {
                return 'اختر نوع التغليف لكل ملف قبل الدفع.';
            }
        }

        if (in_array($order->service_type, ['thesis', 'phd'], true)) {
            if ($order->files->contains(fn ($file) => blank($file->university_name))) {
                return 'اختر الجامعة أو المعهد لكل ملف قبل الدفع.';
            }
        }

        if ($order->service_type === 'thesis') {
            if ($order->files->contains(fn ($file) => $file->file_type === 'pdf' && blank($file->thesis_project_type))) {
                return 'اختر نوع مشروع الرسالة لكل ملف PDF قبل الدفع.';
            }
        }

        return null;
    }
}
