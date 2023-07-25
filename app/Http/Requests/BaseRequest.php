<?php

namespace App\Http\Requests;

use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{

    /**
     * Stops validations on first validation failure
     */
    protected $stopOnFirstFailure = true;

    /**
     * Get the proper failed validation response for the request.
     *
     * @param array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws ApiException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = (new GlobalApiResponse())->error(GlobalApiResponseCodeBook::INVALID_FORM_INPUTS, 'Send valid inputs', $validator->errors()->all());
        throw new HttpResponseException(response()->json($errors, 200));
    }
}