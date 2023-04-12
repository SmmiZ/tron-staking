<?php

namespace App\Http\Requests;

use App\Enums\Resources;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
            'pin' => ['required', Rule::exists('staff', 'pin')->where('id', $this->user()->id), 'exclude'],
            'name' => ['required', 'filled', 'string'],
            'address' => ['required', 'filled', 'string', 'starts_with:T', 'size:34'],
            'amount' => ['required', 'filled', 'integer'],
            'resource' => ['sometimes', 'filled', new Enum(Resources::class)],
        ];
    }
}
