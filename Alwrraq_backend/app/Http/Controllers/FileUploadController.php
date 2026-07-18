<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileUploadController extends Controller
{
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

            if (! in_array($service, ['notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الخدمة غير معروف',
                ], 400);
            }

            if (in_array($service, ['notes', 'books', 'color_printing'], true) && $type !== 'pdf') {
                return response()->json([
                    'success' => false,
                    'message' => 'هذه الخدمة تقبل ملفات PDF فقط',
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
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الملف غير معروف',
                ], 400);
            }

            // Check MIME type
            if (! in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة الملف غير مدعومة',
                ], 400);
            }

            // Check file extension
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions)) {
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
            $filename = $originalName.'_'.$timestamp.'.'.$extension;
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
                'size' => $fileSize,
                'pages' => $pageCount,
                'copies' => 1,
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
                $orderFile->cd_copies
            );

            $orderFile->fill($prices)->save();
            $this->refreshOrderTotals($order);

            if ($path) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحميل الملف بنجاح',
                    'file_id' => $orderFile->id,
                    'order_id' => $order->id,
                    'filename' => $filename,
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

        $data = $request->validate([
            'binding_type' => ['nullable', 'in:tape,wire,normal,thermal,none'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:999'],
            'print_sides' => ['nullable', 'in:one_side,two_sides'],
            'page_size' => ['nullable', 'in:A4,A3,A5,B5'],
            'paper_color' => ['nullable', 'in:white,yellow'],
            'thesis_project_type' => ['nullable', 'in:thesis,supplementary,graduation'],
            'university_name' => ['nullable', 'string', 'max:255'],
            'cover_color' => ['nullable', 'in:black,light_blue,navy,dark_green,light_green,burgundy,beige,white'],
            'writing_color' => ['nullable', 'in:gold,black'],
            'cd_type' => ['nullable', 'in:none,plain,printed'],
            'cd_copies' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        if (array_key_exists('binding_type', $data)) {
            $file->binding_type = $data['binding_type'];
        }

        if (array_key_exists('copies', $data)) {
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
            $file->cd_copies
        );

        $file->fill($prices)->save();
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
        ]);

        $researchTitle = trim($data['research_title']);
        $pages = (int) $data['pages'];
        $prices = $this->calculatePrices('research', $pages, 1, null);

        $order = Order::query()->firstOrCreate([
            'user_id' => Auth::id(),
            'service_type' => 'research',
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
            'message' => 'تم حفظ طلب إنشاء البحث بنجاح',
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

    public function destroyFile(OrderFile $file)
    {
        abort_unless($file->order->user_id === Auth::id() || Auth::user()?->role === 'admin', 403);

        $order = $file->order;

        if ($order->payment_status === 'paid' && Auth::user()?->role !== 'admin') {
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

        $orderDeleted = $order->payment_status !== 'paid' && $order->files()->doesntExist();

        if ($orderDeleted) {
            $order->delete();
        } else {
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

    private function calculatePrices(string $service, int $pages, int $copies, ?string $binding, ?string $writingColor = null, ?string $fileType = null, ?string $paperColor = null, ?string $pageSize = null, ?string $printSides = null, ?string $cdType = 'none', int $cdCopies = 0): array
    {
        $cdCount = $cdType === 'none' ? 0 : max(1, $cdCopies);
        $cdPrice = in_array($service, ['thesis', 'phd'], true) && $fileType === 'pdf'
            ? match ($cdType) {
                'plain' => 5 * $cdCount,
                'printed' => 10 * $cdCount,
                default => 0,
            }
        : 0;

        if (in_array($service, ['thesis', 'phd'], true) && $fileType === 'word') {
            return [
                'print_price' => 0,
                'binding_price' => 0,
                'cd_price' => 0,
                'total_price' => 0,
            ];
        }

        if (in_array($service, ['formatting', 'research'], true)) {
            $servicePrice = $pages * 10;

            return [
                'print_price' => 0,
                'binding_price' => $servicePrice,
                'cd_price' => 0,
                'total_price' => $servicePrice,
            ];
        }

        if ($service === 'color_printing') {
            $sheetCount = max(1, $pages) * max(1, $copies);
            $pageSize = $pageSize ?: 'A4';
            $printPrice = $this->colorPrintingPrice($sheetCount, $pageSize);
            $thermalBindingUnits = $printSides === 'two_sides'
                ? (int) ceil($sheetCount / 2)
                : $sheetCount;
            $bindingPrice = $binding === 'thermal'
                ? $thermalBindingUnits * ($pageSize === 'A3' ? 10 : 5)
                : $this->notesBindingPrice($pages, $binding);

            return [
                'print_price' => $printPrice,
                'binding_price' => $bindingPrice,
                'cd_price' => 0,
                'total_price' => $printPrice + $bindingPrice,
            ];
        }

        if (in_array($service, ['notes', 'books'], true)) {
            $copyCount = max(1, $copies);
            $printPages = max(1, $pages) * $copyCount;

            if ($service === 'books') {
                $printPrice = $paperColor === 'yellow'
                    ? (int) ceil($printPages / 10)
                    : (int) ceil($printPages / 15);
                $bindingPrice = ($pageSize === 'A4' ? 55 : 45) * $copyCount;

                return [
                    'print_price' => $printPrice,
                    'binding_price' => $bindingPrice,
                    'cd_price' => 0,
                    'total_price' => $printPrice + $bindingPrice,
                ];
            }

            $printPrice = $paperColor === 'yellow'
                ? (int) ceil($printPages / 6)
                : (int) ceil($printPages / 12);
            $bindingPrice = $this->notesBindingPrice($pages, $binding) * $copyCount;

            return [
                'print_price' => $printPrice,
                'binding_price' => $bindingPrice,
                'cd_price' => 0,
                'total_price' => $printPrice + $bindingPrice,
            ];
        }

        $copyCount = max(1, $copies);
        $printPrice = $this->printPrice($pages, $copyCount);
        if (! in_array($writingColor, ['gold', 'black'], true)) {
            return [
                'print_price' => $printPrice,
                'binding_price' => 0,
                'cd_price' => $cdPrice,
                'total_price' => $printPrice + $cdPrice,
            ];
        }

        $singleBinding = $writingColor === 'gold' ? 90 : 60;
        $multiBinding = $writingColor === 'gold' ? 75 : 45;
        $bindingPrice = $copyCount === 1 ? $singleBinding : $multiBinding * $copyCount;

        return [
            'print_price' => $printPrice,
            'binding_price' => $bindingPrice,
            'cd_price' => $cdPrice,
            'total_price' => $printPrice + $bindingPrice + $cdPrice,
        ];
    }

    private function printPrice(int $pages, int $copies): int
    {
        return (int) ceil($pages / 15) * max(1, $copies);
    }

    private function colorPrintingPrice(int $sheetCount, string $pageSize): float
    {
        if ($pageSize === 'A3') {
            $unitPrice = match (true) {
                $sheetCount <= 5 => 5,
                $sheetCount <= 10 => 3.5,
                default => 2.5,
            };

            return $sheetCount * $unitPrice;
        }

        $unitPrice = match (true) {
            $sheetCount <= 5 => 2,
            $sheetCount <= 10 => 1.5,
            default => 0.80,
        };

        return $sheetCount * $unitPrice;
    }

    private function notesBindingPrice(int $pages, ?string $binding): int
    {
        if ($binding === 'normal') {
            return 3;
        }

        if ($binding === 'wire') {
            if ($pages < 100) {
                return 5;
            }

            if ($pages < 300) {
                return 7;
            }

            if ($pages <= 600) {
                return 9;
            }

            return 14;
        }

        return 0;
    }

    private function refreshOrderTotals(Order $order): void
    {
        $order->load('files');
        $printTotal = 0;
        if (! in_array($order->service_type, ['formatting', 'research'], true)) {
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
        $bindingTotal = (float) $filesForBinding->sum('binding_price');
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

    private function printProductPrintTotal(Order $order): int
    {
        $whitePages = (int) $order->files
            ->where('file_type', 'pdf')
            ->filter(fn (OrderFile $file) => ($file->paper_color ?: 'white') === 'white')
            ->sum(fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies));
        $yellowPages = (int) $order->files
            ->where('file_type', 'pdf')
            ->filter(fn (OrderFile $file) => $file->paper_color === 'yellow')
            ->sum(fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies));

        $whiteDivisor = $order->service_type === 'notes' ? 12 : 15;
        $whiteTotal = (int) ceil($whitePages / $whiteDivisor);
        $yellowDivisor = $order->service_type === 'books' ? 10 : 6;
        $yellowTotal = (int) ceil($yellowPages / $yellowDivisor);

        return $whiteTotal + $yellowTotal;
    }

    private function deliveryFee(?string $method, float $subtotal): int
    {
        return match ($method) {
            'islamic_university_delivery' => $subtotal >= 35 ? 0 : 5,
            'madinah_delivery' => 20,
            'redbox_delivery' => 30,
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
