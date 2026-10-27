<?php

namespace App\Modules\ShopCart\Http\Requests;

use App\Modules\ShopCart\Enums\CartAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeCartRequest extends FormRequest
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
            'id'        => ['required', 'integer', 'exists:shop_products,id'],
            'pack'      => ['nullable', 'integer', 'min:1'],
            'count'     => ['required', 'integer', 'min:0'],
            'action'    => ['required', Rule::enum(CartAction::class)],
            'cart_uuid' => ['nullable', 'uuid', 'exists:shop_carts,uuid'],
        ];
    }
}
