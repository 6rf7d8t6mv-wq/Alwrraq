<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CustomerOrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->with('files')
            ->withCount('files')
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

        $order->load('files');

        foreach ($order->files as $file) {
            $absolutePath = storage_path('app/' . $file->path);

            if (File::isFile($absolutePath)) {
                File::delete($absolutePath);
            }
        }

        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('status', 'تم حذف الطلب وجميع ملفاته بنجاح.');
    }
}
