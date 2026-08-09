<?php

namespace App\Http\Controllers;

use App\Models\ServiceDefinition;
use App\Services\ServicePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminServiceDefinitionController extends Controller
{
    public function image(string $filename)
    {
        abort_unless($filename === basename($filename), 404);

        $path = 'service-images/'.$filename;
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function index()
    {
        $this->ensureAdmin();
        $this->ensureAnyPermission(['services_view', 'services_create', 'services_update']);

        return view('admin.services', [
            'services' => ServiceDefinition::query()->orderBy('sort_order')->orderBy('id')->get(),
            'workflows' => ServiceDefinition::WORKFLOWS,
        ]);
    }

    public function store(Request $request, ServicePricingService $pricing)
    {
        $this->ensureAdmin();
        $this->ensurePermission('services_create');
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['code'] = $this->uniqueCode($data['title']);
        $data['requires_file'] = $this->workflowRequiresFile($data['workflow_type']);
        $data['requires_delivery'] = $request->boolean('requires_delivery');
        $data['is_active'] = true;
        $data['is_system'] = false;
        $data['sort_order'] = ((int) ServiceDefinition::query()->max('sort_order')) + 10;
        if ($image) {
            $data['image_path'] = $image->store('service-images', 'public');
        }

        try {
            $service = ServiceDefinition::query()->create($data);
        } catch (\Throwable $error) {
            if (isset($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }

            throw $error;
        }
        $pricing->ensureCustomServicePrice($service, (int) Auth::id());

        return back()->with('status', 'تمت إضافة الخدمة وتفعيل سعرها الافتراضي بنجاح.');
    }

    public function update(Request $request, ServiceDefinition $service)
    {
        $this->ensureAdmin();
        $this->ensurePermission('services_update');
        $data = $this->validated($request, $service);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['requires_file'] = $this->workflowRequiresFile($data['workflow_type']);
        $data['requires_delivery'] = $request->boolean('requires_delivery');
        $previousImagePath = $service->image_path;
        if ($image) {
            $data['image_path'] = $image->store('service-images', 'public');
        }

        try {
            $service->update($data);
        } catch (\Throwable $error) {
            if (isset($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }

            throw $error;
        }

        if ($image && $previousImagePath) {
            Storage::disk('public')->delete($previousImagePath);
        }

        return back()->with('status', 'تم تعديل الخدمة بنجاح.');
    }

    private function validated(Request $request, ?ServiceDefinition $service = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,heic,heif', 'max:10240'],
            'workflow_type' => [
                'required',
                'string',
                Rule::in(array_keys(ServiceDefinition::WORKFLOWS)),
            ],
            'requires_delivery' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueCode(string $title): string
    {
        $base = Str::slug($title) ?: 'service';
        $code = $base;
        $suffix = 2;

        while (ServiceDefinition::query()->where('code', $code)->exists()) {
            $code = $base.'-'.$suffix;
            $suffix++;
        }

        return $code;
    }

    private function workflowRequiresFile(string $workflow): bool
    {
        return ! in_array($workflow, ['research', 'stationery'], true);
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasAdminPermission($permission), 403);
    }

    private function ensureAnyPermission(array $permissions): void
    {
        abort_unless(auth()->user()?->hasAnyAdminPermission($permissions), 403);
    }
}
