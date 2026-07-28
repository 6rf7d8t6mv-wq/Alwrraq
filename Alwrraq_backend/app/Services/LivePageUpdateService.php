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
        $pricingRevision = Schema::hasTable('service_price_settings')
            ? hash('sha256', ServicePriceSetting::query()
                ->orderBy('key')
                ->get(['key', 'value', 'updated_at'])
                ->toJson())
            : hash('sha256', 'defaults');
        $catalogRevision = Schema::hasTable('service_definitions')
            ? hash('sha256', ServiceDefinition::query()
                ->orderBy('id')
                ->get(['id', 'title', 'description', 'icon', 'image_path', 'workflow_type', 'requires_file', 'is_active', 'sort_order', 'updated_at'])
                ->toJson())
            : hash('sha256', 'defaults');
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
                $pricingRevision,
                $catalogRevision,
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
                $pricingRevision,
                $catalogRevision,
                $applicationRevision,
            ];
        }

        return [
            'revision' => hash('sha256', implode('|', $parts)),
            'orders_count' => $ordersCount,
            'unseen_count' => $unseenCount,
            'role' => $user->role,
            'pricing_revision' => $pricingRevision,
            'catalog_revision' => $catalogRevision,
            'app_revision' => $applicationRevision,
        ];
    }

    public function applicationRevision(): string
    {
        foreach (['APP_REVISION', 'SOURCE_VERSION', 'RENDER_GIT_COMMIT', 'VERCEL_GIT_COMMIT_SHA', 'HEROKU_SLUG_COMMIT'] as $key) {
            $value = $_SERVER[$key] ?? $_ENV[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        $gitRevision = '';
        foreach ([base_path('.git'), dirname(base_path()).'/.git'] as $gitDirectory) {
            $headPath = $gitDirectory.'/HEAD';
            if (! is_file($headPath)) {
                continue;
            }

            $head = trim((string) file_get_contents($headPath));
            if (! str_starts_with($head, 'ref: ')) {
                $gitRevision = $head;
                break;
            }

            $reference = trim(substr($head, 5));
            $referencePath = $gitDirectory.'/'.$reference;
            if (is_file($referencePath)) {
                $gitRevision = trim((string) file_get_contents($referencePath));
                break;
            }

            $packedRefsPath = $gitDirectory.'/packed-refs';
            if (is_file($packedRefsPath)) {
                foreach (file($packedRefsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                    if ($line[0] === '#' || $line[0] === '^') {
                        continue;
                    }

                    [$revision, $packedReference] = array_pad(preg_split('/\s+/', trim($line), 2), 2, '');
                    if ($packedReference === $reference) {
                        $gitRevision = $revision;
                        break 2;
                    }
                }
            }
        }

        $revision = $gitRevision !== '' ? $gitRevision : (string) config('app.version', 'unknown');

        return app()->environment('local')
            ? hash('sha256', $revision.'|'.$this->localSourceRevision())
            : $revision;
    }

    private function localSourceRevision(): string
    {
        $files = [];

        foreach ([app_path(), base_path('routes'), resource_path('views')] as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $files[] = implode('|', [$file->getPathname(), $file->getMTime(), $file->getSize()]);
            }
        }

        sort($files);

        return hash('sha256', implode("\n", $files));
    }
}
