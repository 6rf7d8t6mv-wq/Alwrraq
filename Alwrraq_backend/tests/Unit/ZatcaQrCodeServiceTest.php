<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\ZatcaQrCodeService;
use Carbon\Carbon;
use Tests\TestCase;

class ZatcaQrCodeServiceTest extends TestCase
{
    public function test_it_generates_the_five_required_zatca_tlv_fields_and_a_qr_svg(): void
    {
        $order = new Order();
        $order->forceFill([
            'id' => 25,
            'grand_total' => 115.00,
            'created_at' => Carbon::parse('2026-07-30 12:30:00', 'UTC'),
        ]);

        $service = new ZatcaQrCodeService();
        $fields = $this->decodeTlv($service->encodedPayload($order));
        $svg = $service->svg($order);

        $this->assertSame([
            1 => 'شركة مسير المدينة المحدودة',
            2 => '314417169600003',
            3 => '2026-07-30T12:30:00Z',
            4 => '115.00',
            5 => '15.00',
        ], $fields);
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('style="display:block;width:100%;height:100%"', $svg);
        $this->assertStringContainsString('viewBox=', $svg);
        $this->assertStringContainsString('<path', $svg);
    }

    /**
     * @return array<int, string>
     */
    private function decodeTlv(string $encodedPayload): array
    {
        $payload = base64_decode($encodedPayload, true);
        $this->assertNotFalse($payload);

        $fields = [];
        $offset = 0;
        while ($offset < strlen($payload)) {
            $tag = ord($payload[$offset++]);
            $length = ord($payload[$offset++]);
            $fields[$tag] = substr($payload, $offset, $length);
            $offset += $length;
        }

        return $fields;
    }
}
