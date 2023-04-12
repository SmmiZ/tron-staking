<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        //todo посмотреть что тут оптимизировать
        $errors = collect();
        foreach ($validator->errors()->all() as $key => $error) {
            $errors->push([
                'error_name' => $validator->errors()->keys()[$key] ?? 'unknown',
                'error_descr' => $error
            ]);
        }

        $response = new Response([
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
