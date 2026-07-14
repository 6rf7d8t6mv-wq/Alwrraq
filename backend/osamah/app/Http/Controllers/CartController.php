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
        $this->refreshOrderTotals($order);
        $order->load('files');

        return view('cart.show', compact('order'));
    }

    public function pay(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $order->load('files');
        $this->refreshOrderTotals($order);
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

    public function updateDelivery(Request $request, Order $order)
    {
        $this->authorizeOrder($order);
        abort_unless(in_array($order->service_type, ['notes', 'books', 'thesis', 'phd'], true), 404);

        $data = $request->validate([
            'delivery_method' => ['required', Rule::in([
                'branch_pickup',
                'islamic_university_delivery',
                'madinah_delivery',
                'redbox_delivery',
            ])],
            'delivery_unit' => ['required_if:delivery_method,islamic_university_delivery', 'nullable', 'string', 'max:50'],
            'delivery_floor' => ['required_if:delivery_method,islamic_university_delivery', 'nullable', 'string', 'max:50'],
            'delivery_room' => ['required_if:delivery_method,islamic_university_delivery', 'nullable', 'string', 'max:50'],
            'delivery_city' => ['required_if:delivery_method,redbox_delivery', 'nullable', 'string', 'max:100'],
            'delivery_district' => ['required_if:delivery_method,madinah_delivery,redbox_delivery', 'nullable', 'string', 'max:100'],
            'delivery_street' => ['required_if:delivery_method,madinah_delivery,redbox_delivery', 'nullable', 'string', 'max:100'],
            'delivery_map_url' => ['required_if:delivery_method,madinah_delivery,redbox_delivery', 'nullable', 'url', 'max:500'],
        ]);

        $this->refreshOrderTotals($order);
        $order->refresh();

        $deliveryFee = $this->deliveryFee($data['delivery_method'], $order);
        $needsAddress = in_array($data['delivery_method'], ['madinah_delivery', 'redbox_delivery'], true);
        $order->forceFill([
            'delivery_method' => $data['delivery_method'],
            'delivery_fee' => $deliveryFee,
            'delivery_unit' => $data['delivery_method'] === 'islamic_university_delivery' ? $data['delivery_unit'] : null,
            'delivery_floor' => $data['delivery_method'] === 'islamic_university_delivery' ? $data['delivery_floor'] : null,
            'delivery_room' => $data['delivery_method'] === 'islamic_university_delivery' ? $data['delivery_room'] : null,
            'delivery_city' => $data['delivery_method'] === 'redbox_delivery' ? $data['delivery_city'] : ($data['delivery_method'] === 'madinah_delivery' ? 'المدينة المنورة' : null),
            'delivery_district' => $needsAddress ? $data['delivery_district'] : null,
            'delivery_street' => $needsAddress ? $data['delivery_street'] : null,
            'delivery_map_url' => $needsAddress ? $data['delivery_map_url'] : null,
            'grand_total' => $order->subtotalAfterDiscount() + $deliveryFee,
        ])->save();

        if (! $request->expectsJson()) {
            return back()->with('status', 'تم حفظ طريقة الاستلام أو التوصيل.');
        }

        return response()->json([
            'success' => true,
            'delivery_fee' => $order->delivery_fee,
            'grand_total' => $order->grand_total,
        ]);
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

        if (in_array($order->service_type, ['notes', 'books', 'thesis', 'phd'], true) && blank($order->delivery_method)) {
            return 'اختر طريقة الاستلام أو التوصيل قبل الدفع.';
        }

        if (in_array($order->service_type, ['notes', 'books'], true)) {
            if ($order->files->contains(fn ($file) => blank($file->binding_type))) {
                return 'اختر نوع التغليف لكل ملف قبل الدفع.';
            }
        }

        if (in_array($order->service_type, ['thesis', 'phd'], true)) {
            $pdfFiles = $order->files->where('file_type', 'pdf');

            if ($pdfFiles->isEmpty()) {
                return 'ارفع ملف PDF قبل الدفع.';
            }

            if ($pdfFiles->contains(fn ($file) => blank($file->cover_color) || blank($file->writing_color))) {
                return 'اختر لون الرسالة ولون الكتابة لكل ملف PDF قبل الدفع.';
            }

            if ($pdfFiles->contains(fn ($file) => $file->writing_color === 'black' && !in_array($file->cover_color, ['beige', 'light_blue', 'light_green', 'white'], true))) {
                return 'الكتابة باللون الأسود متاحة فقط مع البيج أو الأزرق الفاتح أو الأخضر الفاتح أو الأبيض.';
            }
        }

        if ($order->service_type === 'thesis') {
            if ($order->files->where('file_type', 'pdf')->contains(fn ($file) => blank($file->thesis_project_type))) {
                return 'اختر نوع مشروع الرسالة لكل ملف PDF قبل الدفع.';
            }
        }

        if ($order->grand_total <= 0) {
            return 'لا يمكن إتمام طلب بدون إجمالي.';
        }

        return null;
    }

    private function refreshOrderTotals(Order $order): void
    {
        $order->load('files');

        $printTotal = 0;
        if (!in_array($order->service_type, ['formatting', 'research'], true)) {
            if (in_array($order->service_type, ['notes', 'books'], true)) {
                $printTotal = (int) $order->files->sum('print_price');
            } else {
                $filesForPrint = $order->files->where('file_type', 'pdf');
                $printUnits = $filesForPrint->sum(
                    fn ($file) => $file->pages * max(1, (int) $file->copies)
                );
                $printTotal = $this->printPrice((int) $printUnits, 1);
            }
        }

        $filesForBinding = in_array($order->service_type, ['thesis', 'phd'], true)
            ? $order->files->where('file_type', 'pdf')
            : $order->files;
        $bindingTotal = (int) $filesForBinding->sum('binding_price');
        $baseTotal = $printTotal + $bindingTotal;
        $discountAmount = min((int) $order->discount_amount, $baseTotal);
        $deliveryFee = in_array($order->service_type, ['notes', 'books', 'thesis', 'phd'], true)
            ? $this->deliveryFee($order->delivery_method, $order, max(0, $baseTotal - $discountAmount))
            : 0;

        $order->update([
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'grand_total' => max(0, $baseTotal - $discountAmount) + $deliveryFee,
        ]);
    }

    private function deliveryFee(?string $method, Order $order, ?int $subtotal = null): int
    {
        $subtotal ??= $order->subtotalAfterDiscount();

        return match ($method) {
            'islamic_university_delivery' => $subtotal >= 35 ? 0 : 5,
            'madinah_delivery' => 20,
            'redbox_delivery' => 30,
            default => 0,
        };
    }

    private function printPrice(int $pages, int $copies): int
    {
        return (int) ceil($pages / 15) * max(1, $copies);
    }
}
