<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicineResource;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{

    //store function is used by the storeman to add medicines to the database,
    //the validation takes process on the front-end,
    //front-end developer must send the category_id for every medicine created
    public function store(){
        //TODO: make some columns unique in the database, and validate them
        $imageFile = '';
        if(request()->has('image')){
            $validatedImage = Validator::make(request()->get('image'),[
                'image'=>'image'
            ]);
            if($validatedImage->fails()){
                return response()->json([
                    'message'=>'unvalid image file'
                ]);
            }
            else{
                $imageFile = request()->file('image')->store('app','public');
            }
        }

        Medicine::create([
            'category_id' => request()->get('category_id'), //the id is sent for every medicine
            //'category_id' => Category::where('name',request()->get('categoryName'))->orWhere('ar-name',request()->get('name'))->first()->id
            'name' => request()->get('name'),
            'ar-name' => request()->get('ar-name'),
            'scientificName' => request()->get('name'),
            'ar-scientificName' => request()->get('name'),
            'description' => request()->get('name'),
            'ar-description' => request()->get('name'),
            'brand'=>request()->get('brand'),
            'quantity' => request()->get('quantity'),
            'expirationDate' => request()->get('expirationDate'),
            'price' => request()->get('price'),
            'image' => $imageFile
        ]);

        return response()->json([
            'message'=>'medicine added successfully',
            'status' => 200
        ]);
    }

    //list function is used by the farmacist to browse all the medicines in general, with no specfic category
    public function list(){
        $medicines = Medicine::OrderBy('popularity','DESC')->get();

        $message = [
            'message'=>'medicines listed successfully!',
            'status' => 200
        ];
        return MedicineResource::collection($medicines)->additional($message);
    }

    //show is used by the pharamcist to see the details of a certain medicine
    public function show(Medicine $medicine){
        $message = [
            'message' => 'medicine displayed successfully!',
            'status' => 200
        ];
        return (new MedicineResource($medicine))->additional($message);
    }

    public function destroy(Medicine $medicine){
        $medicine->delete();
    }

}
