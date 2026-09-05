<?php

namespace App\Modules\ShopCart\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyPromocodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_uuid' => ['required', 'uuid', 'exists:shop_carts,uuid'],
            'code'      => ['required', 'string', 'max:64'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => t('shop.promocode'),
        ];
    }
}
