<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{

    //create function is used by the storeman to add medicines to the database,
    //the validation takes process on the front-end,
    //front-end developer must send the category_id for every medicine created
    public function store(){
        //TODO make some columns unique in the database, and validate them

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
            'message'=>'medicine added successfully'
        ]);
    }
}
