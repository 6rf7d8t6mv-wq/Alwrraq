<?php

namespace Tests\Unit;

use App\Http\Controllers\FileUploadController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AcademicCdPricingTest extends TestCase
{
    #[DataProvider('cdOptions')]
    public function test_academic_pdf_cd_price_is_included_in_file_total(
        string $cdType,
        int $cdCopies,
        int $expectedCdPrice,
        int $expectedTotal
    ): void {
        $prices = $this->calculatePrices('thesis', 30, 1, null, 'gold', 'pdf', 'white', 'A4', 'two_sides', $cdType, $cdCopies);

        $this->assertSame($expectedCdPrice, $prices['cd_price']);
        $this->assertSame($expectedTotal, $prices['total_price']);
    }

    public static function cdOptions(): array
    {
        return [
            'without cd' => ['none', 0, 0, 92],
            'two plain cds' => ['plain', 2, 10, 102],
            'three printed cds' => ['printed', 3, 30, 122],
        ];
    }

    public function test_word_preview_file_never_receives_a_cd_price(): void
    {
        $prices = $this->calculatePrices('phd', 30, 1, null, 'gold', 'word', 'white', 'A4', 'two_sides', 'printed', 3);

        $this->assertSame(0, $prices['cd_price']);
        $this->assertSame(0, $prices['total_price']);
    }

    private function calculatePrices(mixed ...$arguments): array
    {
        $method = new ReflectionMethod(FileUploadController::class, 'calculatePrices');

        return $method->invoke(new FileUploadController, ...$arguments);
    }
}
