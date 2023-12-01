<?php

use App\Http\Controllers\MedicineController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>'/medicines/','as'=>'medicines.'],function(){
    Route::post('',[MedicineController::class,'store'])->name('store');
    Route::get('list',[MedicineController::class,'list'])->name('list');
    Route::get('{medicine}',[MedicineController::class,'show'])->name('show');
    Route::get('list/{category}',[MedicineController::class,'listCategory'])->name('listCategory');
    //NOTE: when the category arg was out of the bounds it won't return an exception from mysql .. it's just return 404 error
});

?>
