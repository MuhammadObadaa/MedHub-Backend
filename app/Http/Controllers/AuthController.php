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
    public function store()
    {
        $lang = $this->lang();
        $imageFile = '';

        if (request()->has('image')) {
            $validatedImage = Validator::make(request()->all(), ['image' => 'image']);
            if ($validatedImage->fails())
                return response()->json(['message' => 'Invalid image file']);
            else
                $imageFile = request()->file('image')->store('app', 'public');
        }

        if (User::where('phoneNumber', request('phoneNumber'))->first()) {
            $message['ar'] = 'هذا الرقم متوفر مسبقا';
            $message['en'] = 'This phone number already exist';
            return response()->json(['message' => $message[$lang]], 400);
        }

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
        $lang = $this->lang();
        $user = User::where('phoneNumber', request('phoneNumber'))->first();

        if (!$user) {
            $message['ar'] = 'لا يوجد هكذا رقم';
            $message['en'] = 'No such phoneNumber';
            return response()->json(['message' => $message[$lang]], 400);
        }
        if (!Hash::check(request('password'), $user->password)) {
            $message['ar'] = 'كلمة السر خاطئة';
            $message['en'] = 'Wrong Password!';
            return response()->json(['message' => $message[$lang]], 400);
        }

        //TODO: make rememberMe optional
        //TODO: No need to send the rememberMe option to the login. where we already create our own token in the cookie

        Auth::login($user, TRUE); // without true .. there is no remember_me value which is the token for our system
        //Auth::attempt()

        if (request()->hasHeader('FCMToken')) {
            $user->FCMToken = request()->header('FCMToken');
            $user->save();
        }


        // if (request()->hasCookie('token')) {
        //     dump(Cookie::get('token'));
        //     dump(request()->cookie('token'));
        // }

        $message['ar'] = 'تم تسجيل الدخول بنجاح';
        $message['en'] = 'Logged in successfully';

        return response()->json(['message' => $message[$lang], 'token' => $user->remember_token])
            ->withCookie(Cookie()->forever('token', $user->remember_token));
        //->header("ngrok-skip-browser-warning", "69420"); //for ngrok
    }

    public function logout()
    {
        $lang = $this->lang();
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

        $message['ar'] = 'تم تسجيل الخروج بنجاح';
        $message['en'] = 'Logged out successfully';

        return response()->json(['message' => $message[$lang]]);
    }
}
