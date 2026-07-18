<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Services\CartPricingService;
use App\Services\Payments\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function showAll(CartPricingService $cartPricing)
    {
        $cartOrders = $this->cartOrders();
        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);
        $paymentPage = false;

        return response()
            ->view('cart.show', compact('cartOrders', 'cartSummary', 'paymentPage'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function payment(CartPricingService $cartPricing)
    {
        $cartOrders = $this->cartOrders();
        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);
        $paymentPage = true;

        return response()
            ->view('cart.show', compact('cartOrders', 'cartSummary', 'paymentPage'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function show(Order $order, CartPricingService $cartPricing)
    {
        $this->authorizeOrder($order);

        return redirect()->route('cart.index');
    }

    public function payAll(Request $request, PaymentGatewayService $payments, CartPricingService $cartPricing)
    {
        $cartOrders = $this->cartOrders();
        if ($cartOrders->isEmpty()) {
            return redirect()->route('orders.index')->withErrors([
                'order' => 'لا توجد طلبات في السلة للدفع.',
            ]);
        }

        $cartPricing->refreshCartTotals($cartOrders);
        $cartOrders = $this->cartOrders();

        foreach ($cartOrders as $order) {
            if ($message = $this->orderPaymentBlockMessage($order, true)) {
                return redirect()->route('orders.index')->withErrors([
                    'order' => 'خدمة '.$order->service_type.': '.$message,
                ]);
            }
        }

        $data = $this->validatePayment($request);

        $failedPayment = null;
        foreach ($cartOrders as $order) {
            $payment = $payments->createPayment($order, $data['payment_method']);
            $payments->markOrderFromPayment($order, $payment);

            if ($payment->payment_status !== 'paid') {
                $failedPayment = $payment;
            }
        }

        if ($failedPayment) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['payment' => 'تعذر إتمام عملية الدفع. تأكد من طريقة الدفع وحاول مرة أخرى.']);
        }

        return redirect()->route('orders.index')->with('status', 'تم الدفع واعتماد الطلب بجميع خدماته بنجاح.');
    }

    public function pay(Request $request, Order $order, PaymentGatewayService $payments, CartPricingService $cartPricing)
    {
        $this->authorizeOrder($order);

        $order->load('files');
        $cartPricing->refreshOrderTotals($order);
        $order->refresh()->load('files');
        if ($message = $this->orderPaymentBlockMessage($order)) {
            return back()->withErrors([
                'order' => $message,
            ]);
        }

        $data = $this->validatePayment($request);

        $payment = $payments->createPayment($order, $data['payment_method']);
        $payments->markOrderFromPayment($order, $payment);

        if ($payment->payment_status !== 'paid') {
            return redirect()
                ->route('cart.show', $order)
                ->withErrors(['payment' => 'تعذر إتمام عملية الدفع. تأكد من طريقة الدفع وحاول مرة أخرى.']);
        }

        return redirect()->route('orders.index')->with('status', 'تم الدفع واعتماد الطلب بنجاح.');
    }

    public function updateDelivery(Request $request, Order $order, CartPricingService $cartPricing)
    {
        $this->authorizeOrder($order);
        abort_unless(in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true), 404);

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

        if ($data['delivery_method'] === 'redbox_delivery' && $this->isMadinahCity($data['delivery_city'] ?? '')) {
            throw ValidationException::withMessages([
                'delivery_city' => 'خيار خارج المدينة لا يقبل المدينة المنورة. اكتب اسم المدينة خارج المدينة المنورة.',
            ]);
        }

        $cartOrders = $this->cartOrders();
        $deliveryOrders = $cartOrders->filter(
            fn (Order $cartOrder) => in_array($cartOrder->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true)
        );
        $needsAddress = in_array($data['delivery_method'], ['madinah_delivery', 'redbox_delivery'], true);
        $deliveryOrders->each(function (Order $cartOrder) use ($data, $needsAddress) {
            $cartOrder->forceFill([
                'delivery_method' => $data['delivery_method'],
                'delivery_fee' => 0,
                'delivery_unit' => $data['delivery_method'] === 'islamic_university_delivery' ? $data['delivery_unit'] : null,
                'delivery_floor' => $data['delivery_method'] === 'islamic_university_delivery' ? $data['delivery_floor'] : null,
                'delivery_room' => $data['delivery_method'] === 'islamic_university_delivery' ? $data['delivery_room'] : null,
                'delivery_city' => $data['delivery_method'] === 'redbox_delivery' ? $data['delivery_city'] : ($data['delivery_method'] === 'madinah_delivery' ? 'المدينة المنورة' : null),
                'delivery_district' => $needsAddress ? $data['delivery_district'] : null,
                'delivery_street' => $needsAddress ? $data['delivery_street'] : null,
                'delivery_map_url' => $needsAddress ? $data['delivery_map_url'] : null,
            ])->save();
        });

        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);

        if (! $request->expectsJson()) {
            return back()->with('status', 'تم حفظ طريقة الاستلام أو التوصيل لجميع خدمات السلة.');
        }

        return response()->json([
            'success' => true,
            'delivery_fee' => $cartSummary['delivery_fee'],
            'grand_total' => $cartSummary['grand_total'],
        ]);
    }

    private function cartOrders()
    {
        $cartQuery = Order::query()
            ->where('user_id', Auth::id())
            ->where('payment_status', '!=', 'paid');

        (clone $cartQuery)
            ->whereDoesntHave('files')
            ->whereDoesntHave('productItems')
            ->delete();

        return $cartQuery
            ->with(['files', 'productItems.product', 'deliveredFiles'])
            ->withCount('files')
            ->latest()
            ->get();
    }

    private function validatePayment(Request $request): array
    {
        return $request->validate([
            'payment_method' => ['required', Rule::in(PaymentGatewayService::METHODS)],
            'card_name' => ['required_if:payment_method,mada,visa,mastercard', 'nullable', 'string', 'max:255'],
            'card_number' => ['required_if:payment_method,mada,visa,mastercard', 'nullable', 'string', 'regex:/^[0-9 ]{12,23}$/'],
            'card_expiry' => ['required_if:payment_method,mada,visa,mastercard', 'nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/'],
            'card_cvc' => ['required_if:payment_method,mada,visa,mastercard', 'nullable', 'string', 'regex:/^[0-9]{3,4}$/'],
        ]);
    }

    public function applyDiscount(Request $request, Order $order, CartPricingService $cartPricing)
    {
        $this->authorizeOrder($order);

        if ($order->payment_status === 'paid') {
            return $this->discountError($request, 'لا يمكن تطبيق خصم بعد دفع الطلب.');
        }

        $data = $request->validate([
            'discount_code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $discountCode = DiscountCode::query()
            ->where('code', strtoupper($data['discount_code']))
            ->where('is_active', true)
            ->first();

        if (! $discountCode) {
            return $this->discountError($request, 'كود الخصم غير صحيح أو غير مفعل.');
        }

        $cartOrders = $this->cartOrders();
        $cartPricing->refreshCartTotals($cartOrders);
        $cartOrders = $this->cartOrders();
        $baseTotal = (float) $cartOrders->sum(fn (Order $cartOrder) => $cartOrder->baseTotal());
        if ($baseTotal <= 0) {
            return $this->discountError($request, 'لا يمكن تطبيق خصم على طلب بدون إجمالي.');
        }

        $discountAmount = min((int) $discountCode->amount, $baseTotal);
        $this->allocateCartDiscount($cartOrders, $discountCode->code, $discountAmount);
        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);

        if (! $request->expectsJson()) {
            return back()->with('status', 'تم تطبيق كود الخصم على كامل السلة بنجاح.');
        }

        return response()->json([
            'success' => true,
            'discount_code' => $discountCode->code,
            'discount_amount' => $cartSummary['discount_amount'],
            'delivery_fee' => $cartSummary['delivery_fee'],
            'grand_total' => $cartSummary['grand_total'],
        ]);
    }

    private function allocateCartDiscount($orders, string $code, float $amount): void
    {
        $baseTotal = (float) $orders->sum(fn (Order $order) => $order->baseTotal());
        $remaining = round($amount, 2);
        $lastOrderId = $orders->filter(fn (Order $order) => $order->baseTotal() > 0)->last()?->id;

        foreach ($orders as $cartOrder) {
            $share = 0;
            if ($baseTotal > 0 && $cartOrder->baseTotal() > 0) {
                $share = $cartOrder->id === $lastOrderId
                    ? $remaining
                    : round(($amount * $cartOrder->baseTotal()) / $baseTotal, 2);
                $remaining = round($remaining - $share, 2);
            }

            $cartOrder->forceFill([
                'discount_code' => $code,
                'discount_amount' => $share,
                'discount_applied_by' => null,
                'discount_applied_at' => now(),
            ])->save();
        }
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);
    }

    private function discountError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['discount' => $message]);
    }

    private function isMadinahCity(string $city): bool
    {
        $normalized = str_replace([' ', 'ة', 'أ', 'إ', 'آ'], ['', 'ه', 'ا', 'ا', 'ا'], trim($city));

        return in_array($normalized, [
            'المدينهالمنوره',
            'مدينهالمنوره',
            'المدينه',
            'طيبه',
        ], true);
    }

    private function orderPaymentBlockMessage(Order $order, bool $allowZeroTotal = false): ?string
    {
        if ($order->payment_status === 'paid') {
            return 'تم دفع هذا الطلب مسبقًا.';
        }

        if ($order->files->isEmpty() && $order->productItems->isEmpty()) {
            return 'لا يمكن إتمام طلب فارغ.';
        }

        if (in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true) && blank($order->delivery_method)) {
            return 'اختر طريقة الاستلام أو التوصيل قبل الدفع.';
        }

        if (in_array($order->service_type, ['notes', 'books', 'color_printing'], true)) {
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

            if ($pdfFiles->contains(fn ($file) => $file->writing_color === 'black' && ! in_array($file->cover_color, ['beige', 'light_blue', 'light_green', 'white'], true))) {
                return 'الكتابة باللون الأسود متاحة فقط مع البيج أو الأزرق الفاتح أو الأخضر الفاتح أو الأبيض.';
            }
        }

        if ($order->service_type === 'thesis') {
            if ($order->files->where('file_type', 'pdf')->contains(fn ($file) => blank($file->thesis_project_type))) {
                return 'اختر نوع مشروع الرسالة لكل ملف PDF قبل الدفع.';
            }
        }

        if (! $allowZeroTotal && $order->grand_total <= 0) {
            return 'لا يمكن إتمام طلب بدون إجمالي.';
        }

        return null;
    }

    private function refreshOrderTotals(Order $order): void
    {
        $order->load('files');

        $printTotal = 0;
        if (! in_array($order->service_type, ['formatting', 'research'], true)) {
            if (in_array($order->service_type, ['notes', 'books'], true)) {
                $printTotal = $this->printProductPrintTotal($order);
            } elseif ($order->service_type === 'color_printing') {
                $printTotal = (float) $order->files->sum('print_price');
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
        $bindingTotal = (float) $filesForBinding->sum('binding_price');
        $cdTotal = (float) $order->files->sum('cd_price');
        $baseTotal = $printTotal + $bindingTotal + $cdTotal;
        $discountAmount = min((float) $order->discount_amount, $baseTotal);
        $deliveryFee = in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true)
            ? $this->deliveryFee($order->delivery_method, $order, $baseTotal)
            : 0;

        $order->update([
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'grand_total' => max(0, $baseTotal - $discountAmount) + $deliveryFee,
        ]);
    }

    private function printProductPrintTotal(Order $order): int
    {
        $whitePages = (int) $order->files
            ->where('file_type', 'pdf')
            ->filter(fn ($file) => ($file->paper_color ?: 'white') === 'white')
            ->sum(fn ($file) => $file->pages * max(1, (int) $file->copies));
        $yellowPages = (int) $order->files
            ->where('file_type', 'pdf')
            ->filter(fn ($file) => $file->paper_color === 'yellow')
            ->sum(fn ($file) => $file->pages * max(1, (int) $file->copies));

        $whiteDivisor = $order->service_type === 'notes' ? 12 : 15;
        $whiteTotal = (int) ceil($whitePages / $whiteDivisor);
        $yellowDivisor = $order->service_type === 'books' ? 10 : 6;
        $yellowTotal = (int) ceil($yellowPages / $yellowDivisor);

        return $whiteTotal + $yellowTotal;
    }

    private function deliveryFee(?string $method, Order $order, ?float $subtotal = null): int
    {
        $subtotal ??= $order->baseTotal();

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
