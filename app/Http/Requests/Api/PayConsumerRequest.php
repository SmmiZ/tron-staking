<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Closure;
use Illuminate\Validation\Rule;

class PayConsumerRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'consumers' => ['required', 'array'],
            'consumers.*' => ['required', 'exists:consumers,id', function (string $attribute, mixed $value, Closure $fail) {
                if (!$this->user()->consumers()->find($value)) {
                    $fail('Access denied. Consumer is not yours');
                }
            }],
            'days' => ['required', Rule::in(7, 30, 365)]
        ];
    }
}
