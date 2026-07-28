<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Services\CartPricingService;
use App\Services\Payments\MoyasarPaymentService;
use App\Services\ServicePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CartController extends Controller
{
    public function showAll(CartPricingService $cartPricing, ServicePricingService $servicePricingService)
    {
        $cartOrders = $this->cartOrders();
        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);
        $servicePricing = $servicePricingService->all();
        $paymentPage = false;

        return response()
            ->view('cart.show', compact('cartOrders', 'cartSummary', 'servicePricing', 'paymentPage'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function payment(Request $request, CartPricingService $cartPricing, ServicePricingService $servicePricingService)
    {
        $allCartOrders = $this->cartOrders();
        $selectedOrderIds = $this->selectedOrderIds($request);
        $cartOrders = $allCartOrders
            ->whereIn('id', $selectedOrderIds)
            ->values();

        if ($selectedOrderIds->isEmpty() || $cartOrders->count() !== $selectedOrderIds->count()) {
            return redirect()->route('cart.index')->withErrors([
                'order' => 'حدد طلبًا واحدًا على الأقل للانتقال إلى الدفع.',
            ]);
        }

        $selectedDeliveryOrders = $cartOrders->filter(
            fn (Order $order) => in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true)
        );

        if ($selectedDeliveryOrders->contains(fn (Order $order) => blank($order->delivery_method))) {
            return redirect()->route('cart.index')->withErrors([
                'delivery_method' => 'اختر طريقة الاستلام أو التوصيل قبل الانتقال إلى الدفع.',
            ]);
        }

        $cartSummary = $cartPricing->refreshCartTotals($cartOrders);
        $servicePricing = $servicePricingService->all();
        $paymentPage = true;

        return response()
            ->view('cart.show', compact('cartOrders', 'cartSummary', 'servicePricing', 'paymentPage', 'selectedOrderIds'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function show(Order $order, CartPricingService $cartPricing)
    {
        $this->authorizeOrder($order);

        return redirect()->route('cart.index');
    }

    public function prepareMoyasar(Request $request, MoyasarPaymentService $moyasar, CartPricingService $cartPricing)
    {
        $selectedOrderIds = $this->selectedOrderIds($request);
        $allCartOrders = $this->cartOrders();
        $cartOrders = $allCartOrders
            ->whereIn('id', $selectedOrderIds)
            ->values();

        if ($selectedOrderIds->isEmpty() || $cartOrders->count() !== $selectedOrderIds->count()) {
            return response()->json(['message' => 'حدد طلبًا واحدًا على الأقل للدفع.'], 422);
        }

        $cartPricing->refreshCartTotals($cartOrders);
        $cartOrders = $this->cartOrders()
            ->whereIn('id', $selectedOrderIds)
            ->values();

        foreach ($cartOrders as $order) {
            if ($message = $this->orderPaymentBlockMessage($order)) {
                return response()->json(['message' => 'خدمة '.$order->service_type.': '.$message], 422);
            }
        }

        if (! $moyasar->isConfigured()) {
            return response()->json([
                'message' => 'مفاتيح ميسر غير مضافة إلى إعدادات الخادم بعد.',
            ], 503);
        }

        try {
            $attempt = $moyasar->createAttempt($request->user(), $cartOrders);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
        $methods = ['creditcard'];

        if (config('payments.moyasar.apple_pay_enabled')) {
            $methods[] = 'applepay';
        }
        if (config('payments.moyasar.stc_pay_enabled')) {
            $methods[] = 'stcpay';
        }
        if (config('payments.moyasar.google_pay_enabled') && filled(config('payments.moyasar.google_pay_merchant_id'))) {
            $methods[] = 'googlepay';
        }

        return response()->json([
            'attempt_reference' => $attempt->reference,
            'amount' => $attempt->amount_minor,
            'currency' => $attempt->currency,
            'description' => 'Alwrraq orders: '.implode(', ', $attempt->order_ids),
            'publishable_api_key' => config('payments.moyasar.publishable_key'),
            'callback_url' => route('moyasar.callback', $attempt),
            'remember_url' => route('moyasar.remember', $attempt),
            'methods' => $methods,
            'supported_networks' => ['mada', 'visa', 'mastercard', 'amex', 'unionpay'],
            'metadata' => [
                'attempt_reference' => $attempt->reference,
                'customer_id' => (string) $request->user()->id,
            ],
            'apple_pay' => [
                'country' => config('payments.moyasar.apple_pay_country', 'SA'),
                'label' => config('payments.moyasar.merchant_label', 'Alwrraq'),
                'validate_merchant_url' => config('payments.moyasar.apple_pay_validation_url'),
            ],
            'google_pay' => [
                'merchant_id' => config('payments.moyasar.google_pay_merchant_id'),
                'country' => config('payments.moyasar.google_pay_country', 'SA'),
                'label' => config('payments.moyasar.merchant_label', 'Alwrraq'),
                'environment' => config('payments.moyasar.google_pay_environment', 'TEST'),
            ],
        ]);
    }

    public function payAll()
    {
        return redirect()->route('cart.index')->withErrors([
            'payment' => 'استخدم نموذج ميسر الآمن لإتمام الدفع.',
        ]);
    }

    public function pay()
    {
        return redirect()->route('cart.index')->withErrors([
            'payment' => 'استخدم نموذج ميسر الآمن لإتمام الدفع.',
        ]);
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
            ->with(['files', 'productItems.product', 'deliveredFiles', 'serviceDefinition'])
            ->withCount('files')
            ->latest()
            ->get();
    }

    private function selectedOrderIds(Request $request)
    {
        return collect($request->input('order_ids', []))
            ->map(fn ($orderId) => filter_var($orderId, FILTER_VALIDATE_INT))
            ->filter(fn ($orderId) => is_int($orderId) && $orderId > 0)
            ->unique()
            ->values();
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

        if ($order->service_type === 'books' && $order->files->contains(fn ($file) => blank($file->cover_color))) {
            return 'اختر لون الجلد لكل ملف قبل الدفع.';
        }

        if ($order->service_type === 'images' && $order->files->contains(fn ($file) => blank($file->image_print_type))) {
            return 'اختر نوع التصوير لكل صورة قبل الدفع.';
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

}
