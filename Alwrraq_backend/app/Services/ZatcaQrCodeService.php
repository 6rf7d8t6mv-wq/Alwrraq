<?php

namespace App\Services;

use App\Models\Order;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use RuntimeException;

class ZatcaQrCodeService
{
    public const SELLER_NAME = 'شركة مسير المدينة المحدودة';

    public const VAT_NUMBER = '314417169600003';

    public function encodedPayload(Order $order): string
    {
        $invoiceTotal = number_format((float) $order->grand_total, 2, '.', '');
        $vatTotal = number_format(((float) $order->grand_total * 15) / 115, 2, '.', '');
        $timestamp = $order->created_at->clone()->utc()->format('Y-m-d\TH:i:s\Z');

        $tlv = $this->field(1, self::SELLER_NAME)
            .$this->field(2, self::VAT_NUMBER)
            .$this->field(3, $timestamp)
            .$this->field(4, $invoiceTotal)
            .$this->field(5, $vatTotal);

        return base64_encode($tlv);
    }

    public function svg(Order $order): string
    {
        $writer = new SvgWriter();
        $builder = new Builder(
            writer: $writer,
            writerOptions: [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
                SvgWriter::WRITER_OPTION_COMPACT => true,
            ],
            validateResult: false,
            data: $this->encodedPayload($order),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 240,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return preg_replace(
            '/<svg\s/',
            '<svg style="display:block;width:100%;height:100%" ',
            $builder->build()->getString(),
            1
        ) ?? '';
    }

    private function field(int $tag, string $value): string
    {
        $length = strlen($value);
        if ($length > 255) {
            throw new RuntimeException("ZATCA TLV field {$tag} exceeds 255 bytes.");
        }

        return chr($tag).chr($length).$value;
    }
}
