<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. التوكن غير موجود.',
            ], 401);
        }

        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. التوكن غير صحيح.',
            ], 401);
        }

        $user = User::query()->find($userId);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. المستخدم غير موجود.',
            ], 401);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
