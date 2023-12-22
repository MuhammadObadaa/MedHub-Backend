<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Resources\CartResource;
use App\Http\Resources\UserResource;
use App\Models\Cart;
use App\Models\User;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function userReport($year1,$month1,$day1,$year2,$month2,$day2){

        $strt = Carbon::create($year1,$month1,$day1);
        $end = Carbon::create($year2,$month2,$day2,23,59,59);
        $ar = (request()->header('lang') == 'ar');
        //$user = User::find(1);
        $user = AuthMiddleware::getUser();
        $totalOrders = $user->carts()->whereBetween('created_at',[$strt,$end])->count();
        $refusedOrders = $user->carts()->whereBetween('created_at',[$strt,$end])->where('status', 'refused')->count();
        $PreparingOrders = $user->carts()->whereBetween('created_at',[$strt,$end])->where('status', 'in preparation')->count();
        $deliveredOrders = $user->carts()->whereBetween('created_at',[$strt,$end])->where('status', 'delivered')->count();
        $gettingDeliveredOrders = $user->carts()->whereBetween('created_at',[$strt,$end])->where('status', 'getting delivered')->count();
        $totalBill = (int) $user->carts()->where('payed',1)->whereBetween('created_at',[$strt,$end])->sum('bill');

        $carts = $user->carts()->whereBetween('created_at',[$strt,$end])->get();
        $cartsChart = $carts->groupBy(function ($cart) {
            return $cart->created_at->format('Y-m-d');
        })->map(function($group){
            return ($group->sum('bill'));
        });


        $carts = CartResource::collection($user->carts()->whereBetween('created_at',[$strt,$end])->get());

        return response()->json([
            'total orders' => $totalOrders,
            'refused orders' => $refusedOrders,
            'in preparation orders' => $PreparingOrders,
            'delivered orders' => $deliveredOrders,
            'getting delivered orders' => $gettingDeliveredOrders,
            'total payment' => $totalBill,
            'carts chart' => $cartsChart,
            'carts' => $carts,
            'message' => 'report returned successfully!'
        ]);
    }

    public function adminReport($year1,$month1,$day1,$year2,$month2,$day2){

        $strt = Carbon::create($year1,$month1,$day1);
        $end = Carbon::create($year2,$month2,$day2,23,59,59);
        $ar = (request()->header('lang') == 'ar');

        $newUsers = User::whereBetween('created_at',[$strt,$end])->count();
        $newUsersInfo = UserResource::collection(User::whereBetween('created_at',[$strt,$end])->get());
        $newOrders = Cart::whereBetween('created_at',[$strt,$end])->count();
        $inPreparationOrders = Cart::whereBetween('created_at',[$strt,$end])->where('status','in preparation')->count();
        $gettingDeliveredOrders = Cart::whereBetween('created_at',[$strt,$end])->where('status','getting delivered')->count();
        $deliveredOrders = Cart::whereBetween('created_at',[$strt,$end])->where('status','delivered')->count();
        $refusedOrders = Cart::whereBetween('created_at',[$strt,$end])->where('status','refused')->count();
        //another way to some pivot attributes in laravel, look totalMed in userStat
        $soldMedicines = Cart::whereBetween('carts.created_at',[$strt,$end])->where('payed',1)->join('cart_medicine','id','=','cart_medicine.cart_id')->sum('quantity');
        $totalIncome = Cart::whereBetween('carts.created_at',[$strt,$end])->where('payed',1)->sum('bill');
        $totalProfit = Cart::whereBetween('carts.created_at',[$strt,$end])->where('payed',1)->sum('profit');

        $soldCategoriesPercentages = [];
        $carts = Cart::whereBetween('created_at',[$strt,$end])->where('payed',1)->get();
        foreach($carts as $cart){
            $medicines = $cart->medicines()->get();
            foreach($medicines as $medicine){
                if($ar) $name = $medicine->category()->first()->ar_name;
                else $name = $medicine->category()->first()->name;
                $soldCategoriesPercentages[$name] = ($soldCategoriesPercentages[$name] ?? 0) + $medicine->pivot->quantity;
            }
        }
        foreach($soldCategoriesPercentages as &$percentage){
            $percentage = (float) number_format(($percentage * 100.0)/$soldMedicines,2);
        }

        $carts = Cart::whereBetween('created_at',[$strt,$end])->where('payed',1)->get();
        $IncomeChart = $carts->groupBy(function ($cart){
            return $cart->created_at->format('Y-m-d');
        })->map(function ($group){
            return ($group->sum('bill'));
        });
        $ProfitChart = $carts->groupBy(function ($cart){
            return $cart->created_at->format('Y-m-d');
        })->map(function ($group){
            return ($group->sum('profit'));
        });

        $ordersInfo = CartResource::collection(Cart::whereBetween('created_at',[$strt,$end])->where('payed',1)->get());


        return response()->json([
            'joined users' => $newUsers,
            'joined users info' => $newUsersInfo,
            'orders count' => $newOrders,
            'in preparation orders' => $inPreparationOrders,
            'getting delivered orders' => $gettingDeliveredOrders,
            'delivered orders' => $deliveredOrders,
            'refused orders' => $refusedOrders,
            'sold medicines count' => (int) $soldMedicines,
            'categories percentages for sold medicines' => $soldCategoriesPercentages,
            'total income' => (int) $totalIncome,
            'total profit' => (int) $totalProfit,
            'income chart' => $IncomeChart,
            'profit chart' => $ProfitChart,
            'payed orders info' =>$ordersInfo,

            'message' => 'report returned successfully!'
        ]);
    }
}
