<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\SendReviewRequest;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Libs\Response\GlobalApiResponse;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(ReviewService $ReviewService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->review_service = $ReviewService;
        $this->global_api_response = $GlobalApiResponse;
    }
    public function send(SendReviewRequest $request)
    {
        $review_service = $this->review_service->send($request);

        if (!$review_service)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "User's review did not posted!", $review_service));

        return ($this->global_api_response->success(1, "Review posted successfully!", $review_service));
    }
    
    public function getReview($id)
    {
        $review_service = $this->review_service->getReview($id);

        if (!$review_service)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "User's review did fetched!", $review_service));

        return ($this->global_api_response->success(1, "Review fetched successfully!", $review_service));
    }
    
}