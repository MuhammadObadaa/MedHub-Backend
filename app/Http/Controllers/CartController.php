<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{

    //TODO: must design notifications table with status code for every order sent

    //storing the cart in the database
    public function store()
    {
        //Obada function
    }

    //used by the storeman to display the carts of a sepcific user
    public function userList(User $user)
    {
        $carts = $user->carts()->get();
        $message = [
            'message' => 'orders of the user displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }

    //used by the storeman, returns all the orders of all users
    public function all()
    {
        $carts = Cart::latest()->get();
        $message = [
            'message' => 'all orders displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }

    //returns all carts in preparation according to latency
    public function inPreparation()
    {
        $carts = Cart::where('status', 'in preparation')->latest()->get();
        $message = [
            'message' => 'in preparation orders displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }
    public function gettingDelivered()
    {
        $carts = Cart::where('status', 'getting delivered')->latest()->get();
        $message = [
            'message' => 'getting delivered orders user displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }
    public function delivered()
    {
        $carts = Cart::where('status', 'delivered')->latest()->get();
        $message = [
            'message' => 'delivered orders displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }

    //used by the user to display his own carts
    public function authList()
    {
        $carts = auth()->user()->carts->get();
        $message = [
            'message' => 'your orders displayed successfully!',
            'status_code' => 200
        ];
        return CartResource::collection($carts)->additional($message);
    }

    //this function is used to display a specific cart
    public function show(Cart $cart)
    {
        $message = [
            'message' => 'order displayed successfully!',
            'status_code' => 200
        ];
        return (new CartResource($cart))->additional($message);
    }

    //update function is used by the storeman to update the status of the orders or the payment status

    public function update(Cart $cart)
    {
        /*if(request()->get('status') == "getting delivered"){
            //TODO:code to update quantity of medicines, need to be discussed
            //if 2 orders were sent to the storeman one of them takes all in stock medicines, the other will ruin database
            //in this case we need to much of handling and will cause inconvenience to the user, cause his order will be canceled
            //and that gives us another case to deal with, canceling orders
        }*/

        if(request()->has('payed')){
            $cart->payed = request()->get('payed');
            $cart->save();
            return response()->json([
                'message' => 'payment status changed successfully!',
                'status' =>200
            ]);
        }
        if(request()->get('status') == "delivered"){
            if($cart->payed == false){
                return response()->json([
                    'message' => 'cannot deliver the order without payment!',
                    'status' =>402 //payment required
                ]);
            }
        }
        $cart->update(["status" => request()->get('status')]);
        return response()->json([
            'message' => 'status of order updated successfully!',
            'status' => 200
        ]);
    }
}
