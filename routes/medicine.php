<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController as medicine;
use App\Http\Controllers\search;
use Illuminate\Support\Facades\Route;


//first: prefix goes before every route, for example in route number 2, the route will be like this
//api/medicines/{medicine}

//second: whenever we mention a variable between curly braces like this, {medicine} we mean the id of the medicine, for example:
//api/medicines/1

//third: all the following routes will return a message additionally to what they must returns

//fourth: all the returned json fils are formatted according to the standard shapes (resources)

Route::group(['prefix' => '/medicines/', 'as' => 'medicines.'], function () { // tested except admin actions
    //1-returns a json file containing the medicines according to popularity, formatted according to the resource
    Route::get('', [medicine::class, 'list'])->name('list');
    //2-returns a json file with top 10 medicines according to popularity
    Route::get('/top10', [medicine::class, 'top10'])->name('top10');
    //3-returns a json file with 10 recently added medicines according to latency
    Route::get('recent10', [medicine::class, 'recent10'])->name('recent10');
    //4-receives a json file with all medicine attributes, image is not manditory.
    Route::post('', [medicine::class, 'store'])->name('store');
    //5-receives the id, returns a json file with medicine info
    //TODO: admin actions:
    Route::get('{medicine}', [medicine::class, 'show'])->name('show');
    //6-receives the id of the medicine in the url, delete the medicine from the database
    Route::delete('{medicine}', [medicine::class, 'destroy'])->name('destroy');
    //7-receive a json file, with updated medicine attributes, and the id in the url, updates the medicine
    Route::put('{medicine}', [medicine::class, 'update'])->name('update');
});


Route::group(['prefix' => '/categories/', 'as' => 'categories.'], function () {
    //2- returns a json file with all categories and without any medicines
    Route::get('', [CategoryController::class, 'homePage'])->name('list');
    //3-receives a json file with info of the category, store it in the database
    Route::post('', [CategoryController::class, 'store'])->name('store');
    //1-returns a json file with all medicines under the category
    Route::get('{category}', [CategoryController::class, 'show'])->name('show');
    //4-receives the id of the category, delete it
    Route::delete('{category}', [CategoryController::class, 'destroy'])->name('destroy');
    //receives the id of the category and a json file with updated info, updates the category
    Route::put('{category}', [CategoryController::class, 'update'])->name('update');
});


Route::group(['prefix' => '/search/', 'as' => 'search.'], function () {//TODO: improve search
    Route::get('', [search::class, 'search'])->name('search');
    Route::get('{categoryId}', [search::class, 'searchInCategory'])->name('byCategory');
});
