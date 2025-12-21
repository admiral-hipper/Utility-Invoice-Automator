<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $ability)
    {
        $token = $request->user()?->currentAccessToken();
        if (!$token || !$token->can($ability)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
