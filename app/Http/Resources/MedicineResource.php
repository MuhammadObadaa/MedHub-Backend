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
        $report = (Route::is('admin.report') || Route::is('user.report'));
        $stat = (Route::is('admin.stat') || Route::is('user.stat'));
        return [
            'id' => $this->id,
            //TODO: don't send isFavorite when it's favorite list
            //'isFavorite' => $this->when(!Route::is('user.favorites'),AuthMiddleware::getUser()->hasFavored(Medicine::where('id', $this->id)->first())),
            'isFavorite' => $this->when(!$report && !$stat,AuthMiddleware::getUser()->hasFavored(Medicine::where('id', $this->id)->first())),
            'category' => $this->when(!Route::is('categories.list'), new CategoryResource($this->category()->first())),
            'name' => $this->when($arabicLang, $this->ar_name, $this->name),
            'scientificName' => $this->when($arabicLang, $this->ar_scientificName, $this->scientificName),
            'description' => $this->when(!$report && !$stat,$this->when($arabicLang, $this->ar_description, $this->description)),
            'brand' => $this->when($arabicLang, $this->ar_brand, $this->brand),
            'quantity' => $this->when(!$report && !$stat,$this->quantity),
            //short if statement is necessary 'causes even if the route is not carts.* it checks the true arg
            'ordered_quantity' => $this->when(!$stat,$this->pivot ? $this->pivot->quantity : null),
            'expirationDate' => $this->when(!$report && !$stat,$this->expirationDate),
            'isExpired' => $this->when(!$report && !$stat,$this->when($this->expirationDate > now(), false, true)),
            'price' => $this->price,
            'profit' => $this->when(Route::is('admin.*'),$this->profit),
            'image' => $this->when(!$report && !$stat,$this->getImageURL())
        ];
    }
}
