<?php

namespace App\Http\Requests\Api;

class TrxAmountRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'trx_amount' => ['required', 'integer'],
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
            'trx_amount' => __('validation.attributes.trx_amount'),
        ];
    }
}
