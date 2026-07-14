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
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تحديد ملف'
                ], 400);
            }

            $file = $request->file('file');
            $type = $request->input('type', 'unknown');
            $service = $request->input('service', 'notes');

            if (!in_array($service, ['notes', 'thesis', 'phd', 'formatting', 'research'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الخدمة غير معروف'
                ], 400);
            }

            // Validate file is not corrupted
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الملف غير صحيح أو تالف'
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
                    'message' => 'نوع الملف غير معروف'
                ], 400);
            }

            // Check MIME type
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة الملف غير مدعومة'
                ], 400);
            }

            // Check file extension
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة الملف غير صحيحة'
                ], 400);
            }

            // Create storage path if it doesn't exist
            $storagePath = 'uploads/' . $type . 's';
            $fullPath = storage_path('app/' . $storagePath);
            
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            // Generate unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $timestamp = now()->timestamp;
            $filename = $originalName . '_' . $timestamp . '.' . $extension;
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
            $fullStoragePath = storage_path('app/' . $storagePath);
            $file->move($fullStoragePath, $filename);
            
            $path = $storagePath . '/' . $filename;

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

            $orderFile = OrderFile::query()->create([
                'order_id' => $order->id,
                'file_type' => $type,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $filename,
                'path' => $path,
                'size' => $fileSize,
                'pages' => $pageCount,
                'copies' => 1,
                'thesis_project_type' => null,
                'university_name' => null,
                'cover_color' => null,
                'writing_color' => null,
                'binding_type' => null,
                'print_price' => 0,
                'binding_price' => 0,
                'total_price' => 0,
            ]);

            $prices = $this->calculatePrices(
                $service,
                $pageCount,
                $orderFile->copies,
                $orderFile->binding_type,
                $orderFile->writing_color
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
                    'binding_type' => $orderFile->binding_type,
                    'university_name' => $orderFile->university_name,
                    'cover_color' => $orderFile->cover_color,
                    'writing_color' => $orderFile->writing_color,
                    'print_price' => $orderFile->print_price,
                    'binding_price' => $orderFile->binding_price,
                    'total_price' => $orderFile->total_price,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل حفظ الملف'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateFile(Request $request, OrderFile $file)
    {
        abort_unless($file->order->user_id === Auth::id() || Auth::user()?->role === 'admin', 403);

        $data = $request->validate([
            'binding_type' => ['nullable', 'in:tape,wire,normal,none'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:999'],
            'thesis_project_type' => ['nullable', 'in:thesis,supplementary,graduation'],
            'university_name' => ['nullable', 'string', 'max:255'],
            'cover_color' => ['nullable', 'in:black,light_blue,navy,dark_green,light_green,burgundy,beige,white'],
            'writing_color' => ['nullable', 'in:gold,black'],
        ]);

        if (array_key_exists('binding_type', $data)) {
            $file->binding_type = $data['binding_type'];
        }

        if (array_key_exists('copies', $data)) {
            $file->copies = $data['copies'];
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

        if (
            in_array($file->order->service_type, ['thesis', 'phd'], true)
            && $file->writing_color === 'black'
            && !in_array($file->cover_color, ['beige', 'light_blue', 'light_green', 'white'], true)
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
            $file->writing_color
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
            'cover_color' => $file->cover_color,
            'writing_color' => $file->writing_color,
            'order_totals' => $this->orderTotalsPayload($file->order->fresh()),
        ]);
    }

    public function saveResearchOrder(Request $request)
    {
        $data = $request->validate([
            'research_title' => ['required', 'string', 'max:255'],
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
            'stored_name' => 'research-request-' . $order->id,
            'path' => 'research-request',
            'size' => 0,
            'pages' => $pages,
            'copies' => 1,
            'thesis_project_type' => null,
            'university_name' => null,
            'research_title' => $researchTitle,
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

        $absolutePath = storage_path('app/' . $file->path);

        $file->delete();

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }

        $this->refreshOrderTotals($order);

        if (!request()->expectsJson()) {
            return back()->with('status', 'تم حذف الملف من الطلب بنجاح.');
        }

        return response()->json([
            'success' => true,
        ]);
    }

    private function countPDFPages($filePath)
    {
        try {
            $content = file_get_contents($filePath);
            $pageMatches = preg_match_all('/\/Type\s*\/Page[^s]/i', $content);
            return max(1, $pageMatches);
        } catch (\Exception $e) {
            return 1;
        }
    }

    private function countWordPages($filePath)
    {
        try {
            // DOCX is a ZIP file
            $zip = new \ZipArchive();
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

    private function calculatePrices(string $service, int $pages, int $copies, ?string $binding, ?string $writingColor = null): array
    {
        if (in_array($service, ['formatting', 'research'], true)) {
            $servicePrice = $pages * 10;

            return [
                'print_price' => 0,
                'binding_price' => $servicePrice,
                'total_price' => $servicePrice,
            ];
        }

        if ($service === 'notes') {
            $printPrice = $this->printPrice($pages, 1);
            $bindingPrice = 0;

            if ($binding === 'normal') {
                $bindingPrice = 3;
            } elseif ($binding === 'wire') {
                if ($pages < 100) {
                    $bindingPrice = 5;
                } elseif ($pages < 300) {
                    $bindingPrice = 7;
                } elseif ($pages <= 600) {
                    $bindingPrice = 9;
                } else {
                    $bindingPrice = 14;
                }
            }

            return [
                'print_price' => $printPrice,
                'binding_price' => $bindingPrice,
                'total_price' => $printPrice + $bindingPrice,
            ];
        }

        $copyCount = max(1, $copies);
        $printPrice = $this->printPrice($pages, $copyCount);
        if (!in_array($writingColor, ['gold', 'black'], true)) {
            return [
                'print_price' => $printPrice,
                'binding_price' => 0,
                'total_price' => $printPrice,
            ];
        }

        $singleBinding = $writingColor === 'gold' ? 90 : 60;
        $multiBinding = $writingColor === 'gold' ? 75 : 45;
        $bindingPrice = $copyCount === 1 ? $singleBinding : $multiBinding * $copyCount;

        return [
            'print_price' => $printPrice,
            'binding_price' => $bindingPrice,
            'total_price' => $printPrice + $bindingPrice,
        ];
    }

    private function printPrice(int $pages, int $copies): int
    {
        return (int) ceil($pages / 15) * max(1, $copies);
    }

    private function refreshOrderTotals(Order $order): void
    {
        $order->load('files');
        $printTotal = 0;
        if (!in_array($order->service_type, ['formatting', 'research'], true)) {
            $printUnits = $order->files->sum(
                fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies)
            );
            $printTotal = $this->printPrice((int) $printUnits, 1);
        }
        $bindingTotal = (int) $order->files->sum('binding_price');
        $baseTotal = $printTotal + $bindingTotal;
        $discountAmount = min((int) $order->discount_amount, $baseTotal);

        $order->update([
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'discount_amount' => $discountAmount,
            'grand_total' => max(0, $baseTotal - $discountAmount),
        ]);
    }

    private function orderTotalsPayload(Order $order): array
    {
        return [
            'print_total' => $order->print_total,
            'binding_total' => $order->binding_total,
            'grand_total' => $order->grand_total,
        ];
    }
}
