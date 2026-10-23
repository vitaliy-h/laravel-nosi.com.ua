<?php

namespace App\Modules\Review\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'between:1,5'],
            'name'   => ['required', 'string', 'max:255'],
            'city'   => ['nullable', 'string', 'max:255'],
            'email'  => ['required', 'email:rfc,filter', 'max:255'],
            'text'   => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'rating' => t('home.review_rating'),
            'name'   => t('home.review_name'),
            'city'   => t('home.review_city'),
            'email'  => t('home.review_email'),
            'text'   => t('home.review_text'),
        ];
    }
}
