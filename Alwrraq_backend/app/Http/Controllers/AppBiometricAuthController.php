<?php

namespace App\Http\Controllers;

use App\Models\BiometricLoginToken;
use App\Services\GuestCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AppBiometricAuthController extends Controller
{
    public function issue(Request $request): JsonResponse
    {
        abort_unless($request->session()->get('auth_surface') === 'app', 403);

        $data = $request->validate([
            'device_id' => ['required', 'string', 'min:16', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'in:android,ios'],
        ]);

        $plainToken = Str::random(96);
        $token = $request->user()->biometricLoginTokens()->updateOrCreate(
            ['device_id' => $data['device_id']],
            [
                'device_name' => $data['device_name'] ?? null,
                'platform' => $data['platform'] ?? null,
                'token_hash' => hash('sha256', $plainToken),
                'last_used_at' => null,
                'expires_at' => now()->addDays(90),
            ],
        );

        return response()->json([
            'token' => $plainToken,
            'expires_at' => $token->expires_at->toIso8601String(),
            'user_name' => $request->user()->name,
        ])->header('Cache-Control', 'no-store');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:96', 'regex:/^[A-Za-z0-9]+$/'],
            'device_id' => ['required', 'string', 'min:16', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $loginToken = BiometricLoginToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $data['token']))
            ->where('device_id', $data['device_id'])
            ->where('expires_at', '>', now())
            ->first();

        if (! $loginToken || ! $loginToken->user || ! $loginToken->user->canLogin()) {
            return redirect()->route('app.login')->withErrors([
                'login_identifier' => 'انتهت صلاحية الدخول بالبصمة. سجّل الدخول بكلمة المرور ثم فعّلها من جديد.',
            ]);
        }

        Auth::login($loginToken->user, true);
        $request->session()->regenerate();
        $request->session()->put('auth_surface', 'app');
        $request->session()->forget('url.intended');
        $loginToken->forceFill(['last_used_at' => now()])->save();

        app(GuestCartService::class)->claim($request, $loginToken->user);

        return redirect()->route('home');
    }

    public function revoke(Request $request): JsonResponse
    {
        abort_unless($request->session()->get('auth_surface') === 'app', 403);

        $data = $request->validate([
            'device_id' => ['required', 'string', 'min:16', 'max:128'],
        ]);

        $request->user()->biometricLoginTokens()
            ->where('device_id', $data['device_id'])
            ->delete();

        return response()->json(['success' => true])->header('Cache-Control', 'no-store');
    }
}
