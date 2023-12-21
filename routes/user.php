<?php

use App\Http\Controllers\AuthController as auth;
use App\Http\Controllers\UserController as user;
use App\Http\Controllers\MedicineController as medicine;
use App\Http\Controllers\CartController as cart;
use App\Http\Controllers\search;
use App\Http\Controllers\StatisticsController;
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

//TODO: for Obadaa: follow the conventions

//---- Tokens_need routes
Route::group(['prefix' => '/user', 'as' => 'user.'], function () { // tested
    Route::get('', [user::class, 'show'])->name('show');
    //TODO : IDA: switch with favor/id
    Route::post('/favor/{medicine}', [user::class, 'favor'])->name('favor');
    Route::post('/unFavor/{medicine}', [user::class, 'unFavor'])->name('unFavor');
    //5- returns a json file with favorite medicines of the user
    Route::get('/favorites', [medicine::class, 'favorites'])->name('favorites');
    Route::post('/logout', [auth::class, 'logout'])->name('logout');
    //TODO: password auth
    Route::put('/update', [user::class, 'update'])->name('update');
    //takes month and year and return an array that makes a chart, default is stat/0/0
    Route::get('/stat/{year}/{month}', [StatisticsController::class, 'statByDates'])->name('stat.dates');
    //returns some numbers
    Route::get('/stat', [StatisticsController::class, 'userStat'])->name('stat');
});
