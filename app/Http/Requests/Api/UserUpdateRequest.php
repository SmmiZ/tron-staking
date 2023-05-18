<?php

namespace App\Http\Requests\Api;

class UserUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['filled', 'string', 'min:3'],
            'photo' => ['filled', 'image', 'mimes:jpg,jpeg,png', 'max:10000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'photo' => __('validation.attributes.photo'),
        ];
    }
}
