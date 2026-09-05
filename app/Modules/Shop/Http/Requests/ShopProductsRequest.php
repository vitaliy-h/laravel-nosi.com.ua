<?php

namespace App\Modules\Shop\Http\Requests;

use App\Modules\Shop\Enums\SortOptionEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopProductsRequest extends FormRequest
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
            'category_id' => ['nullable', 'integer', 'exists:shop_categories,id'],
            'sort'        => ['nullable', Rule::enum(SortOptionEnum::class)],

            // PrepareShopFilterQuery reads these straight off the request, and
            // accepts `brand` either as `1,2,3` or as `brand[]=1&brand[]=2`.
            'brand'      => $this->brandRules(),
            'brand.*'    => ['integer', 'min:1'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to'   => ['nullable', 'numeric', 'min:0', 'gte:price_from'],
            'search'     => ['nullable', 'string', 'max:255'],
            'page'       => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function brandRules(): array
    {
        return is_array($this->query('brand'))
            ? ['nullable', 'array']
            : ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'];
    }
}
