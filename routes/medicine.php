<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>'/medicines/','as'=>'medicines.'],function(){
    Route::post('',[MedicineController::class,'store'])->name('store');
    Route::get('',[MedicineController::class,'list'])->name('list');
    Route::get('{medicine}',[MedicineController::class,'show'])->name('show');
    Route::delete('{medicine}',[MedicineController::class,'destroy'])->name('destroy');
    Route::put('{medicine}',[MedicineController::class,'update'])->name('update');
});

Route::group(['prefix' => '/categories/','as'=>'categories.'],function(){
    Route::get('{category}',[CategoryController::class,'show'])->name('show');
    Route::post('',[CategoryController::class,'store'])->name('store');
    Route::delete('{category}',[CategoryController::class,'destroy'])->name('destroy');
    Route::get('',[CategoryController::class,'homePage'])->name('list');
    Route::put('{category}',[CategoryController::class,'update'])->name('update');
});

?>
