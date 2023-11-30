<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnMedicineResource extends JsonResource
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
            'name' => $this->name,
            'scientificName' => $this->scientificName,
            'description' => $this->description,
            'brand'=> $this->brand,
            'quantity' => $this->quantitiy,
            'expirationDate' => $this->expirationDate,
            'price' => $this->price,
            'image' => $this->getImageURL()
        ];
    }
}
