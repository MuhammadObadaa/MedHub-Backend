<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "phoneNumber" => $this->phoneNumber,
            "pharmacyName" => $this->pharmacyName,
            "pharmacyLocation" => $this->pharmacyLocation,
            "image" => $this->getImageURL()
        ];
        //return parent::toArray($request);
    }
}
