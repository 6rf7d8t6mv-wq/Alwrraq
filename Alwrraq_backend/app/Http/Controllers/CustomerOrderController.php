<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDeliveredFile;
use App\Models\OrderFile;
use App\Services\Payments\MoyasarPaymentService;
use App\Services\WordPreviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class CustomerOrderController extends Controller
{
    public function index(MoyasarPaymentService $moyasar)
    {
        $moyasar->reconcilePendingAttempts(Auth::user());

        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->whereIn('payment_status', ['paid', 'voided', 'refunded'])
            ->with(['files', 'productItems', 'deliveredFiles', 'serviceDefinition'])
            ->withCount(['files', 'productItems'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function invoice(Order $order)
    {
        $this->authorizeInvoice($order);

        return view('orders.invoice', compact('order'));
    }

    public function destroy(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if ($order->payment_status !== 'unpaid') {
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
                ? 'طلب إنشاء البحوث لا يحتوي على ملف مرفوع للعرض.'
                : 'الملف غير موجود في التخزين، لذلك تعذر عرضه.';

            if (request('from') === 'upload') {
                return redirect()->route('home', [
                    'service' => $order->service_type,
                    'order' => $order->id,
                ])->withErrors(['file' => $message]);
            }

            if (request('from') === 'cart') {
                return redirect()->route('cart.index')->withErrors(['file' => $message]);
            }

            return redirect()
                ->route('orders.index', ['open_order' => $order->id])
                ->withErrors(['file' => $message]);
        }

        if (request()->boolean('download')) {
            return Response::download($absolutePath, $file->original_name);
        }

        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        $detectedMime = File::mimeType($absolutePath) ?: 'application/octet-stream';

        if (request()->boolean('raw')) {
            $inlineMime = $extension === 'pdf'
                ? 'application/pdf'
                : $detectedMime;

            return response()->file($absolutePath, [
                'Content-Type' => $inlineMime,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ]);
        }

        $isPdf = $extension === 'pdf'
            || strtolower($file->file_type) === 'pdf'
            || $detectedMime === 'application/pdf';
        $isImage = strtolower($file->file_type) === 'image'
            || str_starts_with($detectedMime, 'image/');
        $isWord = in_array($extension, ['doc', 'docx'], true)
            || strtolower($file->file_type) === 'word'
            || in_array($detectedMime, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ], true);
        $wordPreviewHtml = $isWord && $extension === 'docx'
            ? $wordPreview->toHtml($absolutePath)
            : null;
        $isPreviewable = $isPdf || $isImage || filled($wordPreviewHtml);

        return response()
            ->view('orders.file-viewer', compact('order', 'file', 'isPreviewable', 'isPdf', 'isImage', 'wordPreviewHtml'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function downloadDeliveredFile(Order $order, OrderDeliveredFile $deliveredFile, WordPreviewService $wordPreview)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless(in_array($order->service_type, ['formatting', 'research'], true), 404);
        abort_unless($deliveredFile->order_id === $order->id, 404);

        $absolutePath = storage_path('app/'.$deliveredFile->path);

        abort_unless(File::isFile($absolutePath), 404);

        if (request()->boolean('raw') || request()->routeIs('orders.delivered-file.raw')) {
            $inlineMime = strtolower(pathinfo($deliveredFile->original_name, PATHINFO_EXTENSION)) === 'pdf'
                ? 'application/pdf'
                : ($deliveredFile->mime ?: File::mimeType($absolutePath) ?: 'application/octet-stream');

            return response()->file($absolutePath, [
                'Content-Type' => $inlineMime,
            ]);
        }

        if (request()->boolean('view') || request()->routeIs('orders.delivered-file.view')) {
            $extension = strtolower(pathinfo($deliveredFile->original_name, PATHINFO_EXTENSION));
            $isPdf = $extension === 'pdf' || $deliveredFile->mime === 'application/pdf';
            $isWord = in_array($extension, ['docx', 'doc'], true)
                || in_array($deliveredFile->mime, [
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ], true);
            $wordPreviewHtml = $isWord && $extension === 'docx'
                ? $wordPreview->toHtml($absolutePath)
                : null;
            $backUrl = route('orders.index').'#order-'.$order->id;
            $rawUrl = route('orders.delivered-file.raw', [$order, $deliveredFile]);
            $downloadUrl = route('orders.delivered-file', [
                'order' => $order,
                'deliveredFile' => $deliveredFile,
                'download' => 1,
                'filename' => $deliveredFile->original_name,
            ]);

            return response()
                ->view('orders.delivered-file-viewer', compact(
                    'order',
                    'deliveredFile',
                    'isPdf',
                    'wordPreviewHtml',
                    'backUrl',
                    'rawUrl',
                    'downloadUrl'
                ))
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
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

    private function authorizeInvoice(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless($order->payment_status === 'paid', 404);

        $order->load(['user', 'files', 'productItems']);
    }
}
