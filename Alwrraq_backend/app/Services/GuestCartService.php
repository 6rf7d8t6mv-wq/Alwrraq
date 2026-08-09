<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestCartService
{
    private const SESSION_KEY = 'guest_cart_token';

    public function token(Request $request): string
    {
        $token = (string) $request->session()->get(self::SESSION_KEY, '');

        if ($token === '') {
            $token = (string) Str::uuid();
            $request->session()->put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public function orderIdentity(Request $request): array
    {
        if ($request->user()) {
            return ['user_id' => $request->user()->id, 'guest_token' => null];
        }

        return ['user_id' => null, 'guest_token' => $this->token($request)];
    }

    public function scopeOwned(Builder $query, Request $request): Builder
    {
        if ($request->user()) {
            return $query->where('user_id', $request->user()->id);
        }

        return $query->whereNull('user_id')->where('guest_token', $this->token($request));
    }

    public function owns(Request $request, Order $order): bool
    {
        if ($request->user()) {
            return (int) $order->user_id === (int) $request->user()->id;
        }

        return $order->user_id === null
            && filled($order->guest_token)
            && hash_equals((string) $order->guest_token, $this->token($request));
    }

    public function claim(Request $request, User $user): void
    {
        $token = (string) $request->session()->get(self::SESSION_KEY, '');
        if ($token === '') {
            return;
        }

        Order::query()
            ->whereNull('user_id')
            ->where('guest_token', $token)
            ->where('payment_status', 'unpaid')
            ->update(['user_id' => $user->id, 'guest_token' => null]);

        $request->session()->forget(self::SESSION_KEY);
    }
}
