<?php

namespace App\Http\Controllers;

use App\Services\LivePageUpdateService;
use App\Services\Payments\MoyasarPaymentService;
use Illuminate\Http\Request;

class LivePageUpdateController extends Controller
{
    public function __invoke(
        Request $request,
        LivePageUpdateService $liveUpdates,
        MoyasarPaymentService $moyasar
    ) {
        $moyasar->reconcilePendingAttempts($request->user());

        return response()
            ->json($liveUpdates->snapshot($request->user()))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
