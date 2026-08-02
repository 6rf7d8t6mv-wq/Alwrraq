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

        if ($draft->pdf_path && $this->storedPdfIsValid($draft->pdf_path)) {
            return Storage::disk('local')->path($draft->pdf_path);
        }

        if ($draft->pdf_path) {
            Storage::disk('local')->delete($draft->pdf_path);
            $draft->forceFill(['pdf_path' => null])->save();
        }

        if ($draft->image_path && Storage::disk('local')->exists($draft->image_path)) {
            $pdf = $this->renderPdfFromFinalImage($draft);
        } else {
            $html = view('resume.pdf', [
                'draft' => $draft,
                'paid' => true,
                'pdfMode' => true,
            ])->render();

            try {
                $pdf = $this->renderPdf($html, $draft->language !== 'en');
            } catch (Throwable $primaryException) {
                Log::warning('Primary resume PDF rendering failed; retrying without Arabic shaping.', [
                    'resume_draft_id' => $draft->id,
                    'error' => $primaryException->getMessage(),
                ]);
                try {
                    $pdf = $this->renderPdf($html, false);
                } catch (Throwable $fallbackException) {
                    Log::warning('Resume HTML PDF fallback failed; retrying from the final image.', [
                        'resume_draft_id' => $draft->id,
                        'error' => $fallbackException->getMessage(),
                    ]);
                    $pdf = $this->renderPdfFromFinalImage($draft);
                }
            }
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

    private function renderPdfFromFinalImage(ResumeDraft $draft): string
    {
        abort_unless(
            $draft->image_path && Storage::disk('local')->exists($draft->image_path),
            500,
            'تعذر إنشاء ملف PDF. أنشئ نسخة الصورة أولًا ثم أعد المحاولة.'
        );

        $absoluteImage = Storage::disk('local')->path($draft->image_path);
        $mime = mime_content_type($absoluteImage) ?: 'image/png';
        $image = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($absoluteImage));
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>'
            .'@page{margin:0;size:A4}html,body{margin:0;padding:0;width:210mm;height:297mm;overflow:hidden}'
            .'img{display:block;width:210mm;height:297mm;object-fit:contain}'
            .'</style></head><body><img src="'.$image.'" alt=""></body></html>';

        return $this->renderPdf($html, false, false);
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
