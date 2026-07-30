<?php

namespace App\Services;

use App\Models\ResumeDraft;
use ArPHP\I18N\Arabic;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ResumeDocumentService
{
    public function ensurePdf(ResumeDraft $draft): string
    {
        $draft->loadMissing('order');
        abort_unless($draft->isPaid(), 403);

        if ($draft->pdf_path && Storage::disk('local')->exists($draft->pdf_path)) {
            return Storage::disk('local')->path($draft->pdf_path);
        }

        $html = view('resume.pdf', [
            'draft' => $draft,
            'paid' => true,
            'pdfMode' => true,
        ])->render();

        try {
            $pdf = $this->renderPdf($html, $draft->language === 'ar');
        } catch (Throwable $exception) {
            Log::warning('Primary resume PDF rendering failed; retrying without Arabic shaping.', [
                'resume_draft_id' => $draft->id,
                'error' => $exception->getMessage(),
            ]);
            $pdf = $this->renderPdf($html, false);
        }

        $path = 'private/resumes/final/resume-'.$draft->id.'.pdf';
        if (! Storage::disk('local')->put($path, $pdf)) {
            throw new RuntimeException('Unable to store the generated resume PDF.');
        }
        $draft->forceFill([
            'pdf_path' => $path,
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();
        $draft->order?->forceFill(['status' => 'completed'])->save();

        return Storage::disk('local')->path($path);
    }

    private function renderPdf(string $html, bool $shapeArabic): string
    {
        if ($shapeArabic) {
            $html = $this->shapeArabicTextNodes($html);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
        $dompdf->getCanvas()->page_text(
            540,
            820,
            $shapeArabic ? $this->shapeArabicTextNodes('صفحة {PAGE_NUM} من {PAGE_COUNT}') : 'Page {PAGE_NUM} of {PAGE_COUNT}',
            null,
            8,
            [0.39, 0.45, 0.55]
        );

        $pdf = $dompdf->output();
        if (! str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('The generated resume document is not a valid PDF.');
        }

        return $pdf;
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
