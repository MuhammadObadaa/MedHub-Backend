<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use Illuminate\Support\Facades\Route;


//prefix goes before every end point for example the delete method will look like this: /medicines/{medicine} where {medicine} is the id of the medicine
//don't bother by the name or anything, just look at the http method and the end point

Route::group(['prefix'=>'/medicines/','as'=>'medicines.'],function(){
    //receive a json file with attributes returns success message with json
    Route::post('',[MedicineController::class,'store'])->name('store');
    //return a list of all medicines and their attributes created ever as a json file with a message
    Route::get('',[MedicineController::class,'list'])->name('list');
    //returns a json file with all the medicine attributes with a message, the id of the medicine must be sent in the url
    Route::get('{medicine}',[MedicineController::class,'show'])->name('show');
    //the id of the medicine is {medicine}, it must be sent in the uri, then the medicien with correspondent id will be deleted, returns a message
    Route::delete('{medicine}',[MedicineController::class,'destroy'])->name('destroy');
    //receives a json file with updated attributes, and the id must be sent in the uri, the medecine will be updated, returns a message
    Route::put('{medicine}',[MedicineController::class,'update'])->name('update');
});

Route::group(['prefix' => '/categories/','as'=>'categories.'],function(){
    //id of category must be sent, returns a json file with all medicines of the category ever created with a message
    Route::get('{category}',[CategoryController::class,'show'])->name('show');
    //recieves a json with attributes of the category, returns a success message
    Route::post('',[CategoryController::class,'store'])->name('store');
    //id must be sent, returns a success message
    Route::delete('{category}',[CategoryController::class,'destroy'])->name('destroy');
    //returns a list of all categories with top 5 medicines in every category
    Route::get('',[CategoryController::class,'homePage'])->name('list');
    //receives the updated attributes of the category, update the category, id must be sent, returns a message
    Route::put('{category}',[CategoryController::class,'update'])->name('update');
});

?>
