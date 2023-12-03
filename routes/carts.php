<?php

use App\Http\Controllers\CartController;
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

Route::group(['prefix' => '/carts/','as' => 'carts.'],function(){
    Route::get('{cart}',[CartController::class,'show'])->name('show');
    Route::get('user/auth',[CartController::class,'authList'])->name('auth.list');
    Route::get('user/{user}',[CartController::class,'list'])->name('user.list');
});
