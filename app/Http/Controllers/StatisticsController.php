<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AuthMiddleware;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicine;



// ●statistics
// * for the storeman
// [  ] get the count of customers
// [  ] get the count of orders with status details
// [  ] get the income
// [  ] get the profit
// [  ] get the count of sold medicines
// [  ] get the top customers
// [  ] get the top medicines
// [  ] get the top categories
// [  ] get the top companies
// [  ] get a sheet of days of month, week versus income/profit
// [  ] get a sheet of month versus income/profit
// [  ] get a sheet of weeks versus new customers
// [  ] get the number of different medicines in stock

class StatisticsController extends Controller
{
    //functions could be added to user and admin controller but just for convenience I have made a statisticsController

    //this function does not contain dates
    public function userReport()
    {
        $user = AuthMiddleware::getUser();
        //$user = User::find(1);
        $totalOrders = $user->carts()->count();
        $refusedOrders = $user->carts()->where('status', 'refused')->count();
        $PreparingOrders = $user->carts()->where('status', 'in preparation')->count();
        $deliveredOrders = $user->carts()->where('status', 'delivered')->count();
        $gettingDeliveredOrders = $user->carts()->where('status', 'getting delivered')->count();
        $favoriteMedicines = $user->favors->count();
        $totalBill = (int) $user->carts()->sum('bill');
        $totalMed = 0;
        $catPercentage = [];
        $carts = $user->carts()->get();
        foreach ($carts as $cart) {
            $medicines = $cart->medicines()->get();
            foreach ($medicines as $med) {
                $totalMed += ($med->pivot->quantity);
                if (request()->header('lang') == 'ar') $name = $med->category()->first()->ar_name;
                else $name = $med->category()->first()->name;
                $catPercentage[$name] =  ($catPercentage[$name] ?? 0) + $med->pivot->quantity;
            }
        }
        foreach ($catPercentage as &$percentage) {
            $percentage = (float)number_format($percentage * 100.0 / $totalMed, 2);
        }
        return response()->json([
            'total orders' => $totalOrders,
            'refused orders' => $refusedOrders,
            'in preparation orders' => $PreparingOrders,
            'delivered orders' => $deliveredOrders,
            'getting delivered orders' => $gettingDeliveredOrders,
            'total payment' => $totalBill,
            'total medicines' => $totalMed,
            'favorite medicines' => $favoriteMedicines,
            'categories percentages' => $catPercentage,

            'message' => 'statistics returned successfully!'
        ]);
    }

    public function reportByDates($year, $month)
    {

        $user = AuthMiddleware::getUser();
        //$user = User::find(1);

        if ($year != 0 && $month != 0) {
            $carts = $user->carts()->whereYear('created_at', $year)->whereMonth('created_at', $month)->get();
        } else {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $carts = $user->carts()->whereBetween('created_at', [$start, $end])->get();
        }

        $cartsByWeek = $carts->groupBy(function ($cart) {
            return $cart->created_at->weekOfMonth;
        })->map(function ($group) {
            return ($group->sum('bill')) / 1000000;
        });
        return response()->json([
            "points" => [
                '1' => $cartsByWeek["1"] ?? 0,
                '2' => $cartsByWeek["2"] ?? 0,
                '3' => $cartsByWeek["3"] ?? 0,
                '4' => $cartsByWeek["4"] ?? 0
            ],
            'message' => 'chart returned successfully!'
        ]);
        //, 200, [], JSON_PRETTY_PRINT);
    }
}
