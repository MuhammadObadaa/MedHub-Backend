<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{

    //TODO: must design notifications table with status code for every order sent

    //used by the storeman to display the carts of a sepcific user
    public function list(User $user){
        $carts = $user->carts()->get();
        $message = [
            'message' => 'orders of the user displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }

    //used by the user to display his own carts
    public function authList(){
        $carts = auth()->user()->carts->get();
        $message = [
            'message' => 'your orders displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }

    //this function is used to display a specific cart
    public function show(Cart $cart){
        $message = [
            'message' => 'order displayed successfully!',
            'status_code' => 200
        ];
        return (new CartResource($cart))->additional($message);
    }
}
