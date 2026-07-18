<?php

namespace App\Http\Controllers;

use App\Models\StationeryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminStationeryProductController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin();
        $search = trim((string) $request->query('q', ''));
        $products = StationeryProduct::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('product_type', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->get();

        return view('admin.stationery-products', compact('products', 'search'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $data = $this->validated($request, true);
        $data['image_path'] = $request->file('image')->store('stationery-products', 'public');
        $data['is_active'] = $request->boolean('is_active');
        StationeryProduct::create($data);

        return back()->with('status', 'تمت إضافة المنتج بنجاح.');
    }

    public function update(Request $request, StationeryProduct $product)
    {
        $this->ensureAdmin();
        $data = $this->validated($request, false);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('stationery-products', 'public');
        }

        $product->update($data);

        return back()->with('status', 'تم تعديل المنتج بنجاح.');
    }

    public function destroy(StationeryProduct $product)
    {
        $this->ensureAdmin();
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return back()->with('status', 'تم حذف المنتج بنجاح.');
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'company_name' => ['required', 'string', 'max:180'],
            'product_type' => ['required', 'string', 'max:180'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }
}
