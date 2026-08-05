<?php

namespace App\Http\Middleware;

use App\Services\Payments\MoyasarPaymentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReconcileMoyasarPayments
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Reconcile after the response is sent, so payment recovery never delays a page.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (! $request->isMethod('GET')
            || $request->expectsJson()
            || $request->ajax()
            || $request->is('live-status', 'admin/live-status', 'app-revision', 'chat/*', 'language/*')) {
            return;
        }

        try {
            app(MoyasarPaymentService::class)->reconcilePendingAttempts();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
