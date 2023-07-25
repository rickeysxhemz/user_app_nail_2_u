<?php

namespace App\Http\Controllers;

use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\RatingService;

class RatingController extends Controller
{
    public function __construct(RatingService $RatingService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->rating_service = $RatingService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function getDetails()
    {
        $rating_details = $this->rating_service->getDetails();

        if ($rating_details === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Rating details not found!", $rating_details));

        if (!$rating_details)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Rating details did not fetched!", $rating_details));

        return ($this->global_api_response->success(count($rating_details), "Rating details fetched successfully!", $rating_details));
    }
}
