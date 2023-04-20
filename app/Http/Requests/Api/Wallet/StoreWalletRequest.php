<?php

namespace App\Http\Requests\Api\Wallet;

use App\Http\Requests\Api\BaseRequest;

class StoreWalletRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'address' => ['required', 'filled', 'string', 'starts_with:T', 'size:34'],
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
            'address' => __('validation.attributes.address'),
        ];
    }
}
