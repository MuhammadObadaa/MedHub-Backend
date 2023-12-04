<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\search;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//TODO: for Obadaa: follow the conventions
//TODO: for Obadaa: organize your routes

//---- Authentication Routes
Route::post('/register', [AuthController::class, 'create'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//---- Tokens_need routes
Route::group(['middleware' => 'user'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/favor/{medicineId}', [UserController::class, 'favor'])->name('favor');
    Route::post('/unFavor/{medicineId}', [UserController::class, 'unFavor'])->name('unFavor');
    Route::post('/changePassword', [UserController::class, 'changePassword'])->name('changePassword')->name('changePassword'); //->middleware('user');
    Route::get('/profile', [UserController::class, 'show'])->name('profile');
    Route::post('/addCart', [UserController::class, 'addCart'])->name('addCart');
    Route::get('/search', [search::class, 'search'])->name('searchByName');
    Route::get('/search/{category}', [search::class, 'searchInCategory'])->name('searchByNameInCategory');
});
