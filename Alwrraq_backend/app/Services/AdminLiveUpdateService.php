<?php

namespace App\Services;

use App\Models\Order;

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

        return [
            'revision' => hash('sha256', implode('|', [$ordersCount, $latestOrderId, $latestUpdatedAt])),
            'orders_count' => $ordersCount,
            'latest_order_id' => $latestOrderId,
            'unseen_count' => $unseenCount,
        ];
    }
}
