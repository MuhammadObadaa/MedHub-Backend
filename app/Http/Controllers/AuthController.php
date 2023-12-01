<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

//search about laravel/ui and Sanctum package
class AuthController extends Controller
{
    // when the middleware doesn't called use this:
    /*
    public function __construct()
    {
        /*
        the 'only' attribute makes the middleware check only the routes specified with middleware (what ever kind of it)
        and not all routes in the controller.
        without it all functions in this controller will be authenticated with this middleware

        $this->middleware('user', ['only' => []]);
    }
    */
    public function create()
    {
        $imageFile = '';

        if (request()->has('image')) {
            //TODO: this should be the image itself not it's url
            request()->validate([
                'image' => 'image'
            ]);

            $imageFile = request()->file('image')->store('app', 'public');
        }

        if (User::where('phoneNumber', request('phoneNumber'))->first())
            return response()->json(['message' => 'This phoneNumber already exist'], 400);

        //TODO: handle the exceptions with connection to DB
        $user = User::create([
            'name' => request('name'),
            'phoneNumber' => request('phoneNumber'),
            'pharmacyName' => request('pharmacyName'),
            'password' => Hash::make(request('password')),
            'image' => $imageFile
        ]);

        return $this->login();
    }

    public function login()
    {
        $user = User::where('phoneNumber', request('phoneNumber'))->first();

        if (!$user)
            return response()->json(['message' => 'No such phoneNumber'], 400);
        if (!Hash::check(request('password'), $user->password))
            return response()->json(['message' => 'Wrong Password!'], 400);

        //TODO: make rememberMe optional
        //TODO: No need to send the rememberMe option to the login. where we already create our own token in the cookie
        Auth::login($user, TRUE);
        //Auth::attempt()

        //for web application
        $cookie = cookie('token', $user->remember_token, 10);

        return response()->json(['message' => 'Logged in successfully', 'token' => $user->remember_token])
            ->withCookie($cookie);
    }

    public function logout()
    {
        // to forget the token and cookies then logout
        $user = AuthMiddleware::getUser();

        Auth::setUser($user);
        Auth::logout();

        cookie()->forget('token');

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
