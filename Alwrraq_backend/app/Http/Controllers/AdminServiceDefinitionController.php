<?php

namespace App\Http\Controllers;

use App\Models\ServiceDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminServiceDefinitionController extends Controller
{
    public function index()
    {
        $this->ensureAdmin();

        return view('admin.services', [
            'services' => ServiceDefinition::query()->orderBy('sort_order')->orderBy('id')->get(),
            'workflows' => ServiceDefinition::WORKFLOWS,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $data = $this->validated($request);
        $data['code'] = $this->uniqueCode($data['title']);
        $data['requires_file'] = $this->workflowRequiresFile($data['workflow_type']);
        $data['is_active'] = true;
        $data['is_system'] = false;
        $data['sort_order'] = ((int) ServiceDefinition::query()->max('sort_order')) + 10;

        ServiceDefinition::query()->create($data);

        return back()->with('status', 'تمت إضافة الخدمة بنجاح.');
    }

    public function update(Request $request, ServiceDefinition $service)
    {
        $this->ensureAdmin();
        $data = $this->validated($request, $service);
        $data['requires_file'] = $this->workflowRequiresFile($data['workflow_type']);
        $service->update($data);

        return back()->with('status', 'تم تعديل الخدمة بنجاح.');
    }

    private function validated(Request $request, ?ServiceDefinition $service = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:20'],
            'workflow_type' => [
                'required',
                'string',
                Rule::in(array_keys(ServiceDefinition::WORKFLOWS)),
            ],
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
}
