<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\UserResource;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cart;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;



class StatisticsController extends Controller
{
    //functions could be added to user and admin controller but just for convenience I have made a statisticsController

    //this function does not contain dates
    public function userStat()
    {
        $ar = (request()->header('lang') == 'ar');
        $user = AuthMiddleware::getUser();
        $totalOrders = $user->carts()->count();
        $refusedOrders = $user->carts()->where('status', 'refused')->count();
        $PreparingOrders = $user->carts()->where('status', 'in preparation')->count();
        $deliveredOrders = $user->carts()->where('status', 'delivered')->count();
        $gettingDeliveredOrders = $user->carts()->where('status', 'getting delivered')->count();
        $favoriteMedicines = $user->favors->count();
        $totalBill = (int) $user->carts()->where('payed', 1)->sum('bill');
        $totalMed = 0;
        $catPercentage = [];
        $carts = $user->carts()->where('payed', 1)->get();
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

    public function userCharts($year, $month)
    {

        $user = AuthMiddleware::getUser();

        if ($year != 0 && $month != 0) {
            $carts = $user->carts()->whereYear('created_at', $year)->whereMonth('created_at', $month)->where('payed', 1)->get();
        } else {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $carts = $user->carts()->whereBetween('created_at', [$start, $end])->where('payed', 1)->get();
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


    public function adminStat()
    {
        $ar = (request()->header('lang') == 'ar');

        $userCnt = User::count();
        $ordersCnt = Cart::count();
        $totalIncome = Cart::where('payed', 1)->sum('bill');
        $inPreparationOrders = Cart::where('status', 'in preparation')->count();
        $gettingDeliveredOrders = Cart::where('status', 'getting delivered')->count();
        $deliveredOrders = Cart::where('status', 'delivered')->count();
        $refusedOrders = Cart::where('status', 'refused')->count();
        //another way to some pivot attributes in laravel, look totalMed in userStat
        $soldMedicines = Cart::where('payed', 1)->join('cart_medicine', 'id', '=', 'cart_medicine.cart_id')->sum('quantity');
        $inStockMedicines = Medicine::where('quantity', '!=', 0)->where('expirationDate', '>', now())->count();
        $inStockQuantity = Medicine::where('expirationDate', '>', now())->sum('quantity');

        //orderBy takes a function that sums the bill column from the carts table and it only takes the carts of the user by joining the tables with whereColumn
        $topUsers = UserResource::collection(User::orderBy(function ($query) {
            $query->selectRaw('sum(bill)')->from('carts')->whereColumn('users.id', 'carts.user_id');
        }, 'desc')->take(5)->get());

        $topMedicines = MedicineResource::collection(Medicine::orderBy('popularity', 'desc')->where('available', 1)->take(5)->get());

        //this code should not be written like that, but we did it as so because brand is not an independent entity in the database
        $topCompanies = [];
        $medicines = Medicine::where('available', 1)->get();
        foreach ($medicines as $medicine) {
            $topCompanies[$medicine->brand] = ($topCompanies[$medicine->brand] ?? 0) + $medicine->popularity;
        }
        arsort($topCompanies);
        //array slice takes the elements from the first index to the second exclusive, array keys returns an array with no values
        $topCompanies = array_slice(array_keys($topCompanies), 0, 5);

        //categories charts with percentages

        //first the percentage of each category for its in stock medicines
        $inStockCategoriesPercentages = [];
        $cats = Category::get();
        foreach ($cats as $cat) {
            if ($ar) $name = $cat->ar_name;
            else $name = $cat->name;
            $inStockCategoriesPercentages[$name] = (float) number_format((100 * $cat->medicines()->where('expirationDate', '>', now())->sum('quantity')) / $inStockQuantity, 2);
            if ($inStockCategoriesPercentages[$name] == 0) unset($inStockCategoriesPercentages[$name]);
        }

        //second the percentage of each category for its sold medicines
        $soldCategoriesPercentages = [];
        $carts = Cart::where('payed', 1)->get();
        foreach ($carts as $cart) {
            $medicines = $cart->medicines()->get();
            foreach ($medicines as $medicine) {
                if ($ar) $name = $medicine->category()->first()->ar_name;
                else $name = $medicine->category()->first()->name;
                $soldCategoriesPercentages[$name] = ($soldCategoriesPercentages[$name] ?? 0) + $medicine->pivot->quantity;
            }
        }
        foreach ($soldCategoriesPercentages as &$percentage) {
            $percentage = (float) number_format(($percentage * 100.0) / $soldMedicines, 2);
        }

        $data = [
            'users count' => $userCnt,
            'orders count' => $ordersCnt,
            'in preparation orders' => $inPreparationOrders,
            'getting delivered orders' => $gettingDeliveredOrders,
            'delivered orders' => $deliveredOrders,
            'refused orders' => $refusedOrders,
            'sold medicines count' => (int) $soldMedicines,
            'in stock medicines' => $inStockMedicines,
            'in stock medicines quantity' => (int) $inStockQuantity,
            'total income' => (int)$totalIncome,

            'top users' => $topUsers,
            'top medicines' => $topMedicines,
            'top companies' => $topCompanies,
            'categories percentages in stock' => $inStockCategoriesPercentages,
            'categories percentages for sold medicines' => $soldCategoriesPercentages,
        ];

        $data['message'] = 'statistics displayed successfully!';
        return $data;
    }

    public function adminCharts($year, $month)
    {
        //this function is for time charts
        if ($month == 0) {
            $carts = Cart::whereYear('created_at', $year)->where('payed', 1)->get();
            $IncomeByMonth = $carts->groupBy(function ($cart) {
                return $cart->created_at->format('m');
            })->map(function ($group) {
                return ($group->sum('bill')) / 1000000.0;
            })->toArray();
            $ProfitByMonth = $carts->groupBy(function ($cart) {
                return $cart->created_at->format('m');
            })->map(function ($group) {
                return ($group->sum('profit')) / 1000000.0;
            })->toArray(); // for sort

            for ($i = 1; $i < 10; $i++) {
                $IncomeByMonth['0' . strval($i)] = $IncomeByMonth['0' . (string)$i] ?? 0;
                $ProfitByMonth['0' . strval($i)] = $ProfitByMonth['0' . (string)$i] ?? 0;
            }
            for ($i = 10; $i < 13; $i++) {
                $IncomeByMonth[strval($i)] = $IncomeByMonth[strval($i)] ?? 0;
                $ProfitByMonth[strval($i)] = $ProfitByMonth[strval($i)] ?? 0;
            }

            //ksort to sort by key
            ksort($IncomeByMonth);
            ksort($ProfitByMonth);


            return response()->json([
                "income" => $IncomeByMonth,
                "profit" => $ProfitByMonth,
                'message' => 'year chart displayed successfully!'
            ]);
        } else {
            $carts = Cart::whereYear('created_at', $year)->whereMonth('created_at', $month)->where('payed', 1)->get();
            $IncomeByWeek = $carts->groupBy(function ($cart) {
                return $cart->created_at->weekOfMonth;
            })->map(function ($group) {
                return ($group->sum('bill')) / 1000000.0;
            });
            $ProfitByWeek = $carts->groupBy(function ($cart) {
                return $cart->created_at->weekOfMonth;
            })->map(function ($group) {
                return ($group->sum('profit')) / 1000000.0;
            });

            for ($i = 1; $i < 5; $i++) {
                $IncomeByWeek[(string)$i] = $IncomeByWeek[(string)$i] ?? 0;
                $ProfitByWeek[(string)$i] = $ProfitByWeek[(string)$i] ?? 0;
            }


            return response()->json([
                "income" => $IncomeByWeek,
                "profit" => $ProfitByWeek,
                'message' => 'month chart displayed successfully!'
            ]);
        }
    }


    public function adminWeekCharts($year, $week)
    {
        $carts = Cart::whereBetween('created_at', [Carbon::create($year)->week($week)->startOfWeek(), Carbon::create($year)->week($week)->endOfWeek()])->where('payed', 1)->get();
        $IncomeByDay = $carts->groupBy(function ($cart) {
            return $cart->created_at->format('N');
        })->map(function ($group) {
            return ($group->sum('bill')) / 1000000.0;
        });

        $ProfitByDay = $carts->groupBy(function ($cart) {
            return $cart->created_at->format('N');
        })->map(function ($group) {
            return ($group->sum('profit')) / 1000000.0;
        });

        for ($i = 1; $i < 8; $i++) {
            $IncomeByDay[(string)$i] = $IncomeByDay[(string)$i] ?? 0;
            $ProfitByDay[(string)$i] = $ProfitByDay[(string)$i] ?? 0;
        }
        return response()->json([
            "income" => $IncomeByDay,
            "profit" => $ProfitByDay,
            'message' => 'day chart displayed successfully!'
        ]);
    }
}
