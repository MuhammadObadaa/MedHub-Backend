<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function __construct()
    {
        /*
        the 'only' attribute makes the middleware check only the routes specified with middleware (what ever kind of it)
        and not all routes in the controller.
        without it all functions in this controller will be authenticated with this middleware
        */
        $this->middleware('user', ['only' => []]);
    }

    public function show()
    {
        $user = User::where('id', $this->getUser()->id)->select('name', 'pharmacyName', 'pharmacyLocation', 'phoneNumber', 'image')->first();
        return response()->json(['user' => $user]);
    }

    private function getUser(): User
    {
        $user = User::where('remember_token', request('token'))->first();
        if (!$user)
            $user = User::where('remember_token', request()->cookie('token'))->first();

        return $user;
    }

    public function changePassword()
    {

        $user = $this->getUser();

        if (!Hash::check(request('oldPassword'), $user->password))
            return response()->json(['message' => 'Wrong Password!'], 400);

        $user->update(['password' => Hash::make(request('newPassword'))]);
        return response()->json(['message' => 'Password changed successfully']);
    }

    public function favor($medicineId)
    {
        $user = $this->getUser();

        $user->favors()->attach($medicineId);
        return response()->json(['message' => 'medicine added to favorite list successfully']);
    }

    public function unFavor($medicineId)
    {
        $user = $this->getUser();

        $user->favors()->detach($medicineId);
        return response()->json(['message' => 'medicine removed from favorite list successfully']);
    }

    public function addCart()
    {
        $user = $this->getUser();
        $bill = 0;

        $cartContents = request('cart');

        $cart = Cart::create(['user_id' => $user->id, 'bill' => '0', 'status' => 'preparing']); // other -> 'sent' and 'received'

        foreach ($cartContents as $medicine) {
            $cart->medicines()->attach($medicine['id'], ['quantity' => $medicine['quantity']]);
            //TODO: is it better to get medicine price from Medicine method or keep it as this?
            //if it works don't touch it :)
            $medicinePrice = Medicine::where('id', $medicine['id'])->first()->price;
            $bill += $medicine['quantity'] * $medicinePrice;
        }

        $cart->update(['bill' => $bill]);

        return response()->json(['message' => 'Cart added successfully!']);
    }

    public function changeImage()
    {
        $user = $this->getUser();
        if($user->image != null){
            Storage::disk('public')->delete($user->image);
        }
        $user->update(['image' => request('image')]);

        return response()->json(['message' => 'Image changed successfully!']);
    }

}
