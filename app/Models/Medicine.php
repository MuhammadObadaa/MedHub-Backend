<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'ar_name',
        'scientificName',
        'ar_scientificName',
        'brand',
        'ar_brand',
        'description',
        'ar_description',
        'quantity',
        'expirationDate',
        'price',
        'popularity',
        'image',
        'profit',
        'category_id'
    ];


    // //this tells the model, for every query u do, get the category info with it
    // protected $with = [
    //     'category'
    // ];

    // //this tells the model, whenever u query the info of the medicines, get the count of users
    // //who added them to the favorites list
    // protected $withCount = [
    //     'favored'
    // ];

    //return the category of the medicine
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    //returns the user that has added the medicine to the favorites
    public function favored()
    {
        return $this->belongsToMany(User::class, 'medicine_user', 'medicine_id', 'user_id')->withTimestamps();
    }
    //return the carts of the user that the medicine belongs to
    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_medicine', 'medicine_id', 'cart_id')->withPivot('quantity')->withTimestamps();
    }

    public function isFavored()
    {
        return $this->favored()->where('user_id', auth()->id())->exists();
    }

    public function getImageURL()
    {
        if ($this->image != "" && $this->image != null) {
            //first arg is the path of the file relative to the public directory
            return url('storage', $this->image);
        }
        return '';
    }
}
