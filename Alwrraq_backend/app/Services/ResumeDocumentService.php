<?php

namespace App\Services;

use App\Models\ResumeDraft;
use ArPHP\I18N\Arabic;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class ResumeDocumentService
{
    public function ensurePdf(ResumeDraft $draft): string
    {
        $draft->loadMissing('order');
        abort_unless($draft->isPaid(), 403);

        if ($draft->pdf_path && Storage::disk('local')->exists($draft->pdf_path)) {
            return Storage::disk('local')->path($draft->pdf_path);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4');
        $html = view('resume.pdf', [
            'draft' => $draft,
            'paid' => true,
            'pdfMode' => true,
        ])->render();
        if ($draft->language === 'ar') {
            $html = $this->shapeArabicTextNodes($html);
        }
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
        $dompdf->getCanvas()->page_text(
            540,
            820,
            $draft->language === 'ar' ? 'صفحة {PAGE_NUM} من {PAGE_COUNT}' : 'Page {PAGE_NUM} of {PAGE_COUNT}',
            null,
            8,
            [0.39, 0.45, 0.55]
        );

        $path = 'private/resumes/final/resume-'.$draft->id.'.pdf';
        Storage::disk('local')->put($path, $dompdf->output());
        $draft->forceFill([
            'pdf_path' => $path,
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();
        $draft->order?->forceFill(['status' => 'completed'])->save();

        return Storage::disk('local')->path($path);
    }

    private function shapeArabicTextNodes(string $html): string
    {
        $arabic = new Arabic();

        return preg_replace_callback(
            '/>([^<>]*[\x{0600}-\x{06FF}][^<>]*)</u',
            static fn (array $match): string => '>'
                .$arabic->utf8Glyphs($match[1], 500, false, false)
                .'<',
            $html
        ) ?? $html;
    }
}
