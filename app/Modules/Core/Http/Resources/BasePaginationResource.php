<?php

namespace App\Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasePaginationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total'        => $this->total(),
            'per_page'     => $this->perPage(),
            'current_page' => $this->currentPage(),
            'last_page'    => $this->lastPage(),
            'from'         => $this->firstItem(),
            'to'           => $this->lastItem(),
        ];
    }
}
