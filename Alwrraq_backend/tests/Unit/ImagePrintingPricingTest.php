<?php

namespace Tests\Unit;

use App\Models\ServiceDefinition;
use App\Services\ServicePricingService;
use PHPUnit\Framework\TestCase;

class ImagePrintingPricingTest extends TestCase
{
    public function test_color_and_black_white_images_are_priced_by_configurable_groups(): void
    {
        $pricing = $this->pricing([
            'images_color_group_size' => 3,
            'images_color_group_price' => 5,
            'images_bw_group_size' => 4,
            'images_bw_group_price' => 2,
        ]);

        $this->assertSame(10.0, $pricing->imagePrintPrice('color', 5));
        $this->assertSame(4.0, $pricing->imagePrintPrice('black_white', 7));
    }

    public function test_personal_image_packages_have_separate_prices(): void
    {
        $pricing = $this->pricing([
            'images_personal_5_price' => 11,
            'images_personal_8_price' => 17,
            'images_personal_16_price' => 29,
        ]);

        $this->assertSame(11.0, $pricing->imagePrintPrice('personal', 5));
        $this->assertSame(17.0, $pricing->imagePrintPrice('personal', 8));
        $this->assertSame(29.0, $pricing->imagePrintPrice('personal', 16));
    }

    public function test_mixed_images_are_grouped_by_type_before_calculating_the_total(): void
    {
        $pricing = $this->pricing([
            'images_color_group_size' => 2,
            'images_color_group_price' => 5,
            'images_bw_group_size' => 3,
            'images_bw_group_price' => 2,
            'images_personal_8_price' => 17,
        ]);
        $files = [
            (object) ['image_print_type' => 'color', 'copies' => 1],
            (object) ['image_print_type' => 'color', 'copies' => 2],
            (object) ['image_print_type' => 'black_white', 'copies' => 4],
            (object) ['image_print_type' => 'personal', 'copies' => 8],
        ];

        $result = $pricing->imageOrderPricing($files);

        $this->assertSame(['copies' => 3, 'price' => 10.0], $result['types']['color']);
        $this->assertSame(['copies' => 4, 'price' => 4.0], $result['types']['black_white']);
        $this->assertSame(['copies' => 8, 'price' => 17.0], $result['types']['personal']);
        $this->assertSame(31.0, $result['total']);
        $this->assertSame(31.0, array_sum($result['allocations']));
    }

    public function test_image_workflow_does_not_add_the_old_generic_service_fee(): void
    {
        $service = new ServiceDefinition([
            'workflow_type' => 'images',
            'is_system' => false,
        ]);

        $this->assertSame(0.0, (new ServicePricingService)->customServicePrice($service));
    }

    private function pricing(array $overrides): ServicePricingService
    {
        return new class($overrides) extends ServicePricingService
        {
            public function __construct(private readonly array $overrides)
            {
            }

            public function all(): array
            {
                $defaults = collect(self::DEFINITIONS)->mapWithKeys(
                    fn (array $definition, string $key) => [$key => (float) $definition['default']]
                )->all();

                return array_merge($defaults, $this->overrides);
            }
        };
    }
}
