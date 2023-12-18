<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Http\Resources\CartCollection;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Cart;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

class CartController extends Controller
{

    //TODO: must design notifications table with status code for every order sent

    //storing the cart in the database
    public function store()
    {
        $user = AuthMiddleware::getUser();
        $bill = 0;

        $cartContents = request('data');

        $cart = Cart::create(['user_id' => $user->id, 'bill' => '0', 'status' => 'in preparation']);

        foreach ($cartContents as $order) {
            $cart->medicines()->attach($order['id'], ['quantity' => $order['quantity']]);
            //TODO: is it better to get medicine price from Medicine method or keep it as this?
            //if it works don't touch it :)
            $medicine = Medicine::where('id', $order['id'])->first();
            $bill += $order['quantity'] * $medicine->price;
            $medicine->update(['popularity' =>  $medicine->popularity + 2 * $order['quantity']]);
        }

        $cart->update(['bill' => $bill]);

        return response()->json(['message' => 'Cart added successfully!']);
    }

    //used by the storeMan to display the carts of a specific user

    //used by the storeMan, returns all the orders of all users
    public function all()
    {
        $carts = Cart::latest()->get();
        $message = ['message' => 'all orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }

    //returns all carts in preparation according to latency
    public function inPreparation()
    {
        $carts = Cart::where('status', 'in preparation')->oldest()->get();
        $message = ['message' => 'in preparation orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }

    public function refused()
    {
        $carts = Cart::where('status', 'refused')->latest()->get();
        $message = ['message' => 'refused orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }
    public function gettingDelivered()
    {
        $carts = Cart::where('status', 'getting delivered')->latest()->get();
        $message = ['message' => 'getting delivered orders user displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }
    public function delivered()
    {
        $carts = Cart::where('status', 'delivered')->latest()->get();
        $message = ['message' => 'delivered orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }
    //used by admin to display a user's carts
    public function userList(User $user)
    {
        $carts = $user->carts()->get();
        $message = ['message' => 'orders of the user displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }

    //used by the user to display his own carts
    public function authList()
    {
        $carts = AuthMiddleware::getUser()->carts()->get();
        $message = ['message' => 'your orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }

    //this function is used to display a specific cart
    public function show(Cart $cart)
    {
        $message = ['message' => 'order displayed successfully!'];
        return (new CartResource($cart))->additional($message);
    }

}
