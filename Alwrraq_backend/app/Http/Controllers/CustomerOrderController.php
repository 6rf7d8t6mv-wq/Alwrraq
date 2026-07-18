<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDeliveredFile;
use App\Models\OrderFile;
use App\Services\WordPreviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class CustomerOrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->with(['files', 'productItems', 'deliveredFiles'])
            ->withCount(['files', 'productItems'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function destroy(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if ($order->payment_status === 'paid') {
            return back()->withErrors([
                'order' => 'لا يمكن حذف الطلب بعد إتمام الدفع.',
            ]);
        }

        $order->load(['files', 'deliveredFiles']);

        foreach ($order->files as $file) {
            $absolutePath = storage_path('app/'.$file->path);

            if (File::isFile($absolutePath)) {
                File::delete($absolutePath);
            }
        }

        foreach ($order->deliveredFiles as $deliveredFile) {
            $deliveredPath = storage_path('app/'.$deliveredFile->path);
            if (File::isFile($deliveredPath)) {
                File::delete($deliveredPath);
            }
        }

        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('status', 'تم حذف الطلب وجميع ملفاته بنجاح.');
    }

    public function viewUploadedFile(Order $order, OrderFile $file, WordPreviewService $wordPreview)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless($file->order_id === $order->id, 404);

        $absolutePath = storage_path('app/'.$file->path);

        if (! File::isFile($absolutePath)) {
            $message = $order->service_type === 'research'
                ? 'طلب إنشاء البحث لا يحتوي على ملف مرفوع للعرض.'
                : 'الملف غير موجود في التخزين، لذلك تعذر عرضه.';

            if (request('from') === 'cart') {
                return redirect()->route('cart.index')->withErrors(['file' => $message]);
            }

            return redirect()
                ->route('orders.index', ['open_order' => $order->id])
                ->withErrors(['file' => $message]);
        }

        if (request()->boolean('raw')) {
            return response()->file($absolutePath, [
                'Content-Type' => File::mimeType($absolutePath) ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.addslashes($file->original_name).'"',
            ]);
        }

        $isPdf = strtolower($file->file_type) === 'pdf';
        $wordPreviewHtml = strtolower($file->file_type) === 'word'
            ? $wordPreview->toHtml($absolutePath)
            : null;
        $isPreviewable = $isPdf || filled($wordPreviewHtml);

        return response()
            ->view('orders.file-viewer', compact('order', 'file', 'isPreviewable', 'isPdf', 'wordPreviewHtml'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function downloadDeliveredFile(Order $order, OrderDeliveredFile $deliveredFile)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless(in_array($order->service_type, ['formatting', 'research'], true), 404);
        abort_unless($deliveredFile->order_id === $order->id, 404);

        $absolutePath = storage_path('app/'.$deliveredFile->path);

        abort_unless(File::isFile($absolutePath), 404);

        if (request()->boolean('view')) {
            return response()->file($absolutePath, [
                'Content-Type' => $deliveredFile->mime ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.addslashes($deliveredFile->original_name).'"',
            ]);
        }

        if (blank($deliveredFile->customer_downloaded_at)) {
            $deliveredFile->update(['customer_downloaded_at' => now()]);
        }

        $hasPendingDeliveredFiles = $order->deliveredFiles()
            ->whereNull('customer_downloaded_at')
            ->exists();

        if (! $hasPendingDeliveredFiles) {
            $order->update(['customer_notification_seen_at' => now()]);
        }

        return Response::download($absolutePath, $deliveredFile->original_name);
    }
}
