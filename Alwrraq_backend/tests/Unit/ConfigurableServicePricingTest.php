<?php

namespace Tests\Unit;

use App\Http\Controllers\FileUploadController;
use App\Services\ServicePricingService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ConfigurableServicePricingTest extends TestCase
{
    public function test_white_and_yellow_paper_group_prices_are_configurable(): void
    {
        $controller = $this->controller([
            'notes_white_pages' => 10,
            'notes_white_group_price' => 2,
            'notes_binding_normal' => 7,
            'books_yellow_pages' => 10,
            'books_yellow_group_price' => 3,
            'books_binding_a4' => 70,
        ]);

        $notes = $this->prices($controller, 'notes', 13, 1, 'normal', null, 'pdf', 'white', 'A4');
        $books = $this->prices($controller, 'books', 21, 1, 'normal', null, 'pdf', 'yellow', 'A4');

        $this->assertSame(4, $notes['print_price']);
        $this->assertSame(7, $notes['binding_price']);
        $this->assertSame(9, $books['print_price']);
        $this->assertSame(70, $books['binding_price']);
    }

    public function test_academic_print_binding_and_cd_prices_are_configurable(): void
    {
        $controller = $this->controller([
            'academic_print_pages' => 10,
            'academic_print_group_price' => 2,
            'academic_gold_single' => 100,
            'academic_cd_plain' => 7,
        ]);

        $prices = $this->prices($controller, 'thesis', 30, 1, null, 'gold', 'pdf', 'white', 'A4', 'two_sides', 'plain', 2);

        $this->assertSame(6, $prices['print_price']);
        $this->assertSame(100, $prices['binding_price']);
        $this->assertSame(14, $prices['cd_price']);
        $this->assertSame(120, $prices['total_price']);
    }

    public function test_academic_service_page_prices_are_configurable(): void
    {
        $controller = $this->controller([
            'formatting_page_price' => 12,
            'research_page_price' => 13,
        ]);

        $formatting = $this->prices($controller, 'formatting', 3, 1, null);
        $research = $this->prices($controller, 'research', 4, 1, null);

        $this->assertSame(36, $formatting['total_price']);
        $this->assertSame(52, $research['total_price']);
    }

    private function controller(array $overrides): FileUploadController
    {
        $pricing = new class($overrides) extends ServicePricingService {
            public function __construct(private readonly array $overrides)
            {
            }

            public function value(string $key): float
            {
                return (float) ($this->overrides[$key] ?? parent::value($key));
            }
        };

        return new FileUploadController($pricing);
    }

    private function prices(FileUploadController $controller, mixed ...$arguments): array
    {
        return (new ReflectionMethod(FileUploadController::class, 'calculatePrices'))
            ->invoke($controller, ...$arguments);
    }
}
