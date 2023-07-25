<?php

namespace App\Http\Requests\UserRequests;

use App\Http\Requests\BaseRequest;

class EditProfileRequest extends BaseRequest
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
            'username' => 'bail|string|max:20|min:4',
            'phone_no' => 'digits:11',
            'email' => 'email|unique:users,email|max:255|min:4',
            'password' => 'string|max:255|min:8',
            'address' => 'string',
            'image_url' => 'mimes:jpg,jpeg,png|max:2048'
        ];
    }
}
