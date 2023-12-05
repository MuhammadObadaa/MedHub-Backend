<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\CartResource;

class CartCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public static $wrap;

    public function toArray($request)
    {
        CartCollection::$wrap = CartResource::getWrap();
        return   CartResource::collection($this->collection);
    }
}
