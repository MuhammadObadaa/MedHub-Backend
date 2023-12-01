<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' =>$this->id,
            'catagory' => $this->when($request->header('lang')=='ar',$this->category()->select('id','ar_name')->get(),$this->category()->select('id','name')->get()),
            'name' => $this->when($request->header('lang')=='ar',$this->ar_name,$this->name),
            'scientificName' =>$this->when($request->header('lang')=='ar',$this->ar_scientificName,$this->scientificName),
            'description' =>$this->when($request->header('lang')=='ar',$this->ar_description,$this->description),
            'brand'=> $this->brand,
            'quantity' => $this->quantity,
            'expirationDate' => $this->expirationDate,
            'price' => $this->price,
            //'likes' => $this->favored()->count(),
            //'isLiked' => $this->isFavored(), //uncomment this when working with logged in users
            'image' => $this->getImageURL()
        ];
    }
}

?>
