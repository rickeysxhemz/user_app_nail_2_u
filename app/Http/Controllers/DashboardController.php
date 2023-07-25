<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\GetArtistPortfolioRequest;
use App\Http\Requests\Dashboard\GetArtistReviewsRequest;
use App\Http\Requests\Dashboard\TrackBookingRequest;
use App\Http\Requests\UserRequests\DeviceTokenRequest;
use App\Services\DashboardService;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;

class DashboardController extends Controller
{
    public function __construct(DashboardService $DashboardService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->dashboard_service = $DashboardService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function getAllArtists()
    {
        $get_artists = $this->dashboard_service->getAllArtists();

        if ($get_artists === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "No artists found!", []));

        if (!$get_artists)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Artists did not fetched!", $get_artists));

        return ($this->global_api_response->success(count($get_artists), "All artists fetched successfully!", $get_artists));
    }
    
    
    public function getArtist($id)
    {
        $get_artists = $this->dashboard_service->getArtist($id);

        if ($get_artists === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "No artists found!", []));

        if (!$get_artists)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Artists did not fetched!", $get_artists));

        return ($this->global_api_response->success(count($get_artists), "All artists fetched successfully!", $get_artists));
    }

    public function getSuggestedArtists()
    {
        $get_artists = $this->dashboard_service->getSuggestedArtists();

        if ($get_artists === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "No artists found!", []));

        if (!$get_artists)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Suggested artists did not fetched!", $get_artists));

        return ($this->global_api_response->success(1, "Suggested artists fetched successfully!", $get_artists));
    }

    public function getNewArtists()
    {
        $get_artists = $this->dashboard_service->getNewArtists();

        if ($get_artists === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "No artists found!", []));

        if (!$get_artists)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "New artists did not fetched!", $get_artists));

        return ($this->global_api_response->success(count($get_artists), "New artists fetched successfully!", $get_artists));
    }

    public function getArtistPortfolio(GetArtistPortfolioRequest $request)
    {
        $artist_portfolio = $this->dashboard_service->getArtistPortfolio($request);

        if ($artist_portfolio === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "No portfolio data found!", []));

        if (!$artist_portfolio)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Artist portfolio did not fetched!", $artist_portfolio));

        return ($this->global_api_response->success(1, "Artist portfolio fetched successfully!", $artist_portfolio));
    }
    
    public function getArtistReviews(GetArtistReviewsRequest $request)
    {
        $artist_reviews = $this->dashboard_service->getArtistReviews($request);

        if (!$artist_reviews)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Artist reviews did not fetched!", $artist_reviews));

        return ($this->global_api_response->success(1, "Artist reviews fetched successfully!", $artist_reviews));
    }
    
    public function trackBooking(TrackBookingRequest $request)
    {
        $booking_data = $this->dashboard_service->trackBooking($request);

        if ($booking_data === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Booking data not found!", []));

        if (!$booking_data)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Booking data did not fetched!", $booking_data));

        return ($this->global_api_response->success(1, "Booking data fetched successfully!", $booking_data));
    }
    
    public function getTrackBooking()
    {
        $booking_data = $this->dashboard_service->getTrackBooking();

        if ($booking_data === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Booking data not found!", []));

        if (!$booking_data)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Booking data did not fetched!", $booking_data));

        return ($this->global_api_response->success(1, "Booking data fetched successfully!", $booking_data));
    } 
    
    public function deviceToken(DeviceTokenRequest $request)
    {
        $device_token = $this->dashboard_service->deviceToken($request);
        // dd($device_token);
        if ($device_token == GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "user not found!", []));

        if (!$device_token)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "User device token not updated!", $device_token));

        return ($this->global_api_response->success(1, "User device token save successfully!", $device_token));
    }
}
