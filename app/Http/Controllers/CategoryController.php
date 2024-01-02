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
        $lang = $this->lang();
        $medicines = $category->medicines()->get();

        $message['ar'] = 'تم عرض جميع الأدوية التي تنتمي للصنف بنجاح';
        $message['en'] =  'medicines listed successfully under a category!';

        $message = ['message' => $message[$lang]];
        return MedicineResource::collection($medicines)->additional($message);
    }

    public function showInfo(Category $category)
    {
        $lang = $this->lang();

        $message['ar'] = 'تم عرض الصنف بنجاح';
        $message['en'] =  'category returned successfully!';

        return response()->json([
            'message' => $message[$lang],
            'category' => $category
        ]);
    }

    //used by the storeMan to create a category
    public function store()
    {
        $lang = $this->lang();
        Category::create([
            'name' => request()->get('name'),
            'ar_name' => request()->get('ar_name')
        ]);

        $message['ar'] = 'تم إنشاء الصنف بنجاح';
        $message['en'] =  'category created successfully!';
        return response()->json([
            'message' => $message[$lang]
        ], 201);
    }

    //used by storeMan to delete a category and all the medicines under it, may not be used by the front-end developer
    public function destroy(Category $category)
    {
        $lang = $this->lang();

        if ($category->hasMany(Medicine::class, 'category_id', 'id')->count() != 0) {
            $message['ar'] = 'لا يمكن حذف الصنف نظرا لوجود أدوية لهذا الصنف';
            $message['en'] = 'category has medicines under it, cannot be deleted';
            return response()->json(['message' => $message[$lang]], 400);
        }
        $category->delete();

        $message['ar'] = 'تم حذف التصنيف بنجاخ';
        $message['en'] = 'category deleted successfully';

        return response()->json(['message' => $message[$lang]]);
    }

    //this function return to the home page all the categories with the top 5 medicines in each category
    public function list()
    {
        $lang = $this->lang();
        $categories = Category::latest()->get();
        $message['ar'] = 'تم عرض جميع التصانيف بنجاح';
        $message['en'] = 'categories listed successfully!';
        $message = ['message' => $message[$lang]];
        return (new CategoryCollection($categories))->additional($message);
    }

    public function update(Category $category)
    {
        $lang = $this->lang();
        
        $updated = [
            'name' => request()->get('name'),
            'ar_name' => request()->get('ar_name')
        ];
        $category->update($updated);

        $message['ar'] = 'تم تحديث التصنيف';
        $message['en'] = 'category updated successfully!';

        return response()->json(['message' => $message[$lang]]);
    }
}
