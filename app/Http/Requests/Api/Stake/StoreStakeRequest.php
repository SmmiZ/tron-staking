<?php

namespace App\Http\Requests\Api\Stake;

use App\Http\Requests\Api\BaseRequest;

class StoreStakeRequest extends BaseRequest
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
//            'unstake_at' => ['required', 'date', 'after:' . now()->addDays(3)]
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
//            'unstake_at' => __('validation.attributes.unstake_at'),
        ];
    }
}
