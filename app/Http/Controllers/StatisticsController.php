<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\UserResource;
use App\Models\Cart;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

// ●statistics
// * for the storeman
// [  ] get the income over timestamps
// [  ] get a sheet of days of month, week versus income/profit
// [  ] get a sheet of month versus income/profit
// [  ] get a sheet of weeks versus new customers

class StatisticsController extends Controller
{
    //functions could be added to user and admin controller but just for convenience I have made a statisticsController

    //this function does not contain dates
    public function userStat()
    {
        $ar = (request()->header('lang') == 'ar');
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
        $carts = $user->carts()->where('payed',1)->get();
        foreach ($carts as $cart) {
            $medicines = $cart->medicines()->get();
            foreach ($medicines as $med) {
                $totalMed += ($med->pivot->quantity);
                if ($ar) $name = $med->category()->first()->ar_name;
                else $name = $med->category()->first()->name;
                $catPercentage[$name] =  ($catPercentage[$name] ?? 0) + $med->pivot->quantity;
            }
        }

        //we can write this too
        //$totalMed = $carts->join('cart_medicine','id','=','cart_medicine.cart_id')->sum('quantity');

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

    public function statByDates($year, $month)
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
            return ($group->sum('bill')) / 1000000.0;
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

    public function adminStat(){

        $ar = (request()->header('lang') == 'ar');

        $userCnt = User::count();
        $ordersCnt = Cart::count();
        $inPreperationOrders = Cart::where('status','in preparation')->count();
        $gettingDeliveredOrders = Cart::where('status','getting delivered')->count();
        $deliveredOrders = Cart::where('status','delivered')->count();
        $refusedOrders = Cart::where('status','refused')->count();
        //another way to some pivot attributes in laravel, look totalMed in userStat
        $soldMedicines = Cart::where('payed',1)->join('cart_medicine','id','=','cart_medicine.cart_id')->sum('quantity');
        $inStockMedicines = Medicine::where('quantity','!=',0)->where('expirationDate','>',now())->count();
        $inStockQuantity = Medicine::where('quantity','!=',0)->where('expirationDate','>',now())->sum('quantity');

        //orderBy takes a function that sums the bill column from the carts table and it only takes the carts of the user by joining the tables with whereColumn
        $topUsers = UserResource::collection(User::orderBy(function($query){
            $query->selectRaw('sum(bill)')->from('carts')->whereColumn('users.id','carts.user_id');
        },'desc')->take(5)->get());

        $topMedicines = MedicineResource::collection(Medicine::orderBy('popularity','desc')->take(5)->get());

        //this code should not be written like that, but we did it as so because brand is not an independent entity in the database
        $topCompanies = [];
        $medicines = Medicine::get();
        foreach($medicines as $medicine){
            $topCompanies[$medicine->brand] = ($topCompanies[$medicine->brand] ?? 0) + $medicine->popularity;
        }
        arsort($topCompanies);
        //array slice takes the elements from the first index to the second exclusive, array keys returns an array with no values
        $topCompanies = array_slice(array_keys($topCompanies),0,5);

        //categories charts with percentages

        //first the percentage of each category for its in stock medicines
        $inStockCategoriesPercentages = [];
        $cats = Category::get();
        foreach($cats as $cat){
            if($ar) $name = $cat->ar_name;
            else $name = $cat->name;
            $inStockCategoriesPercentages[$name] = (float) number_format((100 * $cat->medicines()->where('quantity','!=',0)->where('expirationDate','>',now())->count())/$inStockMedicines,2);
            if($inStockCategoriesPercentages[$name] == 0) unset($inStockCategoriesPercentages[$name]);
        }

        //second the percentage of each category for its sold medicines
        $soldCategoriesPercentages = [];
        $carts = Cart::where('payed',1)->get();
        foreach($carts as $cart){
            $medicines = $cart->medicines()->get();
            foreach($medicines as $medicine){
                if($ar) $name = $medicine->category()->first()->ar_name;
                else $name = $medicine->category()->first()->name;
                $soldCategoriesPercentages[$name] = ($soldCategoriesPercentages[$name] ?? 0) + $medicine->pivot->quantity;
            }
        }
        foreach($soldCategoriesPercentages as &$perc){
            $perc = (float) number_format(($perc * 100.0)/$soldMedicines,2);
        }


        return response()->json([
            'users count' => $userCnt,
            'orders count' => $ordersCnt,
            'in preparation orders' => $inPreperationOrders,
            'getting delivered orders' => $gettingDeliveredOrders,
            'delivered orders' => $deliveredOrders,
            'refused orders' => $refusedOrders,
            'sold medicines count' => (int) $soldMedicines,
            'in stock medicines' => $inStockMedicines,
            'in stock medicines quantity' => (int) $inStockQuantity,
            'top users' => $topUsers,
            'top medicines' => $topMedicines,
            'top companies' => $topCompanies,
            'categories percetages in stock' => $inStockCategoriesPercentages,
            'categories percentages for sold medicines' => $soldCategoriesPercentages
        ]);


    }
}
