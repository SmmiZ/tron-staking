<?php

namespace App\Http\Requests\Api;

use App\Services\TronApi\Tron;
use Closure;

class StoreConsumerRequest extends BaseRequest
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
            'name' => ['required', 'string', 'max:40'],
            'address' => ['required', 'filled', function (string $attribute, mixed $value, Closure $fail) {
                $response = (new Tron())->validateAddress($value);

                if (isset($response['result']) && !$response['result']) {
                    $fail('The :attribute must be a valid wallet address.');
                }
            }],
            'resource_amount' => ['required', 'numeric', 'integer', 'min:1']
        ];
    }
}
