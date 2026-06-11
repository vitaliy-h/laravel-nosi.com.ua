<?php

namespace App\Modules\Core\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
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
        return [];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->has('abc_request')) {
            $actions = $this->abcResponse($validator);
            throw new HttpResponseException(
                response()->json(['actions' => $actions])
            );
        }

        parent::failedValidation($validator);
    }

    public function abcResponse(Validator $validator): array
    {
        $selector = $this->input('abc_form_selector', '');
        $actions = [
            // clear and hide alerts
            [
                'method' => 'text',
                'selector' => '.alert',
                'content' => '',
            ],
            [
                'method' => 'hide',
                'selector' => '.alert',
            ]
        ];
        if ($selector) {
            // clear previous input validation errors
            $actions[] = [
                'method' => 'html',
                'selector' => $selector . ' [data-error]',
                'content' => '',
            ];
        }

        foreach ($validator->errors()->toArray() as $field => $messages) {
            $actions[] = [
                'method' => 'html',
                'selector' => $selector . ' [data-error="' . $field . '"]',
                'content' => implode('<br>', $messages),
            ];
        }

        return $actions;
    }
}
