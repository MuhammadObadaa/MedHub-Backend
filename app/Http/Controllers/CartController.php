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

        $cartContents = request('cart');

        $cart = Cart::create(['user_id' => $user->id, 'bill' => '0', 'status' => 'preparing']); // other -> 'sent' and 'received'

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
    public function userList(User $user)
    {
        $carts = $user->carts()->get();
        $message = ['message' => 'orders of the user displayed successfully!'];
        return (new CartCollection($carts))->additional($message)->response()->setStatusCode(200);
        //TODO: remove setStatusCode because its the default
    }

    //used by the storeMan, returns all the orders of all users
    public function all()
    {
        $carts = Cart::latest()->get();
        $message = ['message' => 'all orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message)->response()->setStatusCode(200);
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
        $carts = Cart::where('status', 'refused')->oldest()->get();
        $message = ['message' => 'refused orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message);
    }
    public function gettingDelivered()
    {
        $carts = Cart::where('status', 'getting delivered')->latest()->get();
        $message = ['message' => 'getting delivered orders user displayed successfully!'];
        return (new CartCollection($carts))->additional($message)->response()->setStatusCode(200);
    }
    public function delivered()
    {
        $carts = Cart::where('status', 'delivered')->latest()->get();
        $message = ['message' => 'delivered orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message)->response()->setStatusCode(200);
    }

    //used by the user to display his own carts
    public function authList()
    {
        $carts = AuthMiddleware::getUser()->carts()->get();
        $message = ['message' => 'your orders displayed successfully!'];
        return (new CartCollection($carts))->additional($message)->response()->setStatusCode(200);
    }

    //this function is used to display a specific cart
    public function show(Cart $cart)
    {
        $message = ['message' => 'order displayed successfully!'];
        return (new CartResource($cart))->additional($message)->response()->setStatusCode(200);
    }

    //update function is used by the storeMan to update the status of the orders or the payment status
    //the order of the status of the order must be handled carefully by the front end
    public function update($cart)
    {
        //TODO: why my route binding does'nt work :)
        $cart = Cart::where('id', $cart)->first();
        //unreachable if statement if handled correctly by front end
        /* $flag = false;
        if(request()->has('status') && ($cart->status == 'refused' || $cart->status == 'delivered')){
            $flag == true;
        }
        else if(request()->has('status') && ($cart->status == "getting delivered")){
            if(request()->get('status') == "in preparation"){
                $flag == true;
            }
        }
        if($cart->payed && request()->has('payed') && (request()->get('payed')==false)){
            $flag = true;
        }

        if($flag){
            return response()->json([
                'message' => "cannot reverse status of order",
                'status' => 400 //bad request
            ]);
        }*/

        //if he is updating the payment status
        if (request()->has('payed')) {
            $cart->update(['payed' => request('payed')]);
            return response()->json(['message' => 'payment status changed successfully!'], 200);
        }

        //choose one of the two approaches, comment the other

        //first approach, refusing the whole order for a single out of stock medicine
        /*
        if (request()->get('status') == "getting delivered") {
            $medicines = $cart->medicines()->get();
            foreach ($medicines as $medicine) {
                if ($medicine->quantity < $medicine->pivot->quantity) {
                    $cart->update(['status' => 'refused']);
                    return response()->json([
                        'message' => 'cannot process the order because ' . $medicine->name . " " . $medicine->quantity . " " . $medicine->pivot->quantity . ' is out of stock'
                    ], 409 /*conflict);
                }
            }
            foreach ($medicines as $medicine)
                $medicine->update(['quantity' => ($medicine->quantity - $medicine->pivot->quantity)]);
        }
        */
        //second approach: if the ordered medicine is greater than the one in stock, give the customer all in stock
        //and if quantity in stock is zero, remove the medicine from the order
        //if all medicines are out of the stock, refuse the order

        $messages = [];
        $i = 0;
        $billUpdate = 0;
        if (request()->get('status') == "getting delivered") {
            $medicines = $cart->medicines;
            $noQuantity = true;
            foreach ($medicines as $medicine) {
                if ($medicine->quantity != 0) {
                    $noQuantity = false;
                    break;
                }
            }

            if ($noQuantity) {
                $cart->update(['status' => 'refused']);
                return response()->json([
                    'message' => 'all medicines you ordered are out of stock, sorry for inconvenience',
                ], 409);
            }
            foreach ($medicines as $medicine) {
                if ($medicine->quantity != 0) {
                    if ($medicine->quantity < $medicine->pivot->quantity) {
                        $billUpdate += (($medicine->pivot->quantity - $medicine->quantity) * $medicine->price);
                        $medicine->pivot->quantity = $medicine->quantity;
                        $medicine->quantity = 0;
                        $medicine->save();
                        $medicine->pivot->save();
                        $messages[$i++] = "the quantity of " . $medicine->name . " does not meet the customer need, we have limited the order quantity to " . $medicine->pivot->quantity;
                    } else {
                        $medicine->quantity = $medicine->quantity - $medicine->pivot->quantity;
                        $medicine->save();
                    }
                } else {
                    $billUpdate += ($medicine->pivot->quantity * $medicine->price);
                    $cart->medicines()->detach($medicine);
                    $messages[$i++] = "medicine " . $medicine->name . " is out of stock, we have removed it from your order";
                }
            }
            $cart->update(['bill' => $cart->bill - $billUpdate]);
        }

        //third approach which is the best approach in my opinion, all updates must be handled the moment the customer send his orders

        if (request()->get('status') == "delivered")
            if ($cart->payed == false) {
                return response()->json([
                    'message' => 'cannot deliver the order without payment!'
                ], 402 /* payment required */);
            }

        $messages[$i++] = "'status of order updated successfully!'";
        $cart->update(["status" => request()->get('status')]);
        return response()->json(['message' => $messages]);
    }
}
