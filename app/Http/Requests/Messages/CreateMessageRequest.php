<?php

namespace App\Http\Requests\Messages;

use App\Http\Requests\BaseRequest;

class CreateMessageRequest extends BaseRequest
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
            'receiver_id' => 'required|numeric|exists:users,id',
            'message' => 'required',
        ];
    }
}
