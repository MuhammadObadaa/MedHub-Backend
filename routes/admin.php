<?php

use App\Http\Controllers\CategoryController as category;
use App\Http\Controllers\MedicineController as medicine;
use App\Http\Controllers\AdminController as admin;
use App\Http\Controllers\CartController as cart;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/medicines/', 'as' => 'medicines.'], function () { // need to tested
    //1-receives a json file with all medicine attributes, image is not manditory.
    Route::post('', [medicine::class, 'store'])->name('store');
    //2-receives the id of the medicine in the url, delete the medicine from the database
    Route::delete('{medicine}', [medicine::class, 'destroy'])->name('destroy');
    //3-receive a json file, with updated medicine attributes, and the id in the url, updates the medicine
    Route::put('{medicine}', [medicine::class, 'update'])->name('update');
});


Route::group(['prefix' => '/categories/', 'as' => 'categories.'], function () { // need to be tested
    //1-receives the id of the category, delete it
    Route::delete('{category}', [category::class, 'destroy'])->name('destroy');
    //2-receives the id of the category and a json file with updated info, updates the category
    Route::put('{category}', [category::class, 'update'])->name('update');
});

//8-returns cart list for a specific user

Route::group(['prefix' => '/carts/', 'as' => 'carts.'], function () { //tested
    Route::get('user/{user}', [cart::class, 'userList'])->name('user');
    //1-updates the status of the orders, receives a json file, and returns a message
    Route::put('{cart}', [cart::class, 'update'])->name('update');
});
