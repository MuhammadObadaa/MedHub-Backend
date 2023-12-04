<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth; for auth
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    //search for Sanctum package
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // add this method Auth::user();
        $user = User::where('remember_token', request()->header('token'))->first();
        if (!$user)
            $user = User::where('remember_token', request()->cookie('token'))->first();
        if (!$user)
            return response()->json(['message' => 'Not Authorized!'], 401);

        return $next($request);
    }

    public static function getUser(): User
    {
        $user = User::where('remember_token', request()->header('token'))->first();
        if (!$user)
            $user = User::where('remember_token', request()->cookie('token'))->first();

        //there is no need to check if the $user is null due to middleware check

        return $user;
    }
}
