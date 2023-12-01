<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //echo "authenticated";
        //TODO: add Auth::user
        $user = User::where('remember_token', request('token'))->first();
        if (!$user)
            $user = User::where('remember_token', request()->cookie('token'))->first();
        if (!$user)
            return response()->json(['message' => 'Not Authenticated!'], 400);

        return $next($request);
    }
}
