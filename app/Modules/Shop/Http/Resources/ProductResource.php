<?php

namespace App\Modules\Shop\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'article'          => $this->article,
            'price'            => $this->price,
            'price2'           => $this->price2,
            'image'            => $this->image,
            'text'             => $this->text,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'brand'            => $this->whenLoaded('brand', fn () => [
                'id'    => $this->brand->id,
                'title' => $this->brand->title,
            ]),
        ];
    }
}
