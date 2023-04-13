<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = collect();
        foreach ($validator->errors()->all() as $key => $error) {
            $errors->push([
                'name' => $validator->errors()->keys()[$key] ?? 'Unknown',
                'desc' => $error
            ]);
        }

        $response = response([
            'status' => false,
            'error' => $validator->errors()->first(),
            'errors' => $errors
        ], 422, [
            'LV-message' => $validator->errors()->first()
        ]);
        throw new ValidationException($validator, $response);
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'error' => 'You\'re not authorized to do this request',
                'errors' => (object)[],
            ], 403)
        );
    }
}
