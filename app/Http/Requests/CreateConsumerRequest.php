<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateConsumerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'name' => ['required', 'filled', 'string'],
            'address' => ['required', 'filled', 'string', 'starts_with:T', 'size:34'],
            'resource_amount' => ['required', 'filled', 'numeric'],
            'user_id' => ['required', 'filled', 'numeric', 'exists:users,id'],
        ];
    }
}
