<?php

namespace App\Modules\Profile\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'email' => [
                'required',
                'string',
                'email',
                function ($attribute, $value, $fail) {
                    if ($value !== request()->user()->email) {
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
            'email'        => t('auth.email'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('delete_email') && !$this->has('email')) {
            $this->merge([
                'email' => $this->delete_email,
            ]);
        }
    }
}
