<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineCollection;
use Illuminate\Http\Request;
use App\Models\Medicine;

class searchController extends Controller
{
    public function search($searched_text,$by)
    {
        //TODO: when creating ar_brand delete last condition
        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $by = 'ar_' . $by;

        //case_sensitive search
        $medicine = Medicine::where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->where('available',1)->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicine))->additional($message);
    }
    public function searchInCategory($category,$searched_text,$by)
    {
        if (request()->hasHeader('lang') && request()->header('lang') == 'ar')
            $by = 'ar_' . $by;

        $medicine = Medicine::where('category_id', $category)->where('available',1);

        $medicine = $medicine->where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicine))->additional($message);
    }
}
