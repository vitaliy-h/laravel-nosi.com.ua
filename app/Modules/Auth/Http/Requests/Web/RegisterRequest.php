<?php

namespace App\Modules\Auth\Http\Requests\Web;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],

            'terms' => ['accepted'],
        ];
    }

    /**
     * Attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => t('auth.name'),
            'email' => t('auth.email'),
            'password' => t('auth.password'),
            'terms' => t('auth.terms'),
        ];
    }
}
