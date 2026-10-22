<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BuyerMiddleware
{
    /**
     * Handle an incoming request.
     * Only users with user_type 'buyer' may proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isBuyer()) {
            abort(403, 'Unauthorized. Buyer access required.');
        }

        return $next($request);
    }
}
