<?php

namespace App\Http\Requests\Api\Wallet;

use App\Http\Requests\Api\BaseRequest;
use App\Services\TronApi\Tron;
use Closure;

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
            'address' => ['required', 'filled', function (string $attribute, mixed $value, Closure $fail) {
                $response = (new Tron())->validateAddress($value);

                if (isset($response['result']) && !$response['result']) {
                    $fail('The :attribute must be a valid wallet address.');
                }
            }],
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
