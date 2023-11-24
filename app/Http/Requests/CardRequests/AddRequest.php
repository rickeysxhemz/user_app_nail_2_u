<?php

namespace App\Http\Requests\CardRequests;

use App\Http\Requests\BaseRequest;

class AddRequest extends BaseRequest
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
            'card_number' => ['required', 'unique:cards,card_number'],
            'card_name' => ['required'],
            'exp_date' => ['required'],
            'cvv' => ['required']
        ];
    }
}
