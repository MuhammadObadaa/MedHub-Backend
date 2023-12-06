<?php

namespace App\Http\Resources;

use App\Models\Medicine;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;

class MedicineResource extends JsonResource
{

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'data';

    public static function getWrap(): string
    {
        //return self::$wrap;
        return MedicineResource::$wrap;
    }

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
            //TODO: don't send isFavorite when it's favorite list
            'isFavorite' => AuthMiddleware::getUser()->hasFavored(Medicine::where('id', $this->id)->first()),
            //'category' => $this->when(!Route::is('categories.list') && !Route::is('categories.show'),$this->when($request->header('lang')=='ar',$this->category()->select('id','ar_name')->get(),$this->category()->select('id','name')->get())),
            // TODO: fix ar_name in category
            //'category' => $this->when(!Route::is('categories.list'), $this->when($arabicLang, $this->category()->select('id', 'ar_name')->first(), $this->category()->select('id', 'name')->first())),
            'category' => $this->when(!Route::is('categories.list'), new CategoryResource($this->category()->first())),
            'name' => $this->when($arabicLang, $this->ar_name, $this->name),
            'scientificName' => $this->when($arabicLang, $this->ar_scientificName, $this->scientificName),
            'description' => $this->when($arabicLang, $this->ar_description, $this->description),
            'brand' => $this->brand,
            'quantity' => $this->quantity,
            //short if statement is necessary 'causes even if the route is not carts.* it checks the true arg
            'ordered_quantity' => $this->when(Route::is('carts.*'), $this->pivot ? $this->pivot->quantity : null),
            'expirationDate' => $this->expirationDate,
            'price' => $this->price,
            //'likes' => $this->favored()->count(),
            'image' => $this->getImageURL()
        ];
    }
}
