<?php

namespace App\Http\Controllers;

use App\Models\ServicePriceSetting;
use App\Services\ServicePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminServicePricingController extends Controller
{
    public function index(ServicePricingService $pricing)
    {
        $this->authorizePricing();

        $lastUpdate = ServicePriceSetting::query()->with('updater')->latest('updated_at')->first();

        return view('admin.service-pricing', [
            'priceGroups' => $pricing->groupedDefinitions(),
            'lastUpdate' => $lastUpdate,
        ]);
    }

    public function update(Request $request, ServicePricingService $pricing)
    {
        $this->authorizePricing();
        $data = $request->validate($pricing->validationRules());
        $pricing->update($data['prices'], (int) Auth::id());

        return back()->with('status', 'تم حفظ أسعار الخدمات وتطبيقها على الحسابات الجديدة بنجاح.');
    }

    private function authorizePricing(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        abort_unless(Auth::user()?->hasAdminPermission('service_prices_update'), 403);
    }
}
