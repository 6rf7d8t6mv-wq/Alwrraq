<?php

namespace App\Services;

use App\Models\ResumeDraft;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ResumeDocumentService
{
    private const IMAGE_PDF_VERSION = 1;

    public function ensurePdf(ResumeDraft $draft): string
    {
        $draft->loadMissing('order');
        abort_unless($draft->isPaid(), 403);

        if ($draft->pdf_path
            && $this->pdfIsCurrentImageVersion($draft)
            && $this->storedPdfIsValid($draft->pdf_path)) {
            return Storage::disk('local')->path($draft->pdf_path);
        }

        if ($draft->pdf_path) {
            Storage::disk('local')->delete($draft->pdf_path);
            $draft->forceFill(['pdf_path' => null])->save();
        }

        if (! $draft->image_path || ! Storage::disk('local')->exists($draft->image_path)) {
            throw new RuntimeException('يجب تجهيز صورة السيرة عالية الدقة قبل إنشاء ملف PDF.');
        }

        $image = Storage::disk('local')->get($draft->image_path);
        $imageInfo = @getimagesizefromstring($image);
        if (! $imageInfo || ($imageInfo[0] ?? 0) < 3000 || ($imageInfo[1] ?? 0) < 4400) {
            throw new RuntimeException('صورة السيرة غير صالحة أو ليست بالدقة المطلوبة.');
        }
        $mime = $imageInfo['mime'] ?? 'image/png';
        $pdf = $this->renderImagePdf('data:'.$mime.';base64,'.base64_encode($image));

        $sourceVersion = pathinfo($draft->image_path, PATHINFO_FILENAME);
        $path = 'private/resumes/final/'.$sourceVersion.'-image-v'.self::IMAGE_PDF_VERSION.'.pdf';
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

    private function pdfIsCurrentImageVersion(ResumeDraft $draft): bool
    {
        return str_ends_with(
            pathinfo((string) $draft->pdf_path, PATHINFO_FILENAME),
            pathinfo((string) $draft->image_path, PATHINFO_FILENAME).'-image-v'.self::IMAGE_PDF_VERSION
        );
    }

    private function renderImagePdf(string $imageSource): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4');
        $dompdf->loadHtml(
            '<!doctype html><html><head><meta charset="utf-8"><style>'
            .'@page{size:A4;margin:0}html,body{margin:0;padding:0;width:210mm;height:297mm;overflow:hidden}'
            .'img{display:block;width:210mm;height:297mm;margin:0;padding:0}'
            .'</style></head><body><img src="'.$imageSource.'" alt=""></body></html>',
            'UTF-8'
        );
        $dompdf->render();
        if ($dompdf->getCanvas()->get_page_count() !== 1) {
            throw new RuntimeException('The generated resume PDF must contain exactly one page.');
        }

        $pdf = $dompdf->output();
        if (! str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('The generated resume document is not a valid PDF.');
        }

        return $pdf;
    }
}
