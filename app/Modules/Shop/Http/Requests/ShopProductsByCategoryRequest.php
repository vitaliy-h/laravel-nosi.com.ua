<?php

namespace App\Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopProductsByCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /*todo product filters*/
        return [];
    }

    public function attributes(): array
    {
        return [];
    }
}
