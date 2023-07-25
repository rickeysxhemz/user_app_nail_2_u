<?php

namespace App\Http\Requests\ServicesDiscount;

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
            'service_id' => 'required|exists:services,id',
            'discount_percentage' => 'required|numeric|digits_between:1,100',
            'start_date' => 'required|date_format:Y-m-d|date',
            'end_date' => 'required|date_format:Y-m-d|date|after_or_equal:start_date'
        ];
    }
}
