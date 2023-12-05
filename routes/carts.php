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

Route::group(['prefix' => '/carts/', 'as' => 'carts.'], function () {
    //1-returns json file with all orders in preparation
    Route::get('prep', [cart::class, 'inPreparation'])->name('list.prep');
    //2-returns a json file with all getting delivered orders
    Route::get('getDel', [cart::class, 'gettingDelivered'])->name('list.getDel');
    //3-returns a json file with all delivered orders
    Route::get('del', [cart::class, 'delivered'])->name('list.del');
    //4-returns all orders of all users
    Route::get('', [cart::class, 'all'])->name('list.all');
    //5-returns a json file with the all carts info of the logged in user
    Route::get('user/auth', [cart::class, 'authList'])->name('auth');
    //6-returns cart list for a specific user
    Route::get('user/{user}', [cart::class, 'userList'])->name('user');
    //7-returns a json flow with all info of a specific cart including medicines
    Route::get('{cart}', [cart::class, 'show'])->name('show');
    //8-updates the status of the orders, receives a json file, and returns a message
    Route::put('{cart}', [cart::class, 'update'])->name('update');
});
