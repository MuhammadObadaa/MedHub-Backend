<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Medicine;
use App\http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\Cart;
use App\Models\User;
use Exception;

class UserController extends Controller
{
    public function show()
    {
        $user = AuthMiddleware::getUser()->select('name', 'pharmacyName', 'pharmacyLocation', 'phoneNumber', 'image')->first();
        return response()->json(['user' => $user]);
    }

    public function changePassword()
    {

        $user = AuthMiddleware::getUser();

        if (!Hash::check(request('oldPassword'), $user->password))
            return response()->json(['message' => 'Wrong Password!'], 400);

        $user->update(['password' => Hash::make(request('newPassword'))]);
        return response()->json(['message' => 'Password changed successfully']);
    }

    //TODO: a new raw will be add to database when you favor a medicine even if it was already in the table
    public function favor($medicine)
    {
        $user = AuthMiddleware::getUser();

        try {
            // you can send medicine->id and the medicine object itself
            $user->favors()->attach($medicine);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 400);
        }

        return response()->json(['message' => 'medicine added to favorites successfully']);
    }

    //TODO: return a sign for already unfavored medicine when unfavored it
    public function unFavor($medicineId)
    {
        $user = AuthMiddleware::getUser();

        try {
            $user->favors()->detach($medicineId);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 400);
        }

        return response()->json(['message' => 'medicine removed from favorites successfully']);
    }

    public function addCart()
    {
        $user = AuthMiddleware::getUser();
        $bill = 0;

        $cartContents = request('cart');

        $cart = Cart::create(['user_id' => $user->id, 'bill' => '0', 'status' => 'preparing']); // other -> 'sent' and 'received'

        foreach ($cartContents as $medicine) {
            $cart->medicines()->attach($medicine['id'], ['quantity' => $medicine['quantity']]);
            $medicinePrice = Medicine::where('id', $medicine['id'])->first()->price;
            $bill += $medicine['quantity'] * $medicinePrice;
        }

        $cart->update(['bill' => $bill]);

        return response()->json(['message' => 'Cart added successfully!']);
    }

    public function changeImage()
    {
        $user = AuthMiddleware::getUser();

        $validatedImage = Validator::make(request()->get('image'), ['image' => 'image']);

        if ($validatedImage->fails())
            return response()->json(['message' => 'Invalid image file']);

        $imageFile = request()->file('image')->store('app', 'public');
        if (File::exists($user->image))
            File::delete($user->image);

        $user->update(['image' => $imageFile]);

        return response()->json(['message' => 'Image changed successfully!']);
    }

    public function changeName()
    {
        $user = AuthMiddleWare::getUser();

        $user->update(['name' => request('name')]);

        return response()->json(['message' => 'name changed successfully!']);
    }

    public function changePhName()
    {
        $user = AuthMiddleWare::getUser();

        $user->update(['PharmacyName' => request('PharmacyName')]);

        return response()->json(['message' => 'PharmacyName changed successfully!']);
    }

    public function changePhLocation()
    {
        $user = AuthMiddleWare::getUser();

        $user->update(['PharmacyLocation' => request('PharmacyLocation')]);

        return response()->json(['message' => 'PharmacyLocation changed successfully!']);
    }
}
