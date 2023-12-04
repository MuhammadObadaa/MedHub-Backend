<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\CategoryResource;

class CategoryCollection extends ResourceCollection
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
        CategoryCollection::$wrap = CategoryResource::getWrap();
        return   CategoryResource::collection($this->collection);
    }
}
