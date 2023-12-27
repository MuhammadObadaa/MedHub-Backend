<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;
use Illuminate\Http\Request;
use App\Http\Resources\MedicineResource;
use App\Models\Category;
use App\Models\Medicine;

//I found the use of category controller in organizing the routes and work generally
class CategoryController extends Controller
{

    //show function used to display the medicines under certain category in browse page
    public function show(Category $category)
    {
        $medicines = $category->medicines()->get();
        $message = [
            'message' => 'medicines listed successfully under a category!'
        ];
        return MedicineResource::collection($medicines)->additional($message);
    }

    public function showInfo(Category $category){
        return response()->json([
            'message'=>'category returned successfully!',
            'category' => $category
        ]);
    }

    //used by the storeMan to create a category
    public function store()
    {
        Category::create([
            'name' => request()->get('name'),
            'ar_name' => request()->get('ar_name')
        ]);
        return response()->json([
            'message' => 'category created successfully!'
        ], 201);
    }

    //used by storeMan to delete a category and all the medicines under it, may not be used by the front-end developer
    public function destroy(Category $category)
    {
        if($category->hasMany(Medicine::class,'category_id','id')->count() != 0){
            return response()->json([
                'message' => 'category has medicines under it, cannot be deleted'
            ],400);
        }
        $category->delete();
        return response()->json([
            'message' => 'category deleted successfully'
        ]);
    }

    //this function return to the home page all the categories with the top 5 medicines in each category
    public function list()
    {
        $categories = Category::latest()->get();
        $message = ['message' => 'categories listed successfully!'];
        return (new CategoryCollection($categories))->additional($message);
    }

    public function update(Category $category)
    {
        $updated = [
            'name' => request()->get('name'),
            'ar_name' => request()->get('ar_name')
        ];
        $category->update($updated);

        return response()->json(['message' => 'updated the category successfully!']);
    }
}
