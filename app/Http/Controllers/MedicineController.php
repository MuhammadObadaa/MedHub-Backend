<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\MedicineCollection;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Resources\CartResource;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{

    //store function is used by the store man to add medicines to the database,
    //the validation takes process on the front-end,
    //front-end developer must send the category_id for every medicine created
    public function store()
    {
        $lang = $this->lang();

        $medicine = Medicine::create([
            'category_id' => request()->get('category_id'), //the id is sent for every medicine
            //'category_id' => Category::where('name',request()->get('categoryName'))->orWhere('ar-name',request()->get('name'))->first()->id
            'name' => request()->get('name'),
            'ar_name' => request()->get('ar_name'),
            'scientificName' => request()->get('scientificName'),
            'ar_scientificName' => request()->get('ar_scientificName'),
            'description' => request()->get('description'),
            'ar_description' => request()->get('ar_description'),
            'brand' => request()->get('brand'),
            'ar_brand' => request()->get('ar_brand'),
            'quantity' => request()->get('quantity'),
            'expirationDate' => request()->get('expirationDate'),
            'price' => request()->get('price'),
            'profit' => request()->get('profit'),
            'image' =>  request()->file('image')->store('app', 'public')
        ]);
        //$medicine->addMediaFromBase64(request()->get('image'))->usingFileName(time() . '.' . request()->get('image')->getClientOriginalExtension())->toMediaCollection('app','public');

        // $medicine->addMedia("download.jpg")->preservingOriginal()->toMediaCollection('medicines');

        $message['ar'] = 'تم إضافة الدواء بنجاح';
        $message['en'] = 'medicine added successfully';

        return response()->json(['message' => $message[$lang]]);
    }

    //list function is used by the pharmacist to browse all the medicines in general, with no specific category
    public function list()
    {
        $lang = $this->lang();
        $medicines = Medicine::OrderBy('popularity', 'DESC')->where('available', 1)->get();


        $message['ar'] = 'تم عرض الأدوية بنجاح';
        $message['en'] = 'medicines listed successfully!';

        $message = ['message' => $message[$lang]];

        return (new MedicineCollection($medicines))->additional($message);
        //return MedicineResource::collection($medicines)->additional($message)->response()->setStatusCode(200); this won't change collection name
    }

    //show is used by the pharmacist to see the details of a certain medicine
    public function show(Medicine $medicine)
    {
        $lang = $this->lang();

        $message['ar'] = 'تم عرض الدواء بنجاح';
        $message['en'] = 'medicine displayed successfully!';

        $message = ['message' =>  $message[$lang]];
        return (new MedicineResource($medicine))->additional($message);
    }

    public function showInfo($medicine)
    {

        $lang = $this->lang();
        $medicine = Medicine::with('category')->find($medicine);

        $message['ar'] = 'تم عرض الدواء بنجاح';
        $message['en'] = 'medicine info returned successfully';

        return response()->json([
            'medicine' => $medicine,
            'message' => $message[$lang]
        ]);
    }

    public function destroy(Medicine $medicine)
    {

        $lang = $this->lang();
        if ($medicine->popularity == 0) {
            Storage::disk('public')->delete($medicine->image);
            $medicine->delete();

            $message['ar'] = 'تم حذف الدواء بنجاح';
            $message['en'] = 'medicine deleted successfully!';

            return response()->json([
                'message' => $message[$lang]
            ]);
        } else {
            $medicine->available = 0;
            $medicine->save();

            $message['ar'] = 'الدواء مرتبط بالاحصائيات. لذلك سيتم جعله غير متوفر ';
            $message['en'] = 'medicine is linked to reports and statistics, so it is updated to be unavailable';

            return response()->json(['message' =>  $message[$lang]]);
        }
    }


    //updating the medicine is a tricky function, and it's related to the shape of the page on the front-end
    //if the front-end displayed a page with text-areas for all fields to update, and in the text-areas, the primary text for them
    //must be the same as the original information, then the shape of the update function must be as so
    //however if the front-end allowed customized editing, and that is, for every attribute of the medicine,
    //the storeMan can update one of them specifically, the update function will differ, and it must be coded with if statements
    public function update(Medicine $medicine)
    {

        $lang = $this->lang();
        $updated = [
            'category_id' => request()->get('category_id'), //the id is sent for every medicine
            'name' => request()->get('name'),
            'ar_name' => request()->get('ar_name'),
            'scientificName' => request()->get('scientificName'),
            'ar_scientificName' => request()->get('ar_scientificName'),
            'description' => request()->get('description'),
            'ar_description' => request()->get('ar_description'),
            'brand' => request()->get('brand'),
            'ar_brand' => request()->get('ar_brand'),
            'quantity' => request()->get('quantity'),
            'expirationDate' => request()->get('expirationDate'),
            'price' => request()->get('price'),
            'profit' => request()->get('profit'),
        ];

        $imageFile = '';
        if (request()->has('image')) {
            $validatedImage = Validator::make(request()->all(), [
                'image' => 'image'
            ]);
            if (!$validatedImage->fails()) {
                if ($medicine->image != '') {
                    Storage::disk('public')->delete($medicine->image);
                }
                $imageFile = request()->file('image')->store('app', 'public');
                $updated['image'] = $imageFile;
            }
        }

        $medicine->update($updated);

        $message['ar'] = 'تم تحديث الدواء بنجاح';
        $message['en'] = 'medicine updated successfully!';

        return response()->json(['message' => $message[$lang]]);
    }


    //returns top 10 medicines
    public function top10()
    {
        $lang = $this->lang();
        $medicines = Medicine::OrderBy('popularity', 'DESC')->where('available', 1)->take(10)->get();


        $message['ar'] = 'تم عرض أفضل 10 أدوية بنجاح';
        $message['en'] = 'top 10 medicines displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new MedicineCollection($medicines))->additional($message);
    }

    //returns recent 10 medicines
    public function recent10()
    {

        $lang = $this->lang();
        $medicines = Medicine::latest()->where('available', 1)->take(10)->get();

        $message['ar'] = 'تم عرض آخر 10 أدوية بنجاح';
        $message['en'] = 'recent 10 medicines displayed successfully!';

        $message = ['message' => $message[$lang]];
        return (new MedicineCollection($medicines))->additional($message);
    }

    public function favorites()
    {
        $lang = $this->lang();

        $medicines = AuthMiddleware::getUser()->favors()->get();

        $message['ar'] = 'تم عرض  الأدوية المفضلة بنجاح';
        $message['en'] = 'favored medicines displayed successfully!';

        $message = ['message' => $message[$lang]];

        return (new MedicineCollection($medicines))->additional($message);
    }
}
