<?php

namespace App\Http\Requests\Dashboard;

use App\Http\Requests\BaseRequest;

class GetArtistPortfolioRequest extends BaseRequest
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
            'artist_id' => 'required|numeric|exists:users,id'
        ];
    }
}
