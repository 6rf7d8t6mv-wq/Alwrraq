<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Support\Collection;

class CartPricingService
{
    public function __construct(private readonly ServicePricingService $pricing) {}

    public function refreshCartTotals(Collection $orders): array
    {
        $orders->each->load(['files', 'productItems', 'serviceDefinition']);
        $orders
            ->where('service_type', 'images')
            ->each(fn (Order $order) => $this->synchronizeImagePrices($order));

        $printAllocations = $this->cartPrintAllocations($orders);

        $baseTotals = $orders->mapWithKeys(function (Order $order) use ($printAllocations) {
            $filesForBinding = in_array($order->service_type, ['thesis', 'phd'], true)
                ? $order->files->where('file_type', 'pdf')
                : $order->files;

            $imagePrintTotal = $order->service_type === 'images'
                ? (float) $order->files->sum('print_price')
                : 0;

            return [$order->id => (float) ($printAllocations[$order->id] ?? 0)
                + $imagePrintTotal
                + (float) $filesForBinding->sum('binding_price')
                + $this->pricing->customServicePrice($order->serviceDefinition)
                + (float) $order->productItems->sum('total_price')
                + (float) $order->files->sum('cd_price')];
        })->all();

        $cartBaseTotal = round(max(0, array_sum($baseTotals)), 2);
        $cartDiscount = round(max(0, min((float) $orders->sum('discount_amount'), $cartBaseTotal)), 2);
        $discountAllocations = $this->allocateAmount(
            $cartDiscount,
            array_filter($baseTotals, fn (float $baseTotal) => $baseTotal > 0)
        );
        $deliveryOrders = $orders->filter(
            fn (Order $order) => in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true)
        );
        $deliveryAnchor = $deliveryOrders->first(fn (Order $order) => filled($order->delivery_method))
            ?? $deliveryOrders->first();
        $deliveryFee = $deliveryAnchor
            ? $this->deliveryFee($deliveryAnchor->delivery_method, $cartBaseTotal)
            : 0;
        $sharedDelivery = $deliveryAnchor && filled($deliveryAnchor->delivery_method)
            ? $deliveryAnchor->only([
                'delivery_method',
                'delivery_unit',
                'delivery_floor',
                'delivery_room',
                'delivery_city',
                'delivery_district',
                'delivery_street',
                'delivery_map_url',
            ])
            : [];
        $discountSource = $orders->first(fn (Order $order) => filled($order->discount_code));

        $orders->each(function (Order $order) use ($printAllocations, $discountAllocations, $deliveryAnchor, $deliveryFee, $sharedDelivery, $discountSource) {
            $filesForBinding = in_array($order->service_type, ['thesis', 'phd'], true)
                ? $order->files->where('file_type', 'pdf')
                : $order->files;
            $bindingTotal = (float) $filesForBinding->sum('binding_price')
                + $this->pricing->customServicePrice($order->serviceDefinition);
            $bindingTotal += (float) $order->productItems->sum('total_price');
            $cdTotal = (float) $order->files->sum('cd_price');
            $printTotal = (float) ($printAllocations[$order->id] ?? 0)
                + ($order->service_type === 'images' ? (float) $order->files->sum('print_price') : 0);
            $baseTotal = $printTotal + $bindingTotal + $cdTotal;
            $discountAmount = (float) ($discountAllocations[$order->id] ?? 0);
            $orderDeliveryFee = $deliveryAnchor?->id === $order->id ? $deliveryFee : 0;

            $totals = [
                'print_total' => round(max(0, $printTotal), 2),
                'binding_total' => round(max(0, $bindingTotal), 2),
                'discount_amount' => round(max(0, $discountAmount), 2),
                'delivery_fee' => round(max(0, $orderDeliveryFee), 2),
                'grand_total' => round(max(0, $baseTotal - $discountAmount) + max(0, $orderDeliveryFee), 2),
            ];

            if ($sharedDelivery && in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'], true)) {
                $totals = array_merge($totals, $sharedDelivery);
            }

            if ($discountSource) {
                $totals = array_merge($totals, [
                    'discount_code' => $discountSource->discount_code,
                    'discount_applied_by' => $discountSource->discount_applied_by,
                    'discount_applied_at' => $discountSource->discount_applied_at,
                ]);
            }

            $order->forceFill($totals)->save();
        });

        $orders->each->refresh();

        return $this->summary($orders);
    }

    public function refreshOrderTotals(Order $order): void
    {
        $this->refreshCartTotals(collect([$order]));
    }

    public function summary(Collection $orders): array
    {
        return [
            'orders_count' => $orders->count(),
            'files_count' => $orders->sum(fn (Order $order) => $order->files->count()),
            'products_count' => $orders->sum(fn (Order $order) => $order->productItems->sum('quantity')),
            'print_total' => round(max(0, (float) $orders->sum('print_total')), 2),
            'binding_total' => round(max(0, (float) $orders->sum('binding_total')), 2),
            'cd_total' => round(max(0, (float) $orders->sum(fn (Order $order) => $order->files->sum('cd_price'))), 2),
            'discount_amount' => round(max(0, (float) $orders->sum('discount_amount')), 2),
            'delivery_fee' => round(max(0, (float) $orders->sum('delivery_fee')), 2),
            'grand_total' => round(max(0, (float) $orders->sum('grand_total')), 2),
        ];
    }

    private function cartPrintAllocations(Collection $orders): array
    {
        $allocations = $orders->mapWithKeys(fn (Order $order) => [$order->id => 0.0])->all();
        $groups = [];

        foreach ($orders as $order) {
            foreach ($order->files as $file) {
                $units = $this->printUnits($order, $file);
                if ($units <= 0) {
                    continue;
                }

                $key = $this->printGroupKey($order, $file);
                $groups[$key]['service'] = $order->service_type;
                $groups[$key]['page_size'] = $file->page_size ?: 'A4';
                $groups[$key]['paper_color'] = $file->paper_color ?: 'white';
                $groups[$key]['orders'][$order->id] = ($groups[$key]['orders'][$order->id] ?? 0) + $units;
            }
        }

        foreach ($groups as $group) {
            $totalUnits = array_sum($group['orders']);
            $totalPrice = $this->groupPrintPrice($group['service'], $totalUnits, $group['paper_color'], $group['page_size']);
            foreach ($this->allocateAmount($totalPrice, $group['orders']) as $orderId => $amount) {
                $allocations[$orderId] = ($allocations[$orderId] ?? 0) + $amount;
            }
        }

        return $allocations;
    }

    private function printUnits(Order $order, OrderFile $file): int
    {
        if (in_array($order->service_type, ['formatting', 'research', 'images'], true)) {
            return 0;
        }

        if (in_array($order->service_type, ['thesis', 'phd'], true) && $file->file_type !== 'pdf') {
            return 0;
        }

        return max(1, (int) $file->pages) * max(1, (int) $file->copies);
    }

    private function printGroupKey(Order $order, OrderFile $file): string
    {
        return match ($order->service_type) {
            'notes', 'books' => implode('|', [$order->service_type, $file->paper_color ?: 'white']),
            'color_printing' => implode('|', [$order->service_type, $file->page_size ?: 'A4']),
            default => $order->service_type,
        };
    }

    private function groupPrintPrice(string $service, int $units, string $paperColor, string $pageSize): float
    {
        return match ($service) {
            'notes' => ceil($units / $this->pricing->value($paperColor === 'yellow' ? 'notes_yellow_pages' : 'notes_white_pages'))
                * $this->pricing->value($paperColor === 'yellow' ? 'notes_yellow_group_price' : 'notes_white_group_price'),
            'books' => ceil($units / $this->pricing->value($paperColor === 'yellow' ? 'books_yellow_pages' : 'books_white_pages'))
                * $this->pricing->value($paperColor === 'yellow' ? 'books_yellow_group_price' : 'books_white_group_price'),
            'color_printing' => $this->colorPrintingPrice($units, $pageSize),
            default => (float) $this->printPrice($units, 1),
        };
    }

    private function allocateAmount(float $amount, array $unitsByOrder): array
    {
        $totalUnits = array_sum($unitsByOrder);
        if ($totalUnits <= 0 || $amount <= 0) {
            return array_fill_keys(array_keys($unitsByOrder), 0.0);
        }

        $remaining = round($amount, 2);
        $lastOrderId = array_key_last($unitsByOrder);
        $allocations = [];

        foreach ($unitsByOrder as $orderId => $units) {
            if ($orderId === $lastOrderId) {
                $allocations[$orderId] = $remaining;
                break;
            }

            $share = round(($amount * $units) / $totalUnits, 2);
            $allocations[$orderId] = $share;
            $remaining = round($remaining - $share, 2);
        }

        return $allocations;
    }

    private function printPrice(int $pages, int $copies): float
    {
        return ceil($pages / $this->pricing->value('academic_print_pages'))
            * $this->pricing->value('academic_print_group_price')
            * max(1, $copies);
    }

    private function colorPrintingPrice(int $sheetCount, string $pageSize): float
    {
        if ($pageSize === 'A3') {
            $unitPrice = match (true) {
                $sheetCount <= 5 => $this->pricing->value('color_a3_first_5'),
                $sheetCount <= 10 => $this->pricing->value('color_a3_to_10'),
                default => $this->pricing->value('color_a3_over_10'),
            };

            return $sheetCount * $unitPrice;
        }

        $unitPrice = match (true) {
            $sheetCount <= 5 => $this->pricing->value('color_a4_first_5'),
            $sheetCount <= 10 => $this->pricing->value('color_a4_to_10'),
            default => $this->pricing->value('color_a4_over_10'),
        };

        return $sheetCount * $unitPrice;
    }

    private function deliveryFee(?string $method, float $subtotal): float
    {
        return match ($method) {
            'islamic_university_delivery' => $subtotal >= $this->pricing->value('delivery_university_free_from') ? 0 : $this->pricing->value('delivery_university_fee'),
            'madinah_delivery' => $this->pricing->value('delivery_madinah_fee'),
            'redbox_delivery' => $this->pricing->value('delivery_redbox_fee'),
            default => 0,
        };
    }

    private function synchronizeImagePrices(Order $order): void
    {
        $pricing = $this->pricing->imageOrderPricing($order->files);

        foreach ($order->files->values() as $index => $file) {
            $price = (float) ($pricing['allocations'][$index] ?? 0);
            if (abs((float) $file->total_price - $price) < 0.001
                && abs((float) $file->print_price - $price) < 0.001) {
                continue;
            }

            $file->forceFill([
                'print_price' => $price,
                'binding_price' => 0,
                'cd_price' => 0,
                'total_price' => $price,
            ])->save();
        }
    }
}
