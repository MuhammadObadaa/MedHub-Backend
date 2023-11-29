<?php

use App\Http\Controllers\MedicineController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>'/medicines/','as'=>'medicines.'],function(){
    Route::post('',[MedicineController::class,'store'])->name('store');
});


?>
