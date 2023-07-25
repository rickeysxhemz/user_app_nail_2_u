<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\BaseRequest;

class CreateBookingRequest extends BaseRequest
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
            'artist_id' => 'required|numeric|exists:users,id',
            'services_ids' => 'required|array|distinct|min:1',
            'schedule_time_id' => 'required|numeric',
        ];
    }
}
