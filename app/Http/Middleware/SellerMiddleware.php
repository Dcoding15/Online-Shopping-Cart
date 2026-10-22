<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     * Only users with user_type 'seller' (or 'admin') may proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || (! $request->user()->isSeller() && ! $request->user()->isAdmin())) {
            abort(403, 'Unauthorized. Seller access required.');
        }

        return $next($request);
    }
}
