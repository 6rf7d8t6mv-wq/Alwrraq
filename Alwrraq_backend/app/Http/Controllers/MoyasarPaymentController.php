<?php

namespace App\Http\Controllers;

use App\Models\MoyasarPaymentAttempt;
use App\Services\Payments\MoyasarPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class MoyasarPaymentController extends Controller
{
    public function remember(
        Request $request,
        MoyasarPaymentAttempt $attempt,
        MoyasarPaymentService $moyasar
    ): JsonResponse {
        abort_unless((int) Auth::id() === (int) $attempt->user_id, 404);

        $payment = $request->validate([
            'id' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
            'source' => ['nullable', 'array'],
            'source.type' => ['nullable', 'string', 'max:30'],
            'source.company' => ['nullable', 'string', 'max:30'],
        ]);

        $moyasar->rememberCreatedPayment($attempt, $payment);

        try {
            $paid = $moyasar->verifyAndComplete(
                $attempt,
                $moyasar->fetchPayment($payment['id'])
            );
        } catch (Throwable $exception) {
            Log::warning('Moyasar completed payment could not be verified immediately.', [
                'attempt_id' => $attempt->id,
                'exception' => $exception::class,
            ]);

            return response()->json([
                'saved' => true,
                'paid' => false,
            ], 202);
        }

        return response()->json([
            'saved' => true,
            'paid' => $paid,
            'redirect_url' => $paid ? route('orders.index') : null,
        ]);
    }

    public function callback(
        Request $request,
        MoyasarPaymentAttempt $attempt,
        MoyasarPaymentService $moyasar
    ): RedirectResponse {
        $paymentId = trim((string) $request->query('id'));
        if ($paymentId === '') {
            return $this->resultRedirect(false, 'لم يصل رقم عملية الدفع من ميسر.');
        }

        try {
            $paid = $moyasar->verifyAndComplete($attempt, $moyasar->fetchPayment($paymentId));
        } catch (Throwable $exception) {
            Log::error('Moyasar callback verification failed.', [
                'attempt_id' => $attempt->id,
                'exception' => $exception::class,
            ]);

            return $this->resultRedirect(false, 'تعذر التحقق من عملية الدفع. لم يتم اعتماد الطلب كمدفوع.');
        }

        return $paid
            ? $this->resultRedirect(true, 'تم الدفع عبر ميسر واعتماد الطلب بنجاح.')
            : $this->resultRedirect(false, 'لم تكتمل عملية الدفع في ميسر.');
    }

    public function webhook(Request $request, MoyasarPaymentService $moyasar): JsonResponse
    {
        $expectedSecret = (string) config('payments.moyasar.webhook_secret');
        $expectedSecretHash = (string) config('payments.moyasar.webhook_secret_hash');
        $providedSecret = (string) $request->input('secret_token');

        $matchesPlainSecret = $expectedSecret !== ''
            && hash_equals($expectedSecret, $providedSecret);
        $matchesSecretHash = $expectedSecretHash !== ''
            && hash_equals($expectedSecretHash, hash('sha256', $providedSecret));

        if (! $matchesPlainSecret && ! $matchesSecretHash) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $paymentId = trim((string) $request->input('data.id'));
        $reference = trim((string) $request->input('data.metadata.attempt_reference'));
        if ($paymentId === '' || $reference === '') {
            return response()->json(['received' => true]);
        }

        $attempt = MoyasarPaymentAttempt::query()->where('reference', $reference)->first();
        if (! $attempt) {
            return response()->json(['received' => true]);
        }

        try {
            $moyasar->verifyAndComplete($attempt, $moyasar->fetchPayment($paymentId));
        } catch (Throwable $exception) {
            Log::error('Moyasar webhook verification failed.', [
                'attempt_id' => $attempt->id,
                'event_id' => $request->input('id'),
                'exception' => $exception::class,
            ]);

            return response()->json(['message' => 'Verification failed'], 500);
        }

        return response()->json(['received' => true]);
    }

    private function resultRedirect(bool $success, string $message): RedirectResponse
    {
        $route = Auth::check() ? 'orders.index' : 'login';

        return $success
            ? redirect()->route($route)->with('status', $message)
            : redirect()->route($route)->withErrors(['payment' => $message]);
    }
}
