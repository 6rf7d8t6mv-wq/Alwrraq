<?php

namespace Tests\Unit;

use App\Services\InvoiceBarcodeService;
use PHPUnit\Framework\TestCase;

class InvoiceBarcodeServiceTest extends TestCase
{
    public function test_it_generates_a_code_39_invoice_barcode(): void
    {
        $svg = (new InvoiceBarcodeService())->svg(25);

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('width="100%" height="100%"', $svg);
        $this->assertStringContainsString('ALW-00000025', $svg);
        $this->assertStringContainsString('<rect ', $svg);
        $this->assertStringContainsString('aria-label="باركود الفاتورة ALW-00000025"', $svg);
    }
}
