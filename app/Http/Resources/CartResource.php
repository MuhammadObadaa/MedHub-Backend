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
        $report = (Route::is('admin.report') || Route::is('user.report'));
        $carts = Route::is('carts.show') || Route::is('carts.list.*');
        return
            [
                'id' => $this->id,
                'bill' => $this->bill,
                'profit' => $this->when($report,$this->profit),
                'status' => $this->status,
                'payment_status' => $this->payed,
                'user' => $this->when( $carts || Route::is('admin.report'),new UserResource($this->user()->first())),
                'ordered_at' => date_format($this->created_at, 'Y-m-d'),
                'received_at' => $this->when($this->status == 'delivered',date_format($this->updated_at,'Y-m-d')),
                'medicines' => $this->when(Route::is('carts.show') || $report , MedicineResource::collection($this->medicines()->get()))
            ];
    }
}
