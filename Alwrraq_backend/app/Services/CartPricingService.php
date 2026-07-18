<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Support\Collection;

class CartPricingService
{
    public function refreshCartTotals(Collection $orders): array
    {
        $orders->each->load(['files', 'productItems']);

        $printAllocations = $this->cartPrintAllocations($orders);

        $baseTotals = $orders->mapWithKeys(function (Order $order) use ($printAllocations) {
            $filesForBinding = in_array($order->service_type, ['thesis', 'phd'], true)
                ? $order->files->where('file_type', 'pdf')
                : $order->files;

            return [$order->id => (float) ($printAllocations[$order->id] ?? 0)
                + (float) $filesForBinding->sum('binding_price')
                + (float) $order->productItems->sum('total_price')
                + (float) $order->files->sum('cd_price')];
        })->all();

        $cartBaseTotal = array_sum($baseTotals);
        $cartDiscount = min((float) $orders->sum('discount_amount'), $cartBaseTotal);
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
            $bindingTotal = (float) $filesForBinding->sum('binding_price');
            $bindingTotal += (float) $order->productItems->sum('total_price');
            $cdTotal = (float) $order->files->sum('cd_price');
            $printTotal = (float) ($printAllocations[$order->id] ?? 0);
            $baseTotal = $printTotal + $bindingTotal + $cdTotal;
            $discountAmount = (float) ($discountAllocations[$order->id] ?? 0);
            $orderDeliveryFee = $deliveryAnchor?->id === $order->id ? $deliveryFee : 0;

            $totals = [
                'print_total' => $printTotal,
                'binding_total' => $bindingTotal,
                'discount_amount' => $discountAmount,
                'delivery_fee' => $orderDeliveryFee,
                'grand_total' => max(0, $baseTotal - $discountAmount) + $orderDeliveryFee,
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
            'print_total' => (float) $orders->sum('print_total'),
            'binding_total' => (float) $orders->sum('binding_total'),
            'cd_total' => (float) $orders->sum(fn (Order $order) => $order->files->sum('cd_price')),
            'discount_amount' => (float) $orders->sum('discount_amount'),
            'delivery_fee' => (float) $orders->sum('delivery_fee'),
            'grand_total' => (float) $orders->sum('grand_total'),
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
        if (in_array($order->service_type, ['formatting', 'research'], true)) {
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
            'notes' => (float) ceil($units / ($paperColor === 'yellow' ? 6 : 12)),
            'books' => (float) ceil($units / ($paperColor === 'yellow' ? 10 : 15)),
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

    private function printPrice(int $pages, int $copies): int
    {
        return (int) ceil($pages / 15) * max(1, $copies);
    }

    private function colorPrintingPrice(int $sheetCount, string $pageSize): float
    {
        if ($pageSize === 'A3') {
            $unitPrice = match (true) {
                $sheetCount <= 5 => 5,
                $sheetCount <= 10 => 3.5,
                default => 2.5,
            };

            return $sheetCount * $unitPrice;
        }

        $unitPrice = match (true) {
            $sheetCount <= 5 => 2,
            $sheetCount <= 10 => 1.5,
            default => 0.80,
        };

        return $sheetCount * $unitPrice;
    }

    private function deliveryFee(?string $method, float $subtotal): int
    {
        return match ($method) {
            'islamic_university_delivery' => $subtotal >= 35 ? 0 : 5,
            'madinah_delivery' => 20,
            'redbox_delivery' => 30,
            default => 0,
        };
    }
}
