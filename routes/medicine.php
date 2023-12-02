<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use Illuminate\Support\Facades\Route;


//first: prefix goes before every route, for example in route number 2, the route will be like this
//api/medicines/{medicine}

//second: whenever we mention a variable between curly braces like this, {medicine} we mean the id of the medicine, for example:
//api/medicines/1

//third: all the follwoing routes will return a message additionally to what they must returns

//fourth: all the returned json fils are formatted according to the standard shapes (resoruces)

Route::group(['prefix'=>'/medicines/','as'=>'medicines.'],function(){
    //1-returns a json file containing the medicines according to popularity, formatted according to the resource
    Route::get('',[MedicineController::class,'list'])->name('list');
    //2-receives the id, returns a json file with medicine info
    Route::get('{medicine}',[MedicineController::class,'show'])->name('show');
    //3-returns a json file with top 10 medicines according to popularity
    Route::get('/top10',[MedicineController::class,'top10'])->name('top10');
    //4-returns a json file with 10 recently added medicines according to latency
    Route::get('recent10',[MedicineController::class,'recent10'])->name('recent10');
    //5- returns a json file with favourite medicines of the user
    Route::get('/user/favourites',[MedicineController::class,'favourites'])->name('user.favourites');
    //6-receives a json file with all medicine attributes, image is not manditory.
    Route::post('',[MedicineController::class,'store'])->name('store');
    //7-receives the id of the medicine in the url, delete the medicine from the database
    Route::delete('{medicine}',[MedicineController::class,'destroy'])->name('destroy');
    //8-receive a json file, with updated medicine attributes, and the id in the url, updates the medicine
    Route::put('{medicine}',[MedicineController::class,'update'])->name('update');
});

Route::group(['prefix' => '/categories/','as'=>'categories.'],function(){
    //1-returns a json file with all medicines under the category
    Route::get('{category}',[CategoryController::class,'show'])->name('show');
    //2- returns a json file with all categories and withotu any medicines
    Route::get('',[CategoryController::class,'list'])->name('list');
    //3-receives a json file with info of the category, store it in the database
    Route::post('',[CategoryController::class,'store'])->name('store');
    //4-receives the id of the category, delete it
    Route::delete('{category}',[CategoryController::class,'destroy'])->name('destroy');
    //receives the id of the category and a json file with updated info, updates the category
    Route::put('{category}',[CategoryController::class,'update'])->name('update');
});

?>
