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
        //TODO:Obada function
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
        $carts = Cart::where('status', 'in preparation')->oldest()->get();
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
        //TODO: obada function
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
    //the order of the status of the order must be handled carefully by the front end
    public function update(Cart $cart)
    {
        //unreachable if statment if handled correctly by front end
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
            $cart->payed = request()->get('payed');
            $cart->save();
            return response()->json([
                'message' => 'payment status changed successfully!',
                'status' => 200
            ]);
        }

        //choose one of the two approaches, comment the other

        //first approach, refusing the whole order for a single out of stock medicine
        if (request()->get('status') == "getting delivered") {
            $medicines = $cart->medicines()->get();
            foreach ($medicines as $medicine) {
                if ($medicine->quantity < $medicine->pivot->quantity) {
                    $cart->update(['status' => 'refused']);
                    return response()->json([
                        'message' => 'cannot process the order because '.$medicine->name." ".$medicine->quantity." ".$medicine->pivot->quantity.' is out of stock',
                        'status' => 409 //confilct
                    ]);
                }
            }
            foreach ($medicines as $medicine) {
                $medicine->update(['quantity' => ($medicine->quantity - $medicine->pivot->quantity)]);
            }
        }
        //secod approach: if the ordered medicine is greater than the one in stock, give the customer all, in stock
        //and if quantity in stock is zero, remove the medicine from the order
        //if all medicines are out of the stock, refuse the order

        $messages = []; $i = 0; $billUpdate = 0;
        if (request()->get('status') == "getting delivered"){
            $medicines = $cart->medicines()->get();
            $flag = true;
            foreach ($medicines as $medicine) {
                if ($medicine->quantity != 0) {
                    $flag = false;
                }
            }
            if($flag){
                $cart->update(['status' => 'refused']);
                return response()->json([
                    'message' => 'all medicines you orderd are out of stock, sorry for inconvenience',
                    'status' => 409 //confilct
                ]);
            }
            foreach ($medicines as $medicine) {
                if($medicine->quantity != 0){
                    if ($medicine->quantity < $medicine->pivot->quantity) {
                        $billUpdate += (($medicine->pivot->quantity - $medicine->quantity)*$medicine->price);
                        $medicine->pivot->quantity = $medicine->quantity;
                        $medicine->quantity = 0;
                        $medicine->save();
                        $medicine->pivot->save();
                        $messages[$i++] = "the quantity of ".$medicine->name." does not meet the customer need, we have limited the order quantity to ".$medicine->pivot->quantity;
                    }
                    else{
                        $medicine->quantity = $medicine->quantity - $medicine->pivot->quantity;
                        $medicine->save();
                    }
                }
                else{
                    $billUpdate += ($medicine->pivot->quantity * $medicine->price);
                    $cart->medicines()->detach($medicine);
                    $messages[$i++] = "medicine ".$medicine->name." is out of stock, we have removed it from your order";
                }
            }
            $cart->update(['bill' => $cart->bill - $billUpdate]);
        }

        //third approach which is the best approach in my opinion, all updates must be handled the moment the customer send his orders

        if (request()->get('status') == "delivered") {
            if ($cart->payed == false) {
                return response()->json([
                    'message' => 'cannot deliver the order without payment!',
                    'status' => 402 //payment required
                ]);
            }
        }
        $messages[$i++] = "'status of order updated successfully!'";
        $cart->update(["status" => request()->get('status')]);
        return response()->json([
            'message' => $messages,
            'status' => 200
        ]);
    }
}
