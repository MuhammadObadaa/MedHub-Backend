<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineCollection;
use Illuminate\Http\Request;
use App\Models\Medicine;

class searchController extends Controller
{
    public function search()
    {
        $searched_text = request('searched_text');
        $by = request('by');

        //TODO: when creating ar_brand delete last condition
        if (request()->hasHeader('lang') && request()->header('lang') == 'ar' && $by != 'brand')
            $by = 'ar_' . $by;

        //case_sensitive search
        $medicine = Medicine::where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicine))->additional($message);
    }
    public function searchInCategory($category)
    {
        $searched_text = request('searched_text');
        $by = request('by');

        if (request()->hasHeader('lang') && request()->header('lang') == 'ar' && $by != 'brand')
            $by = 'ar_' . $by;

        $medicine = Medicine::where('category_id', $category);

        $medicine = $medicine->where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicine))->additional($message);
    }
}
