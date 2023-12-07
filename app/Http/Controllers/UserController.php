<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Medicine;
use App\http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Exception;

class UserController extends Controller
{
    //TODO: recourse for user
    public function show()
    {
        $user = AuthMiddleware::getUser()->select('name', 'pharmacyName', 'pharmacyLocation', 'phoneNumber', 'image')->first();
        return response()->json([
            'user' => $user,
            // 'request' => request()->cookie('token'),
            // 'get' => Cookie::get('token')
        ]);
    }

    public function favor(Medicine $medicine)
    {
        $user = AuthMiddleware::getUser();

        // you can send medicine->id and the medicine object itself
        $user->favors()->attach($medicine->id);

        $medicine->update(['popularity' =>  $medicine->popularity + 1]);

        return response()->json(['message' => 'medicine added to favorites successfully']);
    }

    public function unFavor(Medicine $medicine)
    {
        $user = AuthMiddleware::getUser();

        $user->favors()->detach($medicine);

        $medicine->update(['popularity' =>  $medicine->popularity - 1]);

        return response()->json(['message' => 'medicine removed from favorites successfully']);
    }

    //TODO: specify this method as needed. at least some of them
    public function update()
    {
        $user = AuthMiddleWare::getUser();

        if (request()->has('name'))
            $user->update(['name' => request('name')]);
        if (request()->has('pharmacyName'))
            $user->update(['pharmacyName' => request('pharmacyName')]);
        if (request()->has('pharmacyLocation'))
            $user->update(['pharmacyLocation' => request('pharmacyLocation')]);
        if (request()->has('newPassword')) {
            if (!Hash::check(request('oldPassword'), $user->password))
                return response()->json(['message' => 'Wrong Password!'], 400);
            $user->update(['password', Hash::make('newPassword')]);
        }
        if (request()->has('image')) {
            $validatedImage = Validator::make(request()->all(), ['image' => 'image']);

            if ($validatedImage->fails())
                return response()->json(['message' => 'Invalid image file'], 400);

            $imageFile = request()->file('image')->store('app', 'public');
            // if (File::exists($user->image))
            //     File::delete($user->image);
            if ($user->image != null)
                Storage::disk('public')->delete($user->image);

            $user->update(['image' => $imageFile]);
        }

        return response()->json(['message' => 'Changes applied successfully!']);
    }
}
