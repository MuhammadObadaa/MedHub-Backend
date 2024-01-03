<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineCollection;
use Illuminate\Http\Request;
use App\Models\Medicine;

class searchController extends Controller
{
    public function search($searched_text, $by)
    {
        $lang = $this->lang();

        if ($this->lang() == 'ar')
            $by = 'ar_' . $by;

        //case_sensitive search
        $medicine = Medicine::where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->where('available', 1)->get();

        $message['ar'] = 'تم عرض الأدوية بنجاح';
        $message['en'] = 'medicines listed successfully!';

        $message = ['message' => $message[$lang]];

        return (new MedicineCollection($medicine))->additional($message);
    }
    public function searchInCategory($category, $searched_text, $by)
    {
        $lang = $this->lang();

        if ($this->lang() == 'ar')
            $by = 'ar_' . $by;

        $medicine = Medicine::where('category_id', $category)->where('available', 1);

        $medicine = $medicine->where($by, 'like', '%' . $searched_text . '%')->OrderBy('popularity', 'DESC')->get();

        $message['ar'] = 'تم عرض الأدوية بنجاح';
        $message['en'] = 'medicines listed successfully!';

        $message = ['message' => $message[$lang]];

        return (new MedicineCollection($medicine))->additional($message);
    }
}
