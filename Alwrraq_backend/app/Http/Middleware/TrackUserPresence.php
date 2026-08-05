<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserPresence
{
    private const HEARTBEAT_INTERVAL_SECONDS = 15;

    public function handle(Request $request, Closure $next): Response
    {
        $this->touch($request->user());

        return $next($request);
    }

    public static function touch($user): void
    {
        // Some isolated tests create a minimal users table. Checking the loaded
        // attributes also keeps the middleware safe until the migration is run.
        if (! $user || ! array_key_exists('last_seen_at', $user->getAttributes())) {
            return;
        }

        if ($user->last_seen_at?->gte(now()->subSeconds(self::HEARTBEAT_INTERVAL_SECONDS))) {
            return;
        }

        $seenAt = now();
        $user->newQuery()->whereKey($user->getKey())->update(['last_seen_at' => $seenAt]);
        $user->setAttribute('last_seen_at', $seenAt);
    }
}
