<?php

use App\Http\Controllers\CartController as cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => '/carts/', 'as' => 'carts.'], function () { //tested
    //1-returns json file with all orders in preparation
    Route::get('prep', [cart::class, 'inPreparation'])->name('list.prep');
    //2-returns a json file with all getting delivered orders
    Route::get('getDel', [cart::class, 'gettingDelivered'])->name('list.getDel');
    //3-returns a json file with all delivered orders
    Route::get('del', [cart::class, 'delivered'])->name('list.del');
    //4-returns a json file with all refused orders
    Route::get('refused', [cart::class, 'refused'])->name('list.ref');
    //5-returns all orders of all users
    Route::get('', [cart::class, 'all'])->name('list.all');
    //6-
    Route::post('', [cart::class, 'store'])->name('store');
    //9-returns a json flow with all info of a specific cart including medicines
    Route::get('{cart}', [cart::class, 'show'])->name('show');
});
