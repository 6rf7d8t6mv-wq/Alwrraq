<?php

namespace App\Services;

class InvoiceBarcodeService
{
    /**
     * Generate a dependency-free Code 39 barcode for the invoice reference.
     */
    public function svg(int $orderId): string
    {
        $value = 'ALW-'.str_pad((string) $orderId, 8, '0', STR_PAD_LEFT);
        $patterns = [
            '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw',
            '3' => 'wnwwnnnnn', '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn',
            '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw', '8' => 'wnnwnnwnn',
            '9' => 'nnwwnnwnn', 'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw',
            'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw', 'E' => 'wnnnwwnnn',
            'F' => 'nnwnwwnnn', 'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn',
            'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn', 'K' => 'wnnnnnnww',
            'L' => 'nnwnnnnww', 'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww',
            'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn', 'Q' => 'nnnnnnwww',
            'R' => 'wnnnnnwwn', 'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn',
            'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw', 'W' => 'wwwnnnnnn',
            'X' => 'nwnnwnnnw', 'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn',
            '-' => 'nwnnnnwnw', '*' => 'nwnnwnwnn',
        ];

        $narrow = 2;
        $wide = 5;
        $gap = 2;
        $quiet = 18;
        $height = 54;
        $x = $quiet;
        $bars = '';

        foreach (str_split('*'.$value.'*') as $character) {
            foreach (str_split($patterns[$character]) as $index => $widthType) {
                $width = $widthType === 'w' ? $wide : $narrow;
                if ($index % 2 === 0) {
                    $bars .= '<rect x="'.$x.'" y="2" width="'.$width.'" height="'.$height.'" />';
                }
                $x += $width;
            }
            $x += $gap;
        }

        $svgWidth = $x + $quiet - $gap;

        return '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" role="img" aria-label="باركود الفاتورة '
            .htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            .'" viewBox="0 0 '.$svgWidth.' 76" preserveAspectRatio="xMidYMid meet">'
            .'<g fill="#0f172a">'.$bars.'</g>'
            .'<text x="'.($svgWidth / 2).'" y="71" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" letter-spacing="1">'
            .htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            .'</text></svg>';
    }
}
