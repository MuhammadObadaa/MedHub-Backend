<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //dd(AuthMiddleware::getUser());
        if (!AuthMiddleware::getUser()->isAdmin())
            return response()->json(['message' => 'Admin Route'], 400);

        return $next($request);
    }
}
