<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
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
        // dump(Cookie::get('token'));
        // dump(request()->cookie('token'));
        // dump(Crypt::decrypt(request()->cookie('token'), false));
        /*
            both Cookie::get() and request()->cookie() return decrypted data when dump it outside login function
            but otherwise they return encrypted data like middleware or userController
            excepting 'token' cookie from encrypting as an unStandard solution.
        */

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
