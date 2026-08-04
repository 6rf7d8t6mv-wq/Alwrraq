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
    private const VECTOR_PDF_VERSION = 3;

    public function ensurePdf(ResumeDraft $draft): string
    {
        $draft->loadMissing('order');
        abort_unless($draft->isPaid(), 403);

        if ($draft->pdf_path
            && $this->pdfIsCurrentVectorVersion($draft)
            && $this->storedPdfIsValid($draft->pdf_path)) {
            return Storage::disk('local')->path($draft->pdf_path);
        }

        if ($draft->pdf_path) {
            Storage::disk('local')->delete($draft->pdf_path);
            $draft->forceFill(['pdf_path' => null])->save();
        }

        $html = view('resume.pdf', [
            'draft' => $draft,
            'paid' => true,
            'pdfMode' => true,
        ])->render();

        try {
            // Keep text and rules as PDF vectors. Rendering the saved PNG here
            // makes every letter pixelated as soon as the customer zooms in.
            $pdf = $this->renderPdf($html, $draft->language !== 'en', false);
        } catch (Throwable $primaryException) {
            Log::warning('Vector resume PDF rendering failed; retrying without Arabic shaping.', [
                'resume_draft_id' => $draft->id,
                'error' => $primaryException->getMessage(),
            ]);
            try {
                $pdf = $this->renderPdf($html, false, false);
            } catch (Throwable $fallbackException) {
                Log::error('Resume vector PDF fallback failed.', [
                    'resume_draft_id' => $draft->id,
                    'error' => $fallbackException->getMessage(),
                ]);
                throw new RuntimeException(
                    'تعذر إنشاء ملف PDF المتجهي. حاول مرة أخرى.',
                    previous: $fallbackException
                );
            }
        }

        $sourceVersion = $draft->image_path
            ? pathinfo($draft->image_path, PATHINFO_FILENAME)
            : 'resume-'.$draft->id.'-'.now()->format('YmdHisv');
        $path = 'private/resumes/final/'.$sourceVersion.'-'.$this->uiLocale().'-vector-v'.self::VECTOR_PDF_VERSION.'.pdf';
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

    private function storedPdfIsValid(string $path): bool
    {
        if (! Storage::disk('local')->exists($path) || Storage::disk('local')->size($path) < 1000) {
            return false;
        }

        $fontPath = public_path('fonts/tajawal/Tajawal-Regular.ttf');
        if (is_file($fontPath) && Storage::disk('local')->lastModified($path) < filemtime($fontPath)) {
            return false;
        }

        $stream = Storage::disk('local')->readStream($path);
        if (! is_resource($stream)) {
            return false;
        }

        try {
            return fread($stream, 5) === '%PDF-';
        } finally {
            fclose($stream);
        }
    }

    private function pdfIsCurrentVectorVersion(ResumeDraft $draft): bool
    {
        return str_ends_with(
            pathinfo((string) $draft->pdf_path, PATHINFO_FILENAME),
            '-'.$this->uiLocale().'-vector-v'.self::VECTOR_PDF_VERSION
        );
    }

    private function uiLocale(): string
    {
        return session('ui_locale', 'ar') === 'en' ? 'en' : 'ar';
    }

    private function renderPdf(string $html, bool $shapeArabic, bool $showPageNumber = true): string
    {
        if ($shapeArabic) {
            $html = $this->shapeArabicTextNodes($html);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Tajawal');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
        if (! $showPageNumber && $dompdf->getCanvas()->get_page_count() !== 1) {
            throw new RuntimeException('The generated resume PDF must contain exactly one page.');
        }
        if ($showPageNumber) {
            $dompdf->getCanvas()->page_text(
                540,
                820,
                $shapeArabic ? $this->shapeArabicTextNodes('صفحة {PAGE_NUM} من {PAGE_COUNT}') : 'Page {PAGE_NUM} of {PAGE_COUNT}',
                null,
                8,
                [0.39, 0.45, 0.55]
            );
        }

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
