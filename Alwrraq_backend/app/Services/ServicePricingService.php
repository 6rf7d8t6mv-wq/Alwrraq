<?php

namespace App\Services;

use App\Models\ServiceDefinition;
use App\Models\ServicePriceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;

class ServicePricingService
{
    public const CUSTOM_SERVICE_DEFAULT_PRICE = 1;

    public const DEFINITIONS = [
        'notes_white_pages' => ['group' => 'طباعة المذكرات والملفات', 'label' => 'عدد صفحات الورق الأبيض للمجموعة', 'default' => 12, 'integer' => true, 'suffix' => 'صفحة'],
        'notes_white_group_price' => ['group' => 'طباعة المذكرات والملفات', 'label' => 'سعر مجموعة الورق الأبيض', 'default' => 1, 'suffix' => 'ريال'],
        'notes_yellow_pages' => ['group' => 'طباعة المذكرات والملفات', 'label' => 'عدد صفحات الورق الأصفر للمجموعة', 'default' => 6, 'integer' => true, 'suffix' => 'صفحة'],
        'notes_yellow_group_price' => ['group' => 'طباعة المذكرات والملفات', 'label' => 'سعر مجموعة الورق الأصفر', 'default' => 1, 'suffix' => 'ريال'],
        'notes_binding_normal' => ['group' => 'تغليف المذكرات والملفات', 'label' => 'التغليف العادي', 'default' => 3, 'suffix' => 'ريال'],
        'notes_binding_wire_under_100' => ['group' => 'تغليف المذكرات والملفات', 'label' => 'السلك لأقل من 100 صفحة', 'default' => 5, 'suffix' => 'ريال'],
        'notes_binding_wire_under_300' => ['group' => 'تغليف المذكرات والملفات', 'label' => 'السلك من 100 إلى 299 صفحة', 'default' => 7, 'suffix' => 'ريال'],
        'notes_binding_wire_up_to_600' => ['group' => 'تغليف المذكرات والملفات', 'label' => 'السلك من 300 إلى 600 صفحة', 'default' => 9, 'suffix' => 'ريال'],
        'notes_binding_wire_over_600' => ['group' => 'تغليف المذكرات والملفات', 'label' => 'السلك لأكثر من 600 صفحة', 'default' => 14, 'suffix' => 'ريال'],

        'books_white_pages' => ['group' => 'طباعة وتجليد الكتب', 'label' => 'عدد صفحات الورق الأبيض للمجموعة', 'default' => 15, 'integer' => true, 'suffix' => 'صفحة'],
        'books_white_group_price' => ['group' => 'طباعة وتجليد الكتب', 'label' => 'سعر مجموعة الورق الأبيض', 'default' => 1, 'suffix' => 'ريال'],
        'books_yellow_pages' => ['group' => 'طباعة وتجليد الكتب', 'label' => 'عدد صفحات الورق الأصفر للمجموعة', 'default' => 10, 'integer' => true, 'suffix' => 'صفحة'],
        'books_yellow_group_price' => ['group' => 'طباعة وتجليد الكتب', 'label' => 'سعر مجموعة الورق الأصفر', 'default' => 1, 'suffix' => 'ريال'],
        'books_binding_a4' => ['group' => 'طباعة وتجليد الكتب', 'label' => 'تجليد الكعب الجلد الطبيعي A4 للنسخة', 'default' => 55, 'suffix' => 'ريال'],
        'books_binding_small' => ['group' => 'طباعة وتجليد الكتب', 'label' => 'تجليد الكعب الجلد الطبيعي للمقاس الصغير للنسخة', 'default' => 45, 'suffix' => 'ريال'],

        'academic_print_pages' => ['group' => 'الماجستير والدكتوراه', 'label' => 'عدد صفحات الطباعة للمجموعة', 'default' => 15, 'integer' => true, 'suffix' => 'صفحة'],
        'academic_print_group_price' => ['group' => 'الماجستير والدكتوراه', 'label' => 'سعر مجموعة الطباعة', 'default' => 1, 'suffix' => 'ريال'],
        'academic_gold_single' => ['group' => 'الماجستير والدكتوراه', 'label' => 'التجليد والكتابة الذهبية لنسخة واحدة', 'default' => 90, 'suffix' => 'ريال'],
        'academic_gold_multiple' => ['group' => 'الماجستير والدكتوراه', 'label' => 'التجليد والكتابة الذهبية لكل نسخة عند التعدد', 'default' => 75, 'suffix' => 'ريال'],
        'academic_black_single' => ['group' => 'الماجستير والدكتوراه', 'label' => 'التجليد والكتابة السوداء لنسخة واحدة', 'default' => 60, 'suffix' => 'ريال'],
        'academic_black_multiple' => ['group' => 'الماجستير والدكتوراه', 'label' => 'التجليد والكتابة السوداء لكل نسخة عند التعدد', 'default' => 45, 'suffix' => 'ريال'],
        'academic_cd_plain' => ['group' => 'الماجستير والدكتوراه', 'label' => 'CD بدون طباعة', 'default' => 5, 'suffix' => 'ريال'],
        'academic_cd_printed' => ['group' => 'الماجستير والدكتوراه', 'label' => 'سي دي بغلاف مطبوع', 'default' => 10, 'suffix' => 'ريال'],

        'formatting_page_price' => ['group' => 'الخدمات الأكاديمية', 'label' => 'تنسيق وتدقيق الرسائل لكل صفحة', 'default' => 10, 'suffix' => 'ريال'],
        'research_page_price' => ['group' => 'الخدمات الأكاديمية', 'label' => 'إنشاء البحوث لكل صفحة', 'default' => 10, 'suffix' => 'ريال'],

        'color_a4_first_5' => ['group' => 'الطباعة بالألوان', 'label' => 'A4 لكل ورقة حتى 5 أوراق', 'default' => 2, 'suffix' => 'ريال'],
        'color_a4_to_10' => ['group' => 'الطباعة بالألوان', 'label' => 'A4 لكل ورقة من 6 إلى 10', 'default' => 1.5, 'suffix' => 'ريال'],
        'color_a4_over_10' => ['group' => 'الطباعة بالألوان', 'label' => 'A4 لكل ورقة فوق 10', 'default' => 0.8, 'suffix' => 'ريال'],
        'color_a3_first_5' => ['group' => 'الطباعة بالألوان', 'label' => 'A3 لكل ورقة حتى 5 أوراق', 'default' => 5, 'suffix' => 'ريال'],
        'color_a3_to_10' => ['group' => 'الطباعة بالألوان', 'label' => 'A3 لكل ورقة من 6 إلى 10', 'default' => 3.5, 'suffix' => 'ريال'],
        'color_a3_over_10' => ['group' => 'الطباعة بالألوان', 'label' => 'A3 لكل ورقة فوق 10', 'default' => 2.5, 'suffix' => 'ريال'],
        'thermal_a4_sheet' => ['group' => 'الطباعة بالألوان', 'label' => 'التغليف الحراري A4 لكل ورقة', 'default' => 5, 'suffix' => 'ريال'],
        'thermal_a3_sheet' => ['group' => 'الطباعة بالألوان', 'label' => 'التغليف الحراري A3 لكل ورقة', 'default' => 10, 'suffix' => 'ريال'],

        'images_color_group_size' => ['group' => 'تصوير الصور', 'label' => 'عدد نسخ الصور الملونة في المجموعة', 'default' => 1, 'integer' => true, 'suffix' => 'نسخة'],
        'images_color_group_price' => ['group' => 'تصوير الصور', 'label' => 'سعر مجموعة الصور الملونة', 'default' => 1, 'suffix' => 'ريال'],
        'images_bw_group_size' => ['group' => 'تصوير الصور', 'label' => 'عدد نسخ الصور بالأبيض والأسود في المجموعة', 'default' => 1, 'integer' => true, 'suffix' => 'نسخة'],
        'images_bw_group_price' => ['group' => 'تصوير الصور', 'label' => 'سعر مجموعة الصور بالأبيض والأسود', 'default' => 1, 'suffix' => 'ريال'],
        'images_personal_5_price' => ['group' => 'تصوير الصور', 'label' => 'سعر الصورة الشخصية — 5 نسخ', 'default' => 1, 'suffix' => 'ريال'],
        'images_personal_8_price' => ['group' => 'تصوير الصور', 'label' => 'سعر الصورة الشخصية — 8 نسخ', 'default' => 1, 'suffix' => 'ريال'],
        'images_personal_16_price' => ['group' => 'تصوير الصور', 'label' => 'سعر الصورة الشخصية — 16 نسخة', 'default' => 1, 'suffix' => 'ريال'],

        'delivery_university_fee' => ['group' => 'الاستلام والتوصيل', 'label' => 'التوصيل داخل الجامعة الإسلامية', 'default' => 5, 'suffix' => 'ريال'],
        'delivery_university_free_from' => ['group' => 'الاستلام والتوصيل', 'label' => 'مجاني داخل الجامعة ابتداءً من', 'default' => 35, 'suffix' => 'ريال'],
        'delivery_madinah_fee' => ['group' => 'الاستلام والتوصيل', 'label' => 'التوصيل داخل المدينة المنورة', 'default' => 20, 'suffix' => 'ريال'],
        'delivery_redbox_fee' => ['group' => 'الاستلام والتوصيل', 'label' => 'الشحن خارج المدينة عبر RedBox', 'default' => 30, 'suffix' => 'ريال'],
    ];

    private ?array $loaded = null;

    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        $values = collect(self::DEFINITIONS)->mapWithKeys(
            fn (array $definition, string $key) => [$key => (float) $definition['default']]
        )->all();

        if (Facade::getFacadeApplication() && Schema::hasTable('service_price_settings')) {
            ServicePriceSetting::query()->pluck('value', 'key')->each(function ($value, string $key) use (&$values) {
                if (array_key_exists($key, self::DEFINITIONS)) {
                    $values[$key] = (float) $value;
                }
            });
        }

        return $this->loaded = $values;
    }

    public function value(string $key): float
    {
        return $this->all()[$key] ?? throw new \InvalidArgumentException("Unknown service price: {$key}");
    }

    public function customServicePrice(?ServiceDefinition $service): float
    {
        if (! $service || $service->is_system || $service->workflow_type === 'images') {
            return 0;
        }

        return $this->customServicePrices([$service])[$service->id] ?? self::CUSTOM_SERVICE_DEFAULT_PRICE;
    }

    public function customServicePrices(iterable $services): array
    {
        $services = collect($services)
            ->filter(fn (ServiceDefinition $service) => ! $service->is_system)
            ->values();

        if ($services->isEmpty()) {
            return [];
        }

        $keysById = $services->mapWithKeys(
            fn (ServiceDefinition $service) => [$service->id => $this->customServicePriceKey($service->id)]
        );
        $storedPrices = Facade::getFacadeApplication() && Schema::hasTable('service_price_settings')
            ? ServicePriceSetting::query()->whereIn('key', $keysById->values())->pluck('value', 'key')
            : collect();

        return $keysById->mapWithKeys(fn (string $key, int $serviceId) => [
            $serviceId => (float) ($storedPrices[$key] ?? self::CUSTOM_SERVICE_DEFAULT_PRICE),
        ])->all();
    }

    public function groupedDefinitions(): array
    {
        $values = $this->all();
        $groups = [];

        foreach (self::DEFINITIONS as $key => $definition) {
            $groups[$definition['group']][$key] = [...$definition, 'value' => $values[$key]];
        }

        return $groups;
    }

    public function imagePrintPrice(string $type, int $copies): float
    {
        $copies = max(1, $copies);

        if ($type === 'personal') {
            return $this->value('images_personal_'.$copies.'_price');
        }

        $prefix = $type === 'black_white' ? 'images_bw' : 'images_color';

        return ceil($copies / $this->value($prefix.'_group_size'))
            * $this->value($prefix.'_group_price');
    }

    public function imageOrderPricing(iterable $files): array
    {
        $files = collect($files)->values();
        $allocations = array_fill(0, $files->count(), 0.0);
        $typeTotals = [
            'color' => ['copies' => 0, 'price' => 0.0],
            'black_white' => ['copies' => 0, 'price' => 0.0],
            'personal' => ['copies' => 0, 'price' => 0.0],
        ];

        foreach (['color', 'black_white'] as $type) {
            $unitsByIndex = $files
                ->mapWithKeys(fn ($file, int $index) => [
                    $index => ($file->image_print_type === $type ? max(1, (int) $file->copies) : 0),
                ])
                ->filter(fn (int $copies) => $copies > 0)
                ->all();
            $copies = array_sum($unitsByIndex);
            $price = $copies > 0 ? $this->imagePrintPrice($type, $copies) : 0.0;
            $typeTotals[$type] = ['copies' => $copies, 'price' => $price];

            foreach ($this->allocatePrice($price, $unitsByIndex) as $index => $amount) {
                $allocations[$index] = $amount;
            }
        }

        foreach ($files as $index => $file) {
            if ($file->image_print_type !== 'personal') {
                continue;
            }

            $copies = max(1, (int) $file->copies);
            $price = $this->imagePrintPrice('personal', $copies);
            $allocations[$index] = $price;
            $typeTotals['personal']['copies'] += $copies;
            $typeTotals['personal']['price'] += $price;
        }

        return [
            'total' => array_sum($allocations),
            'allocations' => $allocations,
            'types' => $typeTotals,
        ];
    }

    public function validationRules(): array
    {
        $rules = [];
        foreach (self::DEFINITIONS as $key => $definition) {
            $rules["prices.{$key}"] = $definition['integer'] ?? false
                ? ['required', 'integer', 'min:1', 'max:100000']
                : ['required', 'numeric', 'min:0', 'max:1000000'];
        }

        return $rules;
    }

    public function update(array $prices, int $userId): void
    {
        DB::transaction(function () use ($prices, $userId) {
            foreach (self::DEFINITIONS as $key => $definition) {
                ServicePriceSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $prices[$key], 'updated_by' => $userId]
                );
            }
        });

        $this->loaded = null;
    }

    public function ensureCustomServicePrice(ServiceDefinition $service, int $userId): void
    {
        if ($service->is_system) {
            return;
        }

        ServicePriceSetting::query()->firstOrCreate(
            ['key' => $this->customServicePriceKey($service->id)],
            ['value' => self::CUSTOM_SERVICE_DEFAULT_PRICE, 'updated_by' => $userId]
        );
    }

    public function updateCustomServicePrices(array $prices, int $userId): void
    {
        $services = ServiceDefinition::query()
            ->where('is_system', false)
            ->whereIn('id', array_keys($prices))
            ->get();

        DB::transaction(function () use ($services, $prices, $userId) {
            foreach ($services as $service) {
                ServicePriceSetting::query()->updateOrCreate(
                    ['key' => $this->customServicePriceKey($service->id)],
                    ['value' => $prices[$service->id], 'updated_by' => $userId]
                );
            }
        });
    }

    private function customServicePriceKey(int $serviceId): string
    {
        return 'service_definition_'.$serviceId.'_price';
    }

    private function allocatePrice(float $amount, array $unitsByIndex): array
    {
        $totalUnits = array_sum($unitsByIndex);
        if ($totalUnits <= 0 || $amount <= 0) {
            return array_fill_keys(array_keys($unitsByIndex), 0.0);
        }

        $remaining = round($amount, 2);
        $lastIndex = array_key_last($unitsByIndex);
        $allocations = [];

        foreach ($unitsByIndex as $index => $units) {
            if ($index === $lastIndex) {
                $allocations[$index] = $remaining;
                break;
            }

            $share = round(($amount * $units) / $totalUnits, 2);
            $allocations[$index] = $share;
            $remaining = round($remaining - $share, 2);
        }

        return $allocations;
    }
}
