<?php

namespace App\Modules\ShopOrders\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
            'basket_uuid' => ['required', 'uuid', 'exists:shop_baskets,uuid'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'user_surname' => ['nullable', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255'],
            'user_phone' => ['nullable', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'user_name' => t('profile.user_name'),
            'user_surname' => t('profile.user_surname'),
            'user_email' => t('profile.user_email'),
            'user_phone' => t('profile.user_phone'),
            'comment' => t('profile.order_comment'),
        ];
    }
}
