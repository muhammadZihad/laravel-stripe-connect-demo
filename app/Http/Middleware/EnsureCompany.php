<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isCompany()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Company access required.'], 403);
            }
            abort(403, 'Unauthorized. Company access required.');
        }

        return $next($request);
    }
}
