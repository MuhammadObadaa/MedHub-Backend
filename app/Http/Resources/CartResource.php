<?php

namespace App\Http\Resources;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public static $wrap = 'data';

    public static function getWrap()
    {
        return CartResource::$wrap;
    }

    public function toArray(Request $request): array
    {
        return
            [
                'id' => $this->id,
                'bill' => $this->bill,
                'status' => $this->status,
                'payment_status' => $this->payed,
                'user' => $this->when(Route::is('carts.list.*'),new UserResource(User::where('id','user_id')->first())),
                'ordered_at' => date_format($this->created_at, 'Y-m-d'),
                'medicines' => $this->when(Route::is('carts.show'), MedicineResource::collection($this->medicines()->get()))
            ];
    }
}
