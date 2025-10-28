<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RatingMiddleware
{
    /**
     * Limite le nombre de requêtes par utilisateur (exemple: 100 requêtes/heure).
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->user() ? $request->user()->id : $request->ip();
        $key = 'rate_limit:' . $userId;
        $maxAttempts = 100;
        $decayMinutes = 60;

        $attempts = Cache::get($key, 0);
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de requêtes, veuillez patienter.'
            ], 429);
        }
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        return $next($request);
    }
}
