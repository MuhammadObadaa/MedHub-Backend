<?php

use App\Http\Controllers\CategoryController as category;
use App\Http\Controllers\MedicineController as medicine;
use App\Http\Controllers\UserController as user;
use App\Http\Controllers\AdminController as admin;
use App\Http\Controllers\CartController as cart;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/medicines/', 'as' => 'medicines.'], function () { // need to tested
    //1-receives a json file with all medicine attributes, image is not manditory.
    Route::post('', [medicine::class, 'store'])->name('store');

    Route::get('{medicine}',[medicine::class,'showInfo'])->name('admin.show');
    //2-receives the id of the medicine in the url, delete the medicine from the database
    Route::delete('{medicine}', [medicine::class, 'destroy'])->name('destroy');
    //3-receive a json file, with updated medicine attributes, and the id in the url, updates the medicine
    Route::post('{medicine}', [medicine::class, 'update'])->name('update');
});

Route::group(['prefix' => '/users/', 'as' => 'users.'], function () {
    Route::get('', [user::class, 'list'])->name('list');
    Route::get('{user}', [user::class, 'showUser'])->name('show');
});

Route::group(['prefix' => '/categories/', 'as' => 'categories.'], function () { // need to be tested
    
    Route::post('', [cart::class, 'store'])->name('store');
    //1-receives the id of the category, delete it
    Route::delete('{category}', [category::class, 'destroy'])->name('destroy');
    //2-receives the id of the category and a json file with updated info, updates the category
    Route::post('{category}', [category::class, 'update'])->name('update');
});

//8-returns cart list for a specific user

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
    Route::get('user/{user}', [cart::class, 'userList'])->name('user');
    //7-updates the status of the orders, receives a json file, and returns a message
    Route::post('pay/{cart}', [admin::class, 'pay'])->name('paidCart');

    Route::post('{cart}', [admin::class, 'update'])->name('update');
});

Route::group(['as' => 'admin.'], function () {

    Route::get('/stat', [StatisticsController::class, 'adminStat'])->name('stat');

    Route::get('/charts/{year}/{month}', [StatisticsController::class, 'adminCharts'])->name('charts');

    Route::get('/weekcharts/{year}/{week}', [StatisticsController::class, 'adminWeekCharts'])->name('weekcharts');

    Route::get('/report/{year1}/{month1}/{day1}/{year2}/{month2}/{day2}', [ReportsController::class, 'adminReport'])->name('report');
    Route::get('/pdf/{year1}/{month1}/{day1}/{year2}/{month2}/{day2}', [ReportsController::class, 'pdfAdminReport'])->name('pdf');
});
