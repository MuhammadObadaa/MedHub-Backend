<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use App\Models\Medicine;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>'/medicines/','as'=>'medicines.'],function(){
    Route::post('',[MedicineController::class,'store'])->name('store');
    Route::get('list',[MedicineController::class,'list'])->name('list');
    Route::get('{medicine}',[MedicineController::class,'show'])->name('show');
    Route::delete('medicine',[MedicineController::class,'destroy'])->name('destroy');
});

Route::group(['prefix' => '/categories/','as'=>'categories.'],function(){
    Route::get('{category}',[CategoryController::class,'show'])->name('show');
    Route::post('',[CategoryController::class,'store'])->name('store');
    Route::delete('{category}',[CategoryController::class,'destroy'])->name('destroy');
});

?>
