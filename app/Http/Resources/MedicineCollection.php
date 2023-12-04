<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\MedicineResource;

class MedicineCollection extends ResourceCollection
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
        MedicineCollection::$wrap = MedicineResource::getWrap();
        return   MedicineResource::collection($this->collection);
    }
}
