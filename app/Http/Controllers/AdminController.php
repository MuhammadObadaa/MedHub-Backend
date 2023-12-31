<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cart;

class AdminController extends Controller
{
    //TODO: whats the best practice of setting routes position?
    //TODO: make FCMProjectKey in the .env file

    //https://youtube.com/playlist?list=PLuBL2DYgVDm2HktOlxMTqDn5zGZh1P_Gf&si=AXCuMdxSnyhpTILO
    /**
     * @param $to user firebase token
     * @param $message the message of notification
     */
    public static function notify(string $to, string $message)
    {
        $FCMTitle = "MedHub";
        $FCMApiRoute = 'https://fcm.googleapis.com/fcm/send';
        $FCMProjectKey = 'key=AAAA0Jd0UKU:APA91bH1SFvt7tvg0V_7y1gxzYZrrI7eJaG8zE-o2v_mY-kQOG8woYaPntYl8tfF8xxDGspZrFoWgW7WW7wAGFgEH1zHjTniGeYFQ_WkcVsoFkYyNbLkLn0-lOxxfSmaNgaZWFp2av1U';

        $data = [
            "registration_ids" => [$to], // or you can use 'to' key
            "notification" => [
                "title" => $FCMTitle,
                "body" => $message,
                //"sound" => "default", // for ios
            ]
        ];

        $jsonData = json_encode($data);

        $response = Http::withBody($jsonData, 'application/json')->withHeaders(
            ['Authorization' => $FCMProjectKey]
        )->post($FCMApiRoute);

        return $response;
    }
    private function test()
    {
        //return $this->notify("f9znK07KScizyrb7GbAsD1:APA91bH0YAHw8wPmI0_eWnLgHthLrYsPezNso7PjhlunIBHRuD1OyOPc7oN7aqNHi1E5RIPN2HApzsaw_KSLmPOxOs6S70Ip3r33GDo-lzfk3fcKOY8K9xeJYj1EtFtL5bdvZv4Nq6w7", 'something');

        $to = "fcm token";
        $message = "something";
        $FCMTitle = "MedHub";
        $FCMApiRoute = 'https://fcm.googleapis.com/fcm/send';
        $FCMProjectKey = 'key=AAAA0Jd0UKU:APA91bH1SFvt7tvg0V_7y1gxzYZrrI7eJaG8zE-o2v_mY-kQOG8woYaPntYl8tfF8xxDGspZrFoWgW7WW7wAGFgEH1zHjTniGeYFQ_WkcVsoFkYyNbLkLn0-lOxxfSmaNgaZWFp2av1U';

        $data = [
            "registration_ids" => [$to],
            "notification" => [
                "title" => $FCMTitle,
                "body" => $message,
                "sound" => "default",
            ]
        ];

        $jsonData = json_encode($data);

        $header = [
            'Authorization: ' . $FCMProjectKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $FCMApiRoute);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //due to error

        $result = curl_exec($ch);

        curl_close($ch);
        //$errors = curl_error($ch);
        //$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $result;
    }

    public function pay($cart)
    {
        $cart = Cart::where('id', $cart)->first();

        //TODO: what's the difference?
        //$cart->update(['payed', 1]);
        $cart->payed = true;
        $cart->save();

        AdminController::notify($cart->user()->FCMToken, 'cart ' . $cart->id . ' has been payed');

        return response()->json(['message' => 'the order ' . $cart->id . ' was payed successfully!'], 200);
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
        else if(request()->has('status') 67&& ($cart->status == "getting delivered")){
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

        $billUpdate = 0;
        $profitUpdate = 0;
        //TODO: add profitUpdate
        if (request()->get('status') == "getting delivered") {
            $medicines = $cart->medicines;
            $noQuantity = true;
            foreach ($medicines as $medicine) {
                if ($medicine->available && $medicine->quantity != 0) {
                    $noQuantity = false;
                    break;
                }
            }
            if ($noQuantity) {
                $cart->update(['status' => 'refused']);
                AdminController::notify($cart->user()->FCMToken, 'order ' . $cart->id . ' has been refused, all medicines you ordered are out of stock, sorry for inconvenience');
                return response()->json([
                    'message' => 'all medicines you ordered are out of stock, sorry for inconvenience',
                ], 409);
            }
            foreach ($medicines as $medicine) {
                if ($medicine->available && $medicine->quantity != 0) {
                    if ($medicine->quantity < $medicine->pivot->quantity) {
                        $billUpdate += (($medicine->pivot->quantity - $medicine->quantity) * $medicine->pivot->price);
                        $profitUpdate += (($medicine->pivot->quantity - $medicine->quantity) * $medicine->pivot->profit);
                        $medicine->pivot->quantity = $medicine->quantity;
                        $medicine->quantity = 0;
                        $medicine->popularity = $medicine->popularity + 2 * $medicine->pivot->quantity;
                        $medicine->save();
                        $medicine->pivot->save();
                        AdminController::notify($cart->user()->FCMToken, 'in order ' . $cart->id . ' the available quantity of ' . $medicine->name . ' does not meet the your need, we have limited the quantity to ' . $medicine->pivot->quantity);
                    } else {
                        $medicine->quantity = $medicine->quantity - $medicine->pivot->quantity;
                        $medicine->popularity = $medicine->popularity + 2 * $medicine->pivot->quantity;
                        $medicine->save();
                    }
                } else {
                    $billUpdate += ($medicine->pivot->quantity * $medicine->pivot->price);
                    $profitUpdate += ($medicine->pivot->quantity * $medicine->pivot->profit);
                    $cart->medicines()->detach($medicine);
                    AdminController::notify($cart->user()->FCMToken, 'in order ' . $cart->id . ' medicine ' . $medicine->name . ' is out of stock, we have removed it from your order');
                }
            }
            $cart->update([
                'bill' => $cart->bill - $billUpdate,
                'profit' => $cart->profit - $profitUpdate
            ]);
        }

        //third approach which is the best approach in my opinion, all updates must be handled the moment the customer send his orders

        if (request()->get('status') == "delivered") {
            if ($cart->payed == false) {
                return response()->json([
                    'message' => 'cannot deliver the order without payment!'
                ], 402 /* payment required */);
            }
        }

        $message = "status of order has been updated successfully!";
        $cart->update(["status" => request()->get('status')]);
        AdminController::notify($cart->user()->FCMToken, 'order ' . $cart->id . ' is ' . $cart->status);
        //TODO: make sure that the user received the cart.
        return response()->json(['message' => $message]);
    }
}
