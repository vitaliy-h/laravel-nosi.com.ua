<?php

namespace App\Modules\Profile\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ProfileDeleteUserAccountRequest extends FormRequest
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
            'delete_email' => [
                'required',
                'string',
                'email',
                function ($attribute, $value, $fail) {
                    if ($value !== auth()->user()->email) {
                        $fail(t('profile.delete_account_email_mismatch'));
                    }
                },
            ],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'delete_email' => t('auth.email'),
        ];
    }
}
