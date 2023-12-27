<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\MedicineCollection;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie;

class MedicineController extends Controller
{

    //store function is used by the store man to add medicines to the database,
    //the validation takes process on the front-end,
    //front-end developer must send the category_id for every medicine created
    public function store()
    {

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

        return response()->json(['message' => 'medicine added successfully']);
    }

    //list function is used by the pharmacist to browse all the medicines in general, with no specific category
    public function list()
    {
        $medicines = Medicine::OrderBy('popularity', 'DESC')->where('available',1)->get();

        $message = ['message' => 'medicines listed successfully!'];

        return (new MedicineCollection($medicines))->additional($message);
        //return MedicineResource::collection($medicines)->additional($message)->response()->setStatusCode(200); this won't change collection name
    }

    //show is used by the pharmacist to see the details of a certain medicine
    public function show(Medicine $medicine)
    {
        $message = ['message' => 'medicine displayed successfully!'];
        return (new MedicineResource($medicine))->additional($message);
    }

    public function showInfo(Medicine $medicine){
        return response()->json([
            'message'=>'medicine info returned succesffully',
            'medicine' => $medicine
        ]);
    }

    public function destroy(Medicine $medicine)
    {
        if($medicine->popularity == 0){
            Storage::disk('public')->delete($medicine->image);
            $medicine->delete();
            return response()->json([
                'message' => 'medicine deleted successfully!'
            ]);
        }
        else{
            $medicine->available = 0;
            $medicine->save();
            return response()->json([
                'message' => 'medicine is linked to reports and statistics, so it is updated to be unavailable'
            ]);
        }
    }


    //updating the medicine is a tricky function, and it's related to the shape of the page on the front-end
    //if the front-end displayed a page with text-areas for all fields to update, and in the text-areas, the primary text for them
    //must be the same as the original information, then the shape of the update function must be as so
    //however if the front-end allowed customized editing, and that is, for every attribute of the medicine,
    //the storeMan can update one of them specifically, the update function will differ, and it must be coded with if statements
    public function update(Medicine $medicine)
    {
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

        if (request()->get('image') != '') {
            if ($medicine->image != '') {
                Storage::disk('public')->delete($medicine->image);
            }
            $imageFile = request()->file('image')->store('app', 'public');
            $updated['image'] = $imageFile;
        }

        $medicine->update($updated);
        return response()->json([
            'message' => 'medicine updated successfully!',
        ]);
    }


    //returns top 10 medicines
    public function top10()
    {
        $medicines = Medicine::OrderBy('popularity', 'DESC')->where('available',1)->take(10)->get();
        $message = ['message' => 'top 10 medicines displayed successfully!'];
        return (new MedicineCollection($medicines))->additional($message);
    }

    //returns recent 10 medicines
    public function recent10()
    {
        $medicines = Medicine::latest()->where('available',1)->take(10)->get();
        $message = [
            'message' => 'recent 10 medicines displayed successfully!'
        ];
        return (new MedicineCollection($medicines))->additional($message);
    }

    public function favorites()
    {
        $medicines = AuthMiddleware::getUser()->favors()->get();
        $message = ['message' => 'favored medicines displayed successfully!'];

        return (new MedicineCollection($medicines))->additional($message);
    }
}
