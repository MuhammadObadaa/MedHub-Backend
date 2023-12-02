<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->id,
            'name' => $this->when($request->header('lang') == 'ar', $this->ar_name,$this->name),
            'medicines' => MedicineResource::collection($this->medicines5()->get())
        ];

    }
}