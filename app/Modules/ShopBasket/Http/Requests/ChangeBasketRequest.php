<?php

namespace App\Modules\ShopBasket\Http\Requests;

use App\Modules\ShopBasket\Enums\BasketAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeBasketRequest extends FormRequest
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
            'id' => ['required', 'integer', 'exists:shop_products,id'],
            'count' => ['required', 'integer', 'min:0'],
            'action' => ['required', Rule::enum(BasketAction::class)],
            'basket_uuid' => ['nullable', 'uuid', 'exists:shop_baskets,uuid'],
        ];
    }
}
