<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $report = Route::is('admin.report');
        $stat = Route::is('admin.stat');

        return [
            "id" => $this->id,
            "name" => $this->name,
            "phoneNumber" => $this->phoneNumber,
            "pharmacyName" => $this->pharmacyName,
            "pharmacyLocation" => $this->pharmacyLocation,
            "image" => $this->when(!$stat && !$report,$this->getImageURL()),
            "all time payment" => $this->when($stat,(int)$this->carts()->sum('bill'))
        ];
        //return parent::toArray($request);
    }
}
