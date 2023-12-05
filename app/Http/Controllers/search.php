<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineCollection;
use Illuminate\Http\Request;
use App\Models\Medicine;

class search extends Controller
{
    public function search()
    {
        $searched_text = request('searched_text');
        $by = request('by');

        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $by = 'ar_' . $by;

        //case_sensitive search
        $medicine = Medicine::where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicine))->additional($message)->response()->setStatusCode(200);
    }

    public function searchInCategory($categoryId)
    {
        $searched_text = request('searched_text');
        $by = request('by');

        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $by = 'ar_' . $by;

        $medicine = Medicine::where('category_id', $categoryId);

        $medicine = $medicine->where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicine))->additional($message)->response()->setStatusCode(200);
    }
}
