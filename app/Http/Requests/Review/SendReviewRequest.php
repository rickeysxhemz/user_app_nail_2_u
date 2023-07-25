<?php

namespace App\Http\Requests\Review;

use App\Http\Requests\BaseRequest;

class SendReviewRequest extends BaseRequest
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
            'artist_id' => 'required',
            'rating' => 'required',
            'review' => 'required',
        ];
    }
}