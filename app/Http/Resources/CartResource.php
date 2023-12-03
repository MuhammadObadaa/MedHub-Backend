<?php

namespace App\Http\Resources;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        [
            'id' => $this->id,
            'bill' => $this->bill,
            'status' => $this->status,
            'ordered_at' =>date_format($this->created_at,'Y-m-d'),
            'medicines' => $this->when(Route::is('carts.show'),MedicineResource::collection($this->medicines()->get()))
        ];

    }
}
