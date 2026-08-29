<?php

namespace App\Modules\UserAuth\Http\Requests;

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

            'surname' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:20'],

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
            'name' => t('profile.user_name'),
            'surname' => t('profile.user_surname'),
            'email' => t('profile.user_email'),
            'phone' => t('profile.user_phone'),
            'password' => t('auth.password'),
            'terms' => t('auth.terms'),
        ];
    }
}
