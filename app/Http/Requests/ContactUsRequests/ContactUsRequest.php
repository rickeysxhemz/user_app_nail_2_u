<?php

namespace App\Http\Requests\ContactUsRequests;

use App\Http\Requests\BaseRequest;

class ContactUsRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'full_name' => 'bail|required|string|max:20',
            'email' => 'bail|required|email|max:255|min:4',
            'mobile' => 'bail|required|digits:11',
            'message' => 'bail|required|string'
        ];
    }
}
