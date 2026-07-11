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

            if (!in_array($service, ['notes', 'thesis', 'phd'], true)) {
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
                'binding_type' => null,
                'print_price' => 0,
                'binding_price' => 0,
                'total_price' => 0,
            ]);

            $prices = $this->calculatePrices(
                $service,
                $pageCount,
                $orderFile->copies,
                $orderFile->binding_type
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

        $prices = $this->calculatePrices(
            $file->order->service_type,
            $file->pages,
            $file->copies,
            $file->binding_type
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
            'order_totals' => $this->orderTotalsPayload($file->order->fresh()),
        ]);
    }

    public function destroyFile(OrderFile $file)
    {
        abort_unless($file->order->user_id === Auth::id() || Auth::user()?->role === 'admin', 403);

        $order = $file->order;
        $absolutePath = storage_path('app/' . $file->path);

        $file->delete();

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }

        $this->refreshOrderTotals($order);

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

    private function calculatePrices(string $service, int $pages, int $copies, ?string $binding): array
    {
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
        $singleBinding = $service === 'phd' ? 90 : 70;
        $multiBinding = $service === 'phd' ? 70 : 55;
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
        $printUnits = $order->files->sum(
            fn (OrderFile $file) => $file->pages * max(1, (int) $file->copies)
        );
        $printTotal = $this->printPrice((int) $printUnits, 1);
        $bindingTotal = (int) $order->files->sum('binding_price');

        $order->update([
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'grand_total' => $printTotal + $bindingTotal,
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
