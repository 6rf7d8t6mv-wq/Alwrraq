<?php

namespace App\Http\Controllers;

use App\Models\ServicePriceSetting;
use App\Models\ServiceDefinition;
use App\Services\ServicePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminServicePricingController extends Controller
{
    public function index(ServicePricingService $pricing)
    {
        $this->authorizePricing();

        $lastUpdate = ServicePriceSetting::query()->with('updater')->latest('updated_at')->first();
        $customServices = ServiceDefinition::query()
            ->where('is_system', false)
            ->where('workflow_type', '!=', 'images')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.service-pricing', [
            'priceGroups' => $pricing->groupedDefinitions(),
            'lastUpdate' => $lastUpdate,
            'customServices' => $customServices,
            'customServicePrices' => $pricing->customServicePrices($customServices),
        ]);
    }

    public function update(Request $request, ServicePricingService $pricing)
    {
        $this->authorizePricing();
        $customServiceIds = ServiceDefinition::query()
            ->where('is_system', false)
            ->where('workflow_type', '!=', 'images')
            ->pluck('id');
        $rules = $pricing->validationRules();
        foreach ($customServiceIds as $serviceId) {
            $rules["service_prices.{$serviceId}"] = ['required', 'numeric', 'min:0.01', 'max:1000000'];
        }

        $data = $request->validate($rules);
        $userId = (int) Auth::id();
        $pricing->update($data['prices'], $userId);
        $pricing->updateCustomServicePrices($data['service_prices'] ?? [], $userId);

        return back()->with('status', 'تم حفظ أسعار الخدمات وتطبيقها على الحسابات الجديدة بنجاح.');
    }

    private function authorizePricing(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        abort_unless(Auth::user()?->hasAdminPermission('service_prices_update'), 403);
    }
}
