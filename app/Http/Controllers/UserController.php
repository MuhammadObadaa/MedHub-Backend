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
use Illuminate\Support\Facades\Storage;
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
    public function favor(Medicine $medicine)
    {
        $user = AuthMiddleware::getUser();

        if ($user->hasFavored($medicine))
            return response()->json(['message' => 'Already in the favorite list!'], 400);

        try {
            // you can send medicine->id and the medicine object itself
            $user->favors()->attach($medicine);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 400);
        }

        $medicine->priority += 1;

        return response()->json(['message' => 'medicine added to favorites successfully']);
    }

    //TODO: return a sign for already unfavored medicine when unfavored it
    public function unFavor(Medicine $medicineId)
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

        //TODO: decrease the amount of each ordered medicine
        foreach ($cartContents as $order) {
            $cart->medicines()->attach($order['id'], ['quantity' => $order['quantity']]);
            //TODO: is it better to get medicine price from Medicine method or keep it as this?
            //if it works don't touch it :)
            $medicine = Medicine::where('id', $order['id'])->first();
            $bill += $order['quantity'] * $medicine->price;
            $medicine->priority += 2 * $order['quantity'];
        }

        $cart->update(['bill' => $bill]);

        return response()->json(['message' => 'Cart added successfully!']);
    }

    public function changeImage()
    {
        $user = AuthMiddleware::getUser();
        // if($user->image != null)
        //     Storage::disk('public')->delete($user->image);

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
