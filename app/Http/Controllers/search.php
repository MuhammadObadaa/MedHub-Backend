<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineResource;
use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Category;

class search extends Controller
{
    public function search()
    {
        $searched_text = request('searched_text');
        $by = request('by');

        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $medicine = Medicine::where('ar_' . $by, $searched_text)->OrderBy('popularity', 'DESC')->get();
        else
            $medicine = Medicine::where($by, $searched_text)->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return MedicineResource::collection($medicine)->additional($message);
    }

    public function searchInCategory($category)
    {
        $searched_text = request('searched_text');
        $by = request('by');

        $medicine = Medicine::where('category_id', $category);

        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $medicine = Medicine::where('ar_' . $by, $searched_text)->OrderBy('popularity', 'DESC')->get();
        else
            $medicine = Medicine::where($by, $searched_text)->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return MedicineResource::collection($medicine)->additional($message);
    }
}
