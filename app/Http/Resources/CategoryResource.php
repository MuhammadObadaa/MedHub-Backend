<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public static $wrap = 'data';

    public static function getWrap(): string
    {
        return CategoryResource::$wrap;
    }
    public function toArray(Request $request): array
    {

        $arabicLang = $request->hasHeader('lang') && $request->header('lang') == 'ar' && !Route::is('*.pdf');
        return [
            'id' => $this->id,
            'name' => $this->when($arabicLang, $this->ar_name, $this->name),
        ];
    }
}
