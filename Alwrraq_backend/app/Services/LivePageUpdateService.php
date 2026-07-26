<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\EducationalInstitution;
use App\Models\Order;
use App\Models\ServiceDefinition;
use App\Models\ServicePriceSetting;
use App\Models\StationeryProduct;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class LivePageUpdateService
{
    public function snapshot(User $user): array
    {
        $pricingUpdatedAt = Schema::hasTable('service_price_settings')
            ? (string) (ServicePriceSetting::query()->max('updated_at') ?? 'defaults')
            : 'defaults';
        $catalogUpdatedAt = Schema::hasTable('service_definitions')
            ? (string) (ServiceDefinition::query()->max('updated_at') ?? 'defaults')
            : 'defaults';
        $applicationRevision = $this->applicationRevision();

        if ($user->role === 'admin') {
            $orders = Order::query();
            $ordersCount = (int) (clone $orders)->count();
            $unseenCount = (int) (clone $orders)
                ->whereNull('admin_notification_seen_at')
                ->whereNotIn('status', ['completed', 'finished'])
                ->count();

            $parts = [
                'admin',
                $ordersCount,
                (string) ((clone $orders)->max('updated_at') ?? ''),
                (string) (User::query()->max('updated_at') ?? ''),
                (string) (StationeryProduct::query()->max('updated_at') ?? ''),
                (string) (DiscountCode::query()->max('updated_at') ?? ''),
                (string) (EducationalInstitution::query()->max('updated_at') ?? ''),
                $pricingUpdatedAt,
                $catalogUpdatedAt,
                $applicationRevision,
            ];
        } else {
            $orders = Order::query()->where('user_id', $user->id);
            $ordersCount = (int) (clone $orders)->count();
            $unseenCount = (int) (clone $orders)
                ->whereNull('customer_notification_seen_at')
                ->whereHas('deliveredFiles', fn ($query) => $query->whereNull('customer_downloaded_at'))
                ->count();

            $parts = [
                'customer',
                $user->id,
                $ordersCount,
                (string) ((clone $orders)->max('updated_at') ?? ''),
                (string) ($user->fresh()?->updated_at ?? ''),
                (string) (StationeryProduct::query()->max('updated_at') ?? ''),
                $pricingUpdatedAt,
                $catalogUpdatedAt,
                $applicationRevision,
            ];
        }

        return [
            'revision' => hash('sha256', implode('|', $parts)),
            'orders_count' => $ordersCount,
            'unseen_count' => $unseenCount,
            'role' => $user->role,
            'pricing_revision' => hash('sha256', $pricingUpdatedAt),
            'catalog_revision' => hash('sha256', $catalogUpdatedAt),
            'app_revision' => $applicationRevision,
        ];
    }

    private function applicationRevision(): string
    {
        foreach (['APP_REVISION', 'SOURCE_VERSION', 'RENDER_GIT_COMMIT', 'VERCEL_GIT_COMMIT_SHA', 'HEROKU_SLUG_COMMIT'] as $key) {
            $value = $_SERVER[$key] ?? $_ENV[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        foreach ([base_path('.git'), dirname(base_path()).'/.git'] as $gitDirectory) {
            $headPath = $gitDirectory.'/HEAD';
            if (! is_file($headPath)) {
                continue;
            }

            $head = trim((string) file_get_contents($headPath));
            if (! str_starts_with($head, 'ref: ')) {
                return $head;
            }

            $reference = trim(substr($head, 5));
            $referencePath = $gitDirectory.'/'.$reference;
            if (is_file($referencePath)) {
                return trim((string) file_get_contents($referencePath));
            }

            $packedRefsPath = $gitDirectory.'/packed-refs';
            if (is_file($packedRefsPath)) {
                foreach (file($packedRefsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                    if ($line[0] === '#' || $line[0] === '^') {
                        continue;
                    }

                    [$revision, $packedReference] = array_pad(preg_split('/\s+/', trim($line), 2), 2, '');
                    if ($packedReference === $reference) {
                        return $revision;
                    }
                }
            }
        }

        return (string) config('app.version', 'unknown');
    }
}
