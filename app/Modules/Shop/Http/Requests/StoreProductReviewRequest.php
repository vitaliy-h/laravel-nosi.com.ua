<?php

namespace App\Modules\Shop\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Reviews are open to guests — the route carries no `auth` middleware.
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
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
            'rating'     => ['required', 'integer', 'between:1,5'],
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'text'       => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'rating' => t('shop.your_rating'),
            'name'   => t('shop.your_name'),
            'email'  => t('shop.your_email'),
            'text'   => t('shop.your_product_review'),
        ];
    }
}
