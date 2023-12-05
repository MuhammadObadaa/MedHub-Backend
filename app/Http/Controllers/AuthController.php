<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Middleware\AuthMiddleware;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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

    private function getUser(): User
    {
        //TODO: make it single function across UserController and AuthController
        //TODO: improve the way methods are re_getting the user after it was gotten in the middleware
        $user = User::where('remember_token', request('token'))->first();
        if (!$user)
            $user = User::where('remember_token', request()->cookie('token'))->first();

        //there is no need to check if the $user is null due to middleware check

        return $user;
    }
    public function store()
    {
        $imageFile = '';

        if (request()->has('image')) {
            $validatedImage = Validator::make(request()->get('image'), ['image' => 'image']);
            if ($validatedImage->fails())
                return response()->json(['message' => 'Invalid image file']);
            else
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
        Auth::login($user);
        //Auth::attempt()

        // if (request()->hasCookie('token')) {
        //     dump(Cookie::get('token'));
        //     dump(request()->cookie('token'));
        // }

        return response()->json(['message' => 'Logged in successfully', 'token' => $user->remember_token])
            ->withCookie(Cookie()->forever('token', $user->remember_token));
    }

    public function logout()
    {
        // to forget the token and cookies then logout
        $user = AuthMiddleware::getUser();

        Auth::setUser($user);
        Auth::logout();

        Cookie::queue(Cookie::forget('token'));

        try {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        } catch (Exception $e) {
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
