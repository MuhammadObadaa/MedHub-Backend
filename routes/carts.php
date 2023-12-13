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
    //6-
    Route::post('', [cart::class, 'store'])->name('store');
    Route::get('', [cart::class, 'authList'])->name('list');
    //9-returns a json flow with all info of a specific cart including medicines
    Route::get('{cart}', [cart::class, 'show'])->name('show');
});
