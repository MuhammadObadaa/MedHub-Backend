<?php

use App\Http\Controllers\AuthController as auth;
use App\Http\Controllers\ReportsController as Reports;
use App\Http\Controllers\MedicineController;
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
