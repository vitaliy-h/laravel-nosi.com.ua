<?php

namespace App\Modules\ShopOrder\Http\Requests;

use App\Modules\Core\Rules\UkrainianPhone;
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
     * The phone is stored as +380XXXXXXXXX whatever way the customer typed it.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('user_phone'))) {
            $this->merge(['user_phone' => UkrainianPhone::normalize($this->input('user_phone'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_uuid'    => ['required', 'uuid', 'exists:shop_carts,uuid'],
            'user_name'    => ['nullable', 'string', 'max:255'],
            'user_surname' => ['nullable', 'string', 'max:255'],
            'user_email'   => ['required', 'email:rfc,filter', 'max:255'],
            'user_phone'   => ['required', 'string', new UkrainianPhone()],
            'comment'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'user_name'    => t('profile.user_name'),
            'user_surname' => t('profile.user_surname'),
            'user_email'   => t('profile.user_email'),
            'user_phone'   => t('profile.user_phone'),
            'comment'      => t('profile.order_comment'),
        ];
    }
}
