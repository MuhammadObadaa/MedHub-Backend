<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Http\Resources\CartCollection;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Cart;
use App\Models\Medicine;
use App\Models\User;
use App\Http\Controllers\AdminController;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

class CartController extends Controller
{

    //TODO: pagination
    //TODO: must design notifications table with status code for every order sent

    //storing the cart in the database
    public function store()
    {
        $lang = $this->lang();
        $user = AuthMiddleware::getUser();
        $bill = 0;
        $profit = 0;

        $cartContents = request('data');

        $cart = Cart::create(['user_id' => $user->id, 'bill' => 0, 'profit' => 0]);

        foreach ($cartContents as $order) {
            $medicine = Medicine::where('id', $order['id'])->first();
            $cart->medicines()->attach($medicine->id, ['quantity' => $order['quantity'], 'price' => $medicine->price, 'profit' => $medicine->profit, 'expirationDate' => $medicine->expirationDate]);
            //TODO: is it better to get medicine price from Medicine method or keep it as this?
            //if it works don't touch it :)
            $bill += $order['quantity'] * $medicine->price;
            $profit += $order['quantity'] * $medicine->profit;
        }

        $cart->update(['bill' => $bill, 'profit' => $profit]);

        $message['ar'] = 'تم طلب طلبية جديدة من الصيدلاني ' . $user->name;
        $message['en'] = 'New order from ' . $user->name . ' was placed!';

        AdminController::notify(User::where('is_admin', 1)->first()->FCMToken, $message[$lang]);

        $message['ar'] = 'تم إضافة طلبية جديدة';
        $message['en'] = 'Cart added successfully!';

        return response()->json(['message' => $message[$lang]]);
    }


    //used by the storeMan, returns all the orders of all users
    public function all()
    {
        $lang = $this->lang();
        $carts = Cart::latest()->get();

        $message['ar'] = 'تم عرض جميع الطلبات بنجاح';
        $message['en'] = 'all orders displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }

    //returns all carts in preparation according to latency
    public function inPreparation()
    {
        $lang = $this->lang();
        $carts = Cart::where('status', 'in preparation')->oldest()->get();

        $message['ar'] = 'تم عرض جميع طلبات قيد التحضير بنجاح';
        $message['en'] = 'in preparation orders displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }

    public function refused()
    {

        $lang = $this->lang();
        $carts = Cart::where('status', 'refused')->latest()->get();

        $message['ar'] = 'تم عرض جميع الطلبات المرفوضة بنجاح';
        $message['en'] = 'refused orders displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }
    public function gettingDelivered()
    {

        $lang = $this->lang();
        $carts = Cart::where('status', 'getting delivered')->latest()->get();

        $message['ar'] = 'تم عرض جميع طلبات قيدالتوصيل بنجاح';
        $message['en'] = 'getting delivered orders user displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }
    public function delivered()
    {

        $lang = $this->lang();
        $carts = Cart::where('status', 'delivered')->latest()->get();

        $message['ar'] = 'تم عرض جميع الطلبات التي تم توصيلها بنجاح';
        $message['en'] =  'delivered orders displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }
    //used by admin to display a user's carts
    public function userList(User $user)
    {

        $lang = $this->lang();
        $carts = $user->carts()->get();

        $message['ar'] = 'تم عرض جميع الطلبات للصيدلاني ' . $user->id . ' بنجاح';
        $message['en'] = 'all orders to ' . $user->id . ' displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }

    //used by the user to display his own carts
    public function authList()
    {

        $lang = $this->lang();
        $carts = AuthMiddleware::getUser()->carts()->get();

        $message['ar'] = 'تم عرض جميع طلباتك بنجاح';
        $message['en'] = 'your orders displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartCollection($carts))->additional($message);
    }

    //this function is used to display a specific cart
    public function show(Cart $cart)
    {
        $lang = $this->lang();

        $message['ar'] = 'تم عرض الطلب بنجاح';
        $message['en'] = 'all orders displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new CartResource($cart))->additional($message);
    }
}
