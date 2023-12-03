<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bill',
        'status'
    ];

    // //this tells the model, whenever u fetch the info of a cart, get all the medicines
    // //that was in it.
    // protected $with = [
    //     'medicines'
    // ];

    // //this tells the model, whenever you fetch the cart, get the count of medicines in it
    // protected $withCount = [
    //     'medicines'
    // ];

    //returns the owner of the cart
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    //returns the medicines that are listed in the cart which the user sent
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'cart_medicine', 'cart_id', 'medicine_id')->withTimestamps();
    }
}
