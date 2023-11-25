<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

//search about laravel/ui package
class AuthController extends Controller
{
    public function create()
    {
        $imageFile = '';

        if (request()->has('image')) {
            request()->validate([
                'image' => 'image'
            ]);

            $imageFile = request()->file('image')->store('app', 'public');
        }

        $user = User::create([
            'name' => request('name'),
            'phoneNumber' => request('phoneNumber'),
            'pharmacyName' => request('pharmacyName'),
            'password' => Hash::make(request('password')),
            'pharmacyLocation' => request('pharmacyLocation'),
            'image' => $imageFile
        ]);

        return $this->login();
    }

    public function login()
    {
        $user = User::where('phoneNumber', request('phoneNumber'))->first();

        if (!$user)
            return response()->json(['message' => 'No such phoneNumber'], 300);
        if (!Hash::check(request('password'), $user->password))
            return response()->json(['message' => 'password doesn\'t match'], 300);

        //TODO: make rememberMe optional
        Auth::login($user, TRUE);
        //Auth::attempt()

        //for web application
        $cookie = cookie('token', $user->remember_token, 10);

        return response()->json(['message' => 'Logged in successfully', 'token' => $user->remember_token])
            ->withCookie($cookie);
    }

    public function logout()
    {
        // $user = User::where('remember_token', request()->cookie('token'))->first();
        // if ($user)
        //     $user->update(['remember_token' => NULL]);

        // to forget the token and logout
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
