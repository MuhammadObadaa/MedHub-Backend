<?php

use App\Http\Controllers\AuthController as auth;
use App\Http\Controllers\UserController as user;
use App\Http\Controllers\search;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//TODO: for Obadaa: follow the conventions
//TODO: for Obadaa: organize your routes

//---- Tokens_need routes
Route::group(['prefix' => '/user', 'as' => 'user.'], function () {
    Route::get('', [user::class, 'show'])->name('show');
    //TODO : IDA: switch with favor/id
    Route::post('favor/{medicineId}', [user::class, 'favor'])->name('favor');
    Route::post('unFavor/{medicineId}', [user::class, 'unFavor'])->name('unFavor');
    Route::post('addCart', [user::class, 'addCart'])->name('addCart');
    Route::post('logout', [auth::class, 'logout'])->name('logout');
    //TODO: password auth
    Route::group(['prefix' => '/change', 'as' => 'change'], function () {
        Route::post('Password', [user::class, 'changePassword'])->name('password');
        Route::post('PhName', [user::class, 'changePhName'])->name('phName');
        Route::post('PhLocation', [user::class, 'changePhLocation'])->name('phLocation');
        Route::post('Name', [user::class, 'changeName'])->name('name');
    });
});

Route::group(['prefix' => '/search/', 'as' => 'search.'], function () {
    Route::get('', [search::class, 'search'])->name('search');
    Route::get('{categoryId}', [search::class, 'searchInCategory'])->name('byCategory');
});
