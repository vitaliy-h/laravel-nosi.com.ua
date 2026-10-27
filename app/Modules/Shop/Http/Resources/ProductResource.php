<?php

namespace App\Modules\Shop\Http\Resources;

use App\Modules\Shop\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'slug'    => $this->slug,
            'article' => $this->article,
            'price'   => $this->price,
            // the sets the product is sold in, smallest first; `price` is the smallest one's
            'packs'            => Product::normalizePacks($this->packs),
            'is_pack'          => (bool) $this->is_pack,
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
