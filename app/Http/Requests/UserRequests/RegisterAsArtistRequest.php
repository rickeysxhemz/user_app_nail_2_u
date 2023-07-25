<?php

namespace App\Http\Requests\UserRequests;

use App\Http\Requests\BaseRequest;

class RegisterAsArtistRequest extends BaseRequest
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
            'username' => 'bail|required|string|max:20',
            'email' => 'bail|required|email|unique:users,email|max:255|min:4',
            'password' => 'bail|required|string|max:255|min:8',
            'phone_no' => 'bail|required|digits:11',
            'address' => 'bail|required|string',
            'experience' => 'bail|required|numeric|between:1,80',
            'cv_url' => 'bail|required|mimes:doc,docx,pdf'
        ];
    }
}
