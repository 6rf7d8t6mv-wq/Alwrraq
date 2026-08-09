<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProductItem;
use App\Models\ServiceDefinition;
use App\Models\StationeryProduct;
use App\Services\GuestCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StationeryController extends Controller
{
    public function __construct(private readonly GuestCartService $guestCart) {}

    public function image(string $filename)
    {
        abort_unless($filename === basename($filename), 404);

        $path = 'stationery-products/'.$filename;
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function index(Request $request)
    {
        if ($request->filled('service_definition_id')) {
            $definition = ServiceDefinition::query()
                ->whereKey($request->integer('service_definition_id'))
                ->where('workflow_type', 'stationery')
                ->where('is_active', true)
                ->firstOrFail();
            $request->session()->put('stationery_service_definition_id', $definition->id);
        }

        $search = trim((string) $request->query('q', ''));
        $products = StationeryProduct::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('product_type', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->get();

        $cartOrder = $this->guestCart->scopeOwned(Order::query(), $request)
            ->where('service_type', 'stationery')
            ->where('payment_status', 'unpaid')
            ->with('productItems')
            ->first();
        $cartQuantities = $cartOrder?->productItems
            ->whereNotNull('stationery_product_id')
            ->pluck('quantity', 'stationery_product_id') ?? collect();

        return view('stationery.index', compact('products', 'search', 'cartOrder', 'cartQuantities'));
    }

    public function add(Request $request, StationeryProduct $product)
    {
        abort_unless($product->is_active, 404);

        [$item, $order] = DB::transaction(function () use ($request, $product) {
            $order = Order::query()->firstOrCreate([
                ...$this->guestCart->orderIdentity($request),
                'service_type' => 'stationery',
                'service_definition_id' => $request->session()->get('stationery_service_definition_id'),
                'status' => 'new',
                'payment_status' => 'unpaid',
            ], [
                'print_total' => 0,
                'binding_total' => 0,
                'grand_total' => 0,
            ]);

            $item = $order->productItems()->where('stationery_product_id', $product->id)->first();
            $quantity = min(99, ($item?->quantity ?? 0) + 1);
            $payload = [
                'product_name' => $product->name,
                'company_name' => $product->company_name,
                'product_type' => $product->product_type,
                'image_path' => $product->image_path,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'total_price' => round((float) $product->price * $quantity, 2),
            ];

            if ($item) {
                $item->update($payload);
            } else {
                $item = $order->productItems()->create($payload + ['stationery_product_id' => $product->id]);
            }

            $this->refreshOrder($order);

            return [$item->fresh(), $order->fresh()];
        });

        if (! $request->expectsJson()) {
            return back()->with('status', 'تمت إضافة المنتج إلى السلة.');
        }

        return response()->json($this->cartResponse($order, $item));
    }

    public function remove(Request $request, StationeryProduct $product)
    {
        $order = $this->guestCart->scopeOwned(Order::query(), $request)
            ->where('service_type', 'stationery')
            ->where('payment_status', 'unpaid')
            ->with('productItems')
            ->first();

        if ($order) {
            $order->productItems()->where('stationery_product_id', $product->id)->delete();
            if ($order->productItems()->doesntExist()) {
                $order->delete();
                $order = null;
            } else {
                $this->refreshOrder($order);
                $order->refresh();
            }
        }

        if (! $request->expectsJson()) {
            return back()->with('status', 'تمت إزالة المنتج من السلة.');
        }

        return response()->json($this->cartResponse($order));
    }

    public function removeItem(Request $request, OrderProductItem $item)
    {
        $order = $item->order;
        abort_unless($this->guestCart->owns($request, $order) && $order->service_type === 'stationery' && $order->payment_status === 'unpaid', 403);

        $item->delete();
        if ($order->productItems()->doesntExist()) {
            $order->delete();
        } else {
            $this->refreshOrder($order);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('status', 'تمت إزالة المنتج من السلة.');
    }

    private function refreshOrder(Order $order): void
    {
        $productTotal = (float) $order->productItems()->sum('total_price');
        $discount = min((float) $order->discount_amount, $productTotal);
        $order->forceFill([
            'print_total' => 0,
            'binding_total' => $productTotal,
            'discount_amount' => $discount,
            'grand_total' => max(0, $productTotal - $discount) + (float) $order->delivery_fee,
            'admin_opened_at' => null,
            'admin_notification_seen_at' => null,
        ])->save();
    }

    private function cartResponse(?Order $order, ?OrderProductItem $item = null): array
    {
        return [
            'success' => true,
            'quantity' => $item?->quantity ?? 0,
            'cart_count' => $order ? (int) $order->productItems()->sum('quantity') : 0,
            'cart_total' => $order ? (float) $order->productItems()->sum('total_price') : 0,
        ];
    }
}
