<?php

namespace App\Http\Resources;

use App\Models\Medicine;
use App\Http\Middleware\AuthMiddleware;
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
        $arabicLang = $request->hasHeader('lang') && $request->header('lang') == 'ar';

        return [
            'id' => $this->id,
            'isFavorite' => AuthMiddleware::getUser()->hasFavored(Medicine::where('id', $this->id)->first()),
            'category' => $this->when($arabicLang, $this->category()->select('id', 'ar_name')->first(), $this->category()->select('id', 'name')->first()),
            'name' => $this->when($arabicLang, $this->ar_name, $this->name),
            'scientificName' => $this->when($arabicLang, $this->ar_scientificName, $this->scientificName),
            'description' => $this->when($arabicLang, $this->ar_description, $this->description),
            'brand' => $this->brand,
            'quantity' => $this->quantity,
            'expirationDate' => $this->expirationDate,
            'price' => $this->price,
            'image' => $this->getImageURL()
        ];
    }
}
