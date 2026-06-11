<?php

namespace App\Modules\Profile\Http\Requests;

use App\Modules\Core\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateUserPasswordRequest extends BaseRequest
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
            'current_password' => [
                'required',
                'string',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'current_password' => t('auth.current_password'),
            'password' => t('auth.new_password'),
        ];
    }

    /**
     * Overrides the default validation error response.
     *
     * Temporary method for testing custom validation responses.
     * Remove after testing is completed.
     *
     * @param Validator $validator The validator instance.
     * @return array The custom validation error response.
     */
//    public function abcResponse(Validator $validator): array
//    {
//        return [
//            [
//                'method'=>'alert',
//                'content'=>  'test',
//            ]
//        ];
//    }
}
