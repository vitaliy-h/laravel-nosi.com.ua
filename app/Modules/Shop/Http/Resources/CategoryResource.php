<?php

namespace App\Modules\Shop\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'image'            => $this->image,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'children'         => self::collection(
                $this->relationLoaded('children') ? $this->children : $this->children()->visible()->get()
            ),
        ];
    }
}
