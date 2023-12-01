<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineResource;
use Illuminate\Http\Request;
use App\Models\Medicine;

class search extends Controller
{
    public function search()
    {
        $searched_text = request('searched_text');

        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $medicine = Medicine::where('ar_name', $searched_text)->OrderBy('popularity', 'DESC')->get();
        else
            $medicine = Medicine::where('name', $searched_text)->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return response()->json(MedicineResource::collection($medicine)->additional($message), 200);
    }
}
