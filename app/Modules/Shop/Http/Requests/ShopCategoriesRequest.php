<?php

namespace App\Modules\Shop\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShopCategoriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'has_image'          => ['nullable', 'boolean'],
            'only_with_products' => ['nullable', 'boolean'],
            'random'             => ['nullable', 'boolean'],
            'limit'              => ['nullable', 'integer', 'min:1'],
        ];
    }
}
