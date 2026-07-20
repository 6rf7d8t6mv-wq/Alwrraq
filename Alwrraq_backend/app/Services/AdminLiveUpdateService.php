<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ServicePriceSetting;
use Illuminate\Support\Facades\Schema;

class AdminLiveUpdateService
{
    public function snapshot(): array
    {
        $ordersCount = (int) Order::query()->count();
        $latestOrderId = (int) (Order::query()->max('id') ?? 0);
        $latestUpdatedAt = (string) (Order::query()->max('updated_at') ?? '');
        $unseenCount = (int) Order::query()
            ->whereNull('admin_notification_seen_at')
            ->whereNotIn('status', ['completed', 'finished'])
            ->count();

        $pricingUpdatedAt = Schema::hasTable('service_price_settings')
            ? (string) (ServicePriceSetting::query()->max('updated_at') ?? 'defaults')
            : 'defaults';

        return [
            'revision' => hash('sha256', implode('|', [$ordersCount, $latestOrderId, $latestUpdatedAt, $pricingUpdatedAt])),
            'orders_count' => $ordersCount,
            'latest_order_id' => $latestOrderId,
            'unseen_count' => $unseenCount,
            'pricing_revision' => hash('sha256', $pricingUpdatedAt),
        ];
    }
}
