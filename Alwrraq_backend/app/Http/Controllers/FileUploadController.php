<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderFile;
use App\Models\ServiceDefinition;
use App\Services\ServicePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    private readonly ServicePricingService $pricing;

    public function __construct(?ServicePricingService $pricing = null)
    {
        $this->pricing = $pricing ?? new ServicePricingService();
    }

    public function upload(Request $request)
    {
        try {
            // Validate file exists
            if (! $request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تحديد ملف',
                ], 400);
            }

            $file = $request->file('file');
            $type = $request->input('type', 'unknown');
            $service = $request->input('service', 'notes');

            if (! in_array($service, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research', 'images'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الخدمة غير معروف',
                ], 400);
            }

            $serviceDefinition = $this->resolveServiceDefinition($request, $service);
            if ($service === 'images' && ! $serviceDefinition) {
                return response()->json([
                    'success' => false,
                    'message' => 'اختر خدمة صور صالحة',
                ], 422);
            }

            $imagePrintType = null;
            $imageCopies = 1;

            if (in_array($service, ['notes', 'books', 'color_printing'], true) && $type !== 'pdf') {
                return response()->json([
                    'success' => false,
                    'message' => 'هذه الخدمة تقبل ملفات PDF فقط',
                ], 400);
            }

            if ($service === 'images' && $type !== 'image') {
                return response()->json([
                    'success' => false,
                    'message' => 'هذه الخدمة تقبل الصور فقط',
                ], 400);
            }

            if ($type === 'image' && $service !== 'images') {
                return response()->json([
                    'success' => false,
                    'message' => 'رفع الصور متاح فقط داخل خدمة الصور',
                ], 400);
            }

            // Validate file is not corrupted
            if (! $file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الملف غير صحيح أو تالف',
                ], 400);
            }

            // Validate file type based on request
            if ($type === 'word') {
                $allowedMimes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $allowedExtensions = ['doc', 'docx'];
            } elseif ($type === 'pdf') {
                $allowedMimes = ['application/pdf'];
                $allowedExtensions = ['pdf'];
            } elseif ($type === 'image') {
                $allowedMimes = [];
                $allowedExtensions = [
                    'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'bmp', 'dib',
                    'tif', 'tiff', 'heic', 'heif', 'avif', 'svg', 'ico',
                    'jfif', 'jxl', 'jp2', 'j2k', 'jpf', 'jpx', 'apng',
                    'psd', 'psb', 'ai', 'eps', 'hdr', 'exr',
                    'pbm', 'pgm', 'ppm', 'pnm',
                    'raw', 'dng', 'cr2', 'cr3', 'nef', 'nrw', 'arw', 'srf',
                    'sr2', 'raf', 'orf', 'rw2', 'pef', 'x3f',
                ];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الملف غير معروف',
                ], 400);
            }

            // Check MIME type
            $mimeType = strtolower((string) $file->getMimeType());
            $isSupportedImage = $type === 'image'
                && (str_starts_with($mimeType, 'image/')
                    || in_array(strtolower($file->getClientOriginalExtension()), $allowedExtensions, true));

            if (! $isSupportedImage && ! in_array($mimeType, $allowedMimes, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة الملف غير مدعومة',
                ], 400);
            }

            // Check file extension
            $extension = strtolower($file->getClientOriginalExtension());
            if ($type !== 'image' && ! in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة الملف غير صحيحة',
                ], 400);
            }

            // Create storage path if it doesn't exist
            $storagePath = 'uploads/'.$type.'s';
            $fullPath = storage_path('app/'.$storagePath);

            if (! is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            // Generate unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $timestamp = now()->timestamp;
            $filename = $type === 'image'
                ? Str::uuid()->toString().($extension === '' ? '' : '.'.$extension)
                : $originalName.'_'.$timestamp.'.'.$extension;
            $fileSize = filesize($file->getRealPath()) ?: 0;

            // Count pages
            $pageCount = 1;
            try {
                if ($type === 'pdf') {
                    $pageCount = $this->countPDFPages($file->getRealPath());
                } elseif ($type === 'word') {
                    $pageCount = $this->countWordPages($file->getRealPath());
                }
            } catch (\Exception $e) {
                $pageCount = 1;
            }

            // Store file in storage/app/uploads/
            $fullStoragePath = storage_path('app/'.$storagePath);
            $file->move($fullStoragePath, $filename);

            $path = $storagePath.'/'.$filename;

            $order = Order::query()->firstOrCreate([
                'user_id' => Auth::id(),
                'service_type' => $service,
                'service_definition_id' => $serviceDefinition?->id,
                'status' => 'new',
                'payment_status' => 'unpaid',
            ], [
                'print_total' => 0,
                'binding_total' => 0,
                'grand_total' => 0,
            ]);
            $order->forceFill([
                'admin_opened_at' => null,
                'admin_notification_seen_at' => null,
            ])->save();

            $filePayload = [
                'order_id' => $order->id,
                'file_type' => $type,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $filename,
                'path' => $path,
                'relative_path' => $type === 'image'
                    ? $this->normalizeRelativeImagePath((string) $request->input('relative_path'), $file->getClientOriginalName())
                    : null,
                'image_print_type' => $imagePrintType,
                'size' => $fileSize,
                'pages' => $pageCount,
                'copies' => $service === 'images' ? $imageCopies : 1,
                'print_sides' => $service === 'color_printing' ? 'one_side' : 'two_sides',
                'paper_color' => 'white',
                'thesis_project_type' => null,
                'university_name' => null,
                'cover_color' => null,
                'writing_color' => null,
                'cd_type' => 'none',
                'cd_copies' => 0,
                'binding_type' => $service === 'books' ? 'normal' : null,
                'print_price' => 0,
                'binding_price' => 0,
                'cd_price' => 0,
                'total_price' => 0,
            ];

            if (in_array($service, ['notes', 'books', 'color_printing'], true)) {
                $filePayload['page_size'] = 'A4';
            }

            $orderFile = OrderFile::query()->create($filePayload);

            $prices = $this->calculatePrices(
                $service,
                $pageCount,
                $orderFile->copies,
                $orderFile->binding_type,
                $orderFile->writing_color,
                $orderFile->file_type,
                $orderFile->paper_color,
                $orderFile->page_size,
                $orderFile->print_sides,
                $orderFile->cd_type,
                $orderFile->cd_copies,
                $orderFile->image_print_type
            );

            $orderFile->fill($prices)->save();
            if ($service === 'images') {
                $this->repriceImageOrderFiles($order);
                $orderFile->refresh();
            }
            $this->refreshOrderTotals($order);

            if ($path) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحميل الملف بنجاح',
                    'file_id' => $orderFile->id,
                    'order_id' => $order->id,
                    'filename' => $filename,
                    'original_name' => $orderFile->original_name,
                    'relative_path' => $orderFile->relative_path,
                    'image_print_type' => $orderFile->image_print_type,
                    'path' => $path,
                    'size' => $fileSize,
                    'pages' => $pageCount,
                    'copies' => $orderFile->copies,
                    'print_sides' => $orderFile->print_sides,
                    'page_size' => $orderFile->page_size,
                    'paper_color' => $orderFile->paper_color,
                    'binding_type' => $orderFile->binding_type,
                    'university_name' => $orderFile->university_name,
                    'cover_color' => $orderFile->cover_color,
                    'writing_color' => $orderFile->writing_color,
                    'cd_type' => $orderFile->cd_type,
                    'cd_copies' => $orderFile->cd_copies,
                    'print_price' => $orderFile->print_price,
                    'binding_price' => $orderFile->binding_price,
                    'cd_price' => $orderFile->cd_price,
                    'total_price' => $orderFile->total_price,
                    'order_totals' => $this->orderTotalsPayload($order->fresh()),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل حفظ الملف',
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Upload error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }

    public function updateFile(Request $request, OrderFile $file)
    {
        abort_unless($file->order->user_id === Auth::id() || Auth::user()?->role === 'admin', 403);

        $coverColorRule = $file->order->service_type === 'books'
            ? 'in:black,green,red,blue,beige,brown'
            : 'in:black,light_blue,navy,dark_green,light_green,burgundy,beige,white';

        $data = $request->validate([
            'binding_type' => ['nullable', 'in:tape,wire,normal,thermal,none'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:999'],
            'print_sides' => ['nullable', 'in:one_side,two_sides'],
            'page_size' => ['nullable', 'in:A4,A3,A5,B5'],
            'paper_color' => ['nullable', 'in:white,yellow'],
            'thesis_project_type' => ['nullable', 'in:thesis,supplementary,graduation'],
            'university_name' => ['nullable', 'string', 'max:255'],
            'cover_color' => ['nullable', $coverColorRule],
            'writing_color' => ['nullable', 'in:gold,black'],
            'cd_type' => ['nullable', 'in:none,plain,printed'],
            'cd_copies' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image_print_type' => ['nullable', 'in:color,black_white,personal'],
        ]);

        if ($file->order->service_type === 'images') {
            $imagePrintType = $data['image_print_type'] ?? $file->image_print_type ?? 'color';
            $imageCopies = (int) ($data['copies'] ?? $file->copies ?? 1);

            if ($imagePrintType === 'personal' && ! in_array($imageCopies, [5, 8, 16], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'اختر 5 أو 8 أو 16 نسخة للصورة الشخصية',
                ], 422);
            }

            $file->image_print_type = $imagePrintType;
            $file->copies = $imageCopies;
        }

        if (array_key_exists('binding_type', $data)) {
            $file->binding_type = $data['binding_type'];
        }

        if (array_key_exists('copies', $data) && $file->order->service_type !== 'images') {
            $file->copies = $data['copies'];
        }

        if (array_key_exists('print_sides', $data)) {
            $file->print_sides = $data['print_sides'] ?: 'two_sides';
        }

        if (array_key_exists('page_size', $data) && in_array($file->order->service_type, ['notes', 'books', 'color_printing'], true)) {
            $pageSize = $data['page_size'] ?: 'A4';
            $file->page_size = $pageSize === 'A3' && $file->order->service_type !== 'color_printing' ? 'A4' : $pageSize;
        }

        if (array_key_exists('paper_color', $data) && in_array($file->order->service_type, ['notes', 'books'], true)) {
            $file->paper_color = $data['paper_color'] ?: 'white';
        }

        if (array_key_exists('thesis_project_type', $data)) {
            $file->thesis_project_type = $data['thesis_project_type'];
        }

        if (array_key_exists('university_name', $data)) {
            $file->university_name = $data['university_name'];
        }

        if (array_key_exists('cover_color', $data)) {
            $file->cover_color = $data['cover_color'];
        }

        if (array_key_exists('writing_color', $data)) {
            $file->writing_color = $data['writing_color'];
        }

        if (in_array($file->order->service_type, ['thesis', 'phd'], true) && $file->file_type === 'pdf') {
            if (array_key_exists('cd_type', $data)) {
                $file->cd_type = $data['cd_type'] ?: 'none';
            }

            if (array_key_exists('cd_copies', $data)) {
                $file->cd_copies = (int) $data['cd_copies'];
            }

            if ($file->cd_type === 'none') {
                $file->cd_copies = 0;
            } else {
                $file->cd_copies = max(1, (int) $file->cd_copies);
            }
        } else {
            $file->cd_type = 'none';
            $file->cd_copies = 0;
        }

        if (
            in_array($file->order->service_type, ['thesis', 'phd'], true)
            && $file->writing_color === 'black'
            && ! in_array($file->cover_color, ['beige', 'light_blue', 'light_green', 'white'], true)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'الكتابة باللون الأسود متاحة فقط مع البيج أو الأزرق الفاتح أو الأخضر الفاتح أو الأبيض.',
            ], 422);
        }

        $prices = $this->calculatePrices(
            $file->order->service_type,
            $file->pages,
            $file->copies,
            $file->binding_type,
            $file->writing_color,
            $file->file_type,
            $file->paper_color,
            $file->page_size,
            $file->print_sides,
            $file->cd_type,
            $file->cd_copies,
            $file->image_print_type
        );

        $file->fill($prices)->save();
        if ($file->order->service_type === 'images') {
            $this->repriceImageOrderFiles($file->order);
            $file->refresh();
        }
        $this->refreshOrderTotals($file->order);

        return response()->json([
            'success' => true,
            'print_price' => $file->print_price,
            'binding_price' => $file->binding_price,
            'total_price' => $file->total_price,
            'thesis_project_type' => $file->thesis_project_type,
            'university_name' => $file->university_name,
            'print_sides' => $file->print_sides,
            'page_size' => $file->page_size,
            'paper_color' => $file->paper_color,
            'cover_color' => $file->cover_color,
            'writing_color' => $file->writing_color,
            'cd_type' => $file->cd_type,
            'cd_copies' => $file->cd_copies,
            'cd_price' => $file->cd_price,
            'image_print_type' => $file->image_print_type,
            'copies' => $file->copies,
            'order_totals' => $this->orderTotalsPayload($file->order->fresh()),
        ]);
    }

    public function saveResearchOrder(Request $request)
    {
        $data = $request->validate([
            'research_title' => ['required', 'string', 'max:255'],
            'research_student_name' => ['required', 'string', 'max:255'],
            'research_instructor_name' => ['required', 'string', 'max:255'],
            'university_name' => ['required', 'string', 'max:255'],
            'pages' => ['required', 'integer', 'min:1', 'max:9999'],
            'service_definition_id' => ['nullable', 'integer', 'exists:service_definitions,id'],
        ]);
        $serviceDefinition = $this->resolveServiceDefinition($request, 'research');

        $researchTitle = trim($data['research_title']);
        $pages = (int) $data['pages'];
        $prices = $this->calculatePrices('research', $pages, 1, null);

        $order = Order::query()->firstOrCreate([
            'user_id' => Auth::id(),
            'service_type' => 'research',
            'service_definition_id' => $serviceDefinition?->id,
            'status' => 'new',
            'payment_status' => 'unpaid',
        ], [
            'print_total' => 0,
            'binding_total' => 0,
            'grand_total' => 0,
        ]);
        $order->forceFill([
            'admin_opened_at' => null,
            'admin_notification_seen_at' => null,
        ])->save();

        $orderFile = $order->files()->where('file_type', 'research')->first();
        $payload = [
            'file_type' => 'research',
            'original_name' => $researchTitle,
            'stored_name' => 'research-request-'.$order->id,
            'path' => 'research-request',
            'size' => 0,
            'pages' => $pages,
            'copies' => 1,
            'print_sides' => 'two_sides',
            'thesis_project_type' => null,
            'university_name' => trim($data['university_name']),
            'research_title' => $researchTitle,
            'research_student_name' => trim($data['research_student_name']),
            'research_instructor_name' => trim($data['research_instructor_name']),
            'binding_type' => null,
            ...$prices,
        ];

        if ($orderFile) {
            $orderFile->fill($payload)->save();
        } else {
            $orderFile = $order->files()->create($payload);
        }

        $this->refreshOrderTotals($order);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ طلب إنشاء البحوث بنجاح',
            'file_id' => $orderFile->id,
            'order_id' => $order->id,
            'research_title' => $orderFile->research_title,
            'research_student_name' => $orderFile->research_student_name,
            'research_instructor_name' => $orderFile->research_instructor_name,
            'university_name' => $orderFile->university_name,
            'pages' => $orderFile->pages,
            'print_price' => $orderFile->print_price,
            'binding_price' => $orderFile->binding_price,
            'total_price' => $orderFile->total_price,
            'order_totals' => $this->orderTotalsPayload($order->fresh()),
        ]);
    }

    private function resolveServiceDefinition(Request $request, string $workflow): ?ServiceDefinition
    {
        $definitionId = $request->integer('service_definition_id');

        if ($definitionId) {
            return ServiceDefinition::query()
                ->whereKey($definitionId)
                ->where('workflow_type', $workflow)
                ->where('is_active', true)
                ->firstOrFail();
        }

        return ServiceDefinition::query()
            ->where('code', $workflow)
            ->where('workflow_type', $workflow)
            ->first();
    }

    private function normalizeRelativeImagePath(string $relativePath, string $fallbackName): string
    {
        $path = str_replace('\\', '/', trim($relativePath));
        $segments = collect(explode('/', $path))
            ->reject(fn (string $segment) => $segment === '' || $segment === '.' || $segment === '..')
            ->map(fn (string $segment) => trim((string) preg_replace('/[\\x00-\\x1F\\x7F]/u', '', $segment)))
            ->filter()
            ->values();

        if ($segments->isEmpty()) {
            return $fallbackName;
        }

        return Str::limit($segments->implode('/'), 1000, '');
    }

    public function destroyFile(OrderFile $file)
    {
        abort_unless($file->order->user_id === Auth::id() || Auth::user()?->role === 'admin', 403);

        $order = $file->order;

        if ($order->payment_status !== 'unpaid' && Auth::user()?->role !== 'admin') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف ملف بعد إتمام الدفع.',
                ], 422);
            }

            return back()->withErrors([
                'file' => 'لا يمكن حذف ملف بعد إتمام الدفع.',
            ]);
        }

        $absolutePath = storage_path('app/'.$file->path);

        $file->delete();

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }

        $orderDeleted = $order->payment_status === 'unpaid' && $order->files()->doesntExist();

        if ($orderDeleted) {
            $order->delete();
        } else {
            if ($order->service_type === 'images') {
                $this->repriceImageOrderFiles($order);
            }
            $this->refreshOrderTotals($order);
        }

        if (! request()->expectsJson()) {
            return back()->with(
                'status',
                $orderDeleted
                    ? 'تم حذف الملف والخدمة الفارغة من السلة بنجاح.'
                    : 'تم حذف الملف من الطلب بنجاح.'
            );
        }

        return response()->json([
            'success' => true,
            'order_deleted' => $orderDeleted,
        ]);
    }

    private function countPDFPages($filePath)
    {
        try {
            $content = file_get_contents($filePath);
            $pageMatches = preg_match_all('/\/Type\s*\/Page\b(?!s)/i', $content);
            if ($pageMatches > 0) {
                return max(1, $pageMatches);
            }

            preg_match_all('/\/Count\s+(\d+)/i', $content, $countMatches);
            $counts = array_map('intval', $countMatches[1] ?? []);

            return max(1, $counts ? max($counts) : 1);
        } catch (\Exception $e) {
            return 1;
        }
    }

    private function countWordPages($filePath)
    {
        try {
            // DOCX is a ZIP file
            $zip = new \ZipArchive;
            if ($zip->open($filePath) !== true) {
                return 1;
            }

            // Try to read document.xml
            $docXml = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($docXml === false) {
                return 1;
            }

            // Count paragraphs as approximation
            $paragraphs = substr_count($docXml, '<w:p>');
            $pageCount = max(1, ceil($paragraphs / 30));

            return $pageCount;
        } catch (\Exception $e) {
            return 1;
        }
    }

    private function calculatePrices(string $service, int $pages, int $copies, ?string $binding, ?string $writingColor = null, ?string $fileType = null, ?string $paperColor = null, ?string $pageSize = null, ?string $printSides = null, ?string $cdType = 'none', int $cdCopies = 0, ?string $imagePrintType = null): array
    {
        $cdCount = $cdType === 'none' ? 0 : max(1, $cdCopies);
        $cdPrice = in_array($service, ['thesis', 'phd'], true) && $fileType === 'pdf'
            ? match ($cdType) {
                'plain' => $this->pricing->value('academic_cd_plain') * $cdCount,
                'printed' => $this->pricing->value('academic_cd_printed') * $cdCount,
                default => 0,
            }
        : 0;

        if (in_array($service, ['thesis', 'phd'], true) && $fileType === 'word') {
            return $this->normalizePrices([
                'print_price' => 0,
                'binding_price' => 0,
                'cd_price' => 0,
                'total_price' => 0,
            ]);
        }

        if (in_array($service, ['formatting', 'research'], true)) {
            $pagePrice = $service === 'formatting'
                ? $this->pricing->value('formatting_page_price')
                : $this->pricing->value('research_page_price');
            $servicePrice = $pages * $pagePrice;

            return $this->normalizePrices([
                'print_price' => 0,
                'binding_price' => $servicePrice,
                'cd_price' => 0,
                'total_price' => $servicePrice,
            ]);
        }

        if ($service === 'images') {
            $imagePrice = in_array($imagePrintType, ['color', 'black_white', 'personal'], true)
                ? $this->pricing->imagePrintPrice($imagePrintType, $copies)
                : 0;

            return $this->normalizePrices([
                'print_price' => $imagePrice,
                'binding_price' => 0,
                'cd_price' => 0,
                'total_price' => $imagePrice,
            ]);
        }

        if ($service === 'color_printing') {
            $sheetCount = max(1, $pages) * max(1, $copies);
            $pageSize = $pageSize ?: 'A4';
            $printPrice = $this->colorPrintingPrice($sheetCount, $pageSize);
            $thermalBindingUnits = $printSides === 'two_sides'
                ? (int) ceil($sheetCount / 2)
                : $sheetCount;
            $bindingPrice = $binding === 'thermal'
                ? $thermalBindingUnits * $this->pricing->value($pageSize === 'A3' ? 'thermal_a3_sheet' : 'thermal_a4_sheet')
                : $this->notesBindingPrice($pages, $binding);

            return $this->normalizePrices([
                'print_price' => $printPrice,
                'binding_price' => $bindingPrice,
                'cd_price' => 0,
                'total_price' => $printPrice + $bindingPrice,
            ]);
        }

        if (in_array($service, ['notes', 'books'], true)) {
            $copyCount = max(1, $copies);
            $printPages = max(1, $pages) * $copyCount;

            if ($service === 'books') {
                $printPrice = $paperColor === 'yellow'
                    ? ceil($printPages / $this->pricing->value('books_yellow_pages')) * $this->pricing->value('books_yellow_group_price')
                    : ceil($printPages / $this->pricing->value('books_white_pages')) * $this->pricing->value('books_white_group_price');
                $bindingPrice = $this->pricing->value($pageSize === 'A4' ? 'books_binding_a4' : 'books_binding_small') * $copyCount;

                return $this->normalizePrices([
                    'print_price' => $printPrice,
                    'binding_price' => $bindingPrice,
                    'cd_price' => 0,
                    'total_price' => $printPrice + $bindingPrice,
                ]);
            }

            $printPrice = $paperColor === 'yellow'
                ? ceil($printPages / $this->pricing->value('notes_yellow_pages')) * $this->pricing->value('notes_yellow_group_price')
                : ceil($printPages / $this->pricing->value('notes_white_pages')) * $this->pricing->value('notes_white_group_price');
            $bindingPrice = $this->notesBindingPrice($pages, $binding) * $copyCount;

            return $this->normalizePrices([
                'print_price' => $printPrice,
                'binding_price' => $bindingPrice,
                'cd_price' => 0,
                'total_price' => $printPrice + $bindingPrice,
            ]);
        }

        $copyCount = max(1, $copies);
        $printPrice = $this->printPrice($pages, $copyCount);
        if (! in_array($writingColor, ['gold', 'black'], true)) {
            return $this->normalizePrices([
                'print_price' => $printPrice,
                'binding_price' => 0,
                'cd_price' => $cdPrice,
                'total_price' => $printPrice + $cdPrice,
            ]);
        }

        $singleBinding = $this->pricing->value($writingColor === 'gold' ? 'academic_gold_single' : 'academic_black_single');
        $multiBinding = $this->pricing->value($writingColor === 'gold' ? 'academic_gold_multiple' : 'academic_black_multiple');
        $bindingPrice = $copyCount === 1 ? $singleBinding : $multiBinding * $copyCount;

        return $this->normalizePrices([
            'print_price' => $printPrice,
            'binding_price' => $bindingPrice,
            'cd_price' => $cdPrice,
            'total_price' => $printPrice + $bindingPrice + $cdPrice,
        ]);
    }

    private function normalizePrices(array $prices): array
    {
        return collect($prices)->map(fn ($price) => floor((float) $price) === (float) $price
            ? (int) $price
            : round((float) $price, 2))->all();
    }

    private function printPrice(int $pages, int $copies): float
    {
        return ceil($pages / $this->pricing->value('academic_print_pages'))
            * $this->pricing->value('academic_print_group_price')
            * max(1, $copies);
    }

    private function colorPrintingPrice(int $sheetCount, string $pageSize): float
    {
        if ($pageSize === 'A3') {
            $unitPrice = match (true) {
                $sheetCount <= 5 => $this->pricing->value('color_a3_first_5'),
                $sheetCount <= 10 => $this->pricing->value('color_a3_to_10'),
                default => $this->pricing->value('color_a3_over_10'),
            };

            return $sheetCount * $unitPrice;
        }

        $unitPrice = match (true) {
            $sheetCount <= 5 => $this->pricing->value('color_a4_first_5'),
            $sheetCount <= 10 => $this->pricing->value('color_a4_to_10'),
            default => $this->pricing->value('color_a4_over_10'),
        };

        return $sheetCount * $unitPrice;
    }

    private function notesBindingPrice(int $pages, ?string $binding): float
    {
        if ($binding === 'normal') {
            return $this->pricing->value('notes_binding_normal');
        }

        if ($binding === 'wire') {
            if ($pages < 100) {
                return $this->pricing->value('notes_binding_wire_under_100');
            }

            if ($pages < 300) {
                return $this->pricing->value('notes_binding_wire_under_300');
            }

            if ($pages <= 600) {
                return $this->pricing->value('notes_binding_wire_up_to_600');
            }

            return $this->pricing->value('notes_binding_wire_over_600');
        }

        return 0;
    }

    private function refreshOrderTotals(Order $order): void
    {
        $order->load(['files', 'serviceDefinition']);
        $printTotal = 0;
        if ($order->service_type === 'images') {
            $printTotal = (float) $order->files->sum('print_price');
        } elseif (! in_array($order->service_type, ['formatting', 'research'], true)) {
            if (in_array($order->service_type, ['notes', 'books'], true)) {
                $printTotal = $this->printProductPrintTotal($order);
            } elseif ($order->service_type === 'color_printing') {
                $printTotal = (float) $order->files->sum('print_price');
            } else {
                $filesForPrint = $order->files->where('file_type', 'pdf');
                $printUnits = $filesForPrint->sum(
                    fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies)
                );
                $printTotal = $this->printPrice((int) $printUnits, 1);
            }
        }
        $filesForBinding = in_array($order->service_type, ['thesis', 'phd'], true)
            ? $order->files->where('file_type', 'pdf')
            : $order->files;
        $bindingTotal = (float) $filesForBinding->sum('binding_price')
            + $this->pricing->customServicePrice($order->serviceDefinition);
        $cdTotal = (float) $order->files->sum('cd_price');
        $baseTotal = $printTotal + $bindingTotal + $cdTotal;
        $discountAmount = min((float) $order->discount_amount, $baseTotal);
        $subtotal = max(0, $baseTotal - $discountAmount);
        $deliveryFee = in_array($order->service_type, ['notes', 'books', 'color_printing', 'thesis', 'phd'], true)
            ? $this->deliveryFee($order->delivery_method, $baseTotal)
            : 0;

        $order->update([
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'grand_total' => $subtotal + $deliveryFee,
        ]);
    }

    private function repriceImageOrderFiles(Order $order): void
    {
        $order->load('files');
        $pricing = $this->pricing->imageOrderPricing($order->files);

        foreach ($order->files->values() as $index => $file) {
            $price = (float) ($pricing['allocations'][$index] ?? 0);
            $file->forceFill([
                'print_price' => $price,
                'binding_price' => 0,
                'cd_price' => 0,
                'total_price' => $price,
            ])->save();
        }

        $order->load('files');
    }

    private function printProductPrintTotal(Order $order): float
    {
        $whitePages = (int) $order->files
            ->where('file_type', 'pdf')
            ->filter(fn (OrderFile $file) => ($file->paper_color ?: 'white') === 'white')
            ->sum(fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies));
        $yellowPages = (int) $order->files
            ->where('file_type', 'pdf')
            ->filter(fn (OrderFile $file) => $file->paper_color === 'yellow')
            ->sum(fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies));

        $prefix = $order->service_type === 'notes' ? 'notes' : 'books';
        $whiteTotal = ceil($whitePages / $this->pricing->value($prefix.'_white_pages'))
            * $this->pricing->value($prefix.'_white_group_price');
        $yellowTotal = ceil($yellowPages / $this->pricing->value($prefix.'_yellow_pages'))
            * $this->pricing->value($prefix.'_yellow_group_price');

        return $whiteTotal + $yellowTotal;
    }

    private function deliveryFee(?string $method, float $subtotal): float
    {
        return match ($method) {
            'islamic_university_delivery' => $subtotal >= $this->pricing->value('delivery_university_free_from') ? 0 : $this->pricing->value('delivery_university_fee'),
            'madinah_delivery' => $this->pricing->value('delivery_madinah_fee'),
            'redbox_delivery' => $this->pricing->value('delivery_redbox_fee'),
            default => 0,
        };
    }

    private function orderTotalsPayload(Order $order): array
    {
        return [
            'print_total' => $order->print_total,
            'binding_total' => $order->binding_total,
            'cd_total' => (float) $order->files()->sum('cd_price'),
            'delivery_fee' => $order->delivery_fee,
            'grand_total' => $order->grand_total,
        ];
    }
}
