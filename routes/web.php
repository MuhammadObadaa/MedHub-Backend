<?php

use App\Http\Controllers\AuthController as auth;
use App\Http\Controllers\AdminController as admin;
use App\Http\Controllers\UserController as user;
use App\Http\Controllers\searchController as search;
use App\Http\Controllers\StatisticsController;
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

//---- Authentication Routes
Route::post('/register', [auth::class, 'store'])->name('register');
Route::post('/login', [auth::class, 'login'])->name('login');

Route::get('/test', [admin::class, 'test']);

Route::get('admin/stat/{year}/{month}',[StatisticsController::class,'adminStatByDates']);
